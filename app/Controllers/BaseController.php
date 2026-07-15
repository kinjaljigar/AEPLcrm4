<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\Authorization;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Session instance
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Authorization library instance
     *
     * @var Authorization
     */
    protected $authorization;

    /**
     * Admin session data
     *
     * @var array
     */
    protected $admin_session = [];

    /**
     * JWT token
     *
     * @var string|null
     */
    protected $token;

    /**
     * CLI base URL for API calls
     *
     * @var string
     */
    protected $cliBaseUrl;

    /**
     * View data array
     *
     * @var array
     */
    protected $view_data = [];

    /**
     * Methods that don't require authentication
     *
     * @var array
     */
    protected $open_methods = ['login', 'logout', 'forget'];

    /**
     * Methods that require Master Admin access
     *
     * @var array
     */
    protected $master_methods = [];

    /**
     * Methods that require Project Leader or higher access
     *
     * @var array
     */
    protected $project_methods = [];

    /**
     * Methods that require Team access
     *
     * @var array
     */
    protected $team_methods = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load helpers
        helper(['url', 'custom', 'form']);

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload services and libraries
        $this->session = service('session');
        $this->authorization = new Authorization();

        // Set timezone
        date_default_timezone_set('Asia/Kolkata');

        // Get current method name
        $router = service('router');
        $method = strtolower($router->methodName());

        // Load CLI base URL from config
        $this->cliBaseUrl = config('App')->cliBaseUrl ?? 'http://localhost/AEPLcrm4/public/v1/';

        // Check authentication for protected methods
        if (!in_array($method, $this->open_methods)) {
            // Get admin session
            if ($this->session->has('admin_session')) {
                $this->admin_session = $this->session->get('admin_session');
            }

            // Get token
            if ($this->session->has('token')) {
                $this->token = $this->session->get('token');
            }

            // Check if user is logged in
            if (isset($this->admin_session['u_id']) && $this->admin_session['u_id'] > 0) {
                // Check project method access
                if (in_array($method, $this->project_methods)) {
                    $has_access = $this->authorization->is_project_leader_or_higher($this->admin_session) ||
                                  ($this->authorization->is_role_allowed($this->admin_session['u_type'] ?? '', ['Project Leader', 'TaskCoordinator']) && $method == 'index');

                    if (!$has_access) {
                        header('Location: ' . base_url('home/tasks'));
                        exit;
                    }
                }

                // Check master method access
                if (in_array($method, $this->master_methods)) {
                    if (!$this->authorization->is_admin($this->admin_session)) {
                        header('Location: ' . base_url('home/tasks'));
                        exit;
                    }
                }

                // Load unread inbox messages (leave approvals, task alerts, etc.)
                try {
                    $db = \Config\Database::connect();

                    // Ensure leave reminder DB record exists for today (Project Leaders only).
                    // Must run BEFORE the message query so the created record is included.
                    $this->_checkLeaveReminders($db, intval($this->admin_session['u_id']), $this->admin_session['u_type'] ?? '');

                    $messages = $db->query("
                        SELECT M.me_id, M.me_text, M.me_p_id,
                               COALESCE(M.leave_message, 'No') AS leave_message,
                               COALESCE(M.conference_message, 'No') AS conference_message,
                               COALESCE(M.task_message, 'No') AS task_message,
                               COALESCE(M.birthday_message, 'No') AS birthday_message,
                               COALESCE(M.leave_reminder, 'No') AS leave_reminder,
                               P.p_name
                        FROM aa_message M
                        LEFT JOIN aa_projects P ON P.p_id = M.me_p_id
                        INNER JOIN aa_message_users MU ON MU.mu_me_id = M.me_id
                        WHERE MU.mu_u_id = " . intval($this->admin_session['u_id']) . "
                          AND (MU.mu_read IS NULL OR MU.mu_read = 0)
                        ORDER BY M.me_id DESC
                    ")->getResultArray();

                    $this->session->set('messages', $messages);
                } catch (\Exception $e) {
                    $this->session->set('messages', []);
                }
            } else {
                // Not logged in - redirect to login and stop execution
                // Note: return redirect() in initController() does NOT stop method execution in CI4
                header('Location: ' . base_url('home/login'));
                exit;
            }
        }
    }

    /**
     * Leave reminder for Project Leaders.
     * Creates one real aa_message + aa_message_users record per leader per day
     * when any of their employees have an approved leave starting within the next 3 days.
     * Using real me_id ensures the standard dismiss (reset_me / mu_read=1) flow works correctly.
     */
    protected function _checkLeaveReminders($db, $u_id, $u_type)
    {
        try {
            if ($u_type !== 'Project Leader') return;

            $today          = date('Y-m-d');
            $threeDaysLater = date('Y-m-d', strtotime('+3 days'));

            // Cleanup: delete aa_message_users rows where the leader already dismissed the alert
            $db->query(
                "DELETE MU FROM aa_message_users MU
                 INNER JOIN aa_message M ON M.me_id = MU.mu_me_id
                 WHERE M.leave_reminder = 'Yes' AND MU.mu_u_id = {$u_id} AND MU.mu_read = 1"
            );
            // Cleanup: delete leave_reminder messages older than 7 days
            $db->query(
                "DELETE FROM aa_message
                 WHERE leave_reminder = 'Yes'
                 AND me_u_id = {$u_id}
                 AND DATE(me_datetime) < DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );

            // One message per leader per day — skip if already created today
            $alreadySent = (int)($db->query(
                "SELECT COUNT(*) as cnt FROM aa_message
                 WHERE leave_reminder = 'Yes'
                 AND me_u_id = {$u_id}
                 AND DATE(me_datetime) = '{$today}'"
            )->getRowArray()['cnt'] ?? 0);

            if ($alreadySent > 0) return;

            // Find employees under this leader with upcoming approved leaves
            $upcomingLeaves = $db->query(
                "SELECT U.u_name, L.l_from_date, L.l_to_date,
                        L.l_is_halfday, L.l_halfday_time,
                        L.l_is_hourly, L.l_hourly_time, L.l_message
                 FROM aa_leaves L
                 JOIN aa_users U ON U.u_id = L.l_u_id
                 WHERE U.u_leader = {$u_id}
                 AND L.l_status = 'Approved'
                 AND L.l_from_date >= '{$today}'
                 AND L.l_from_date <= '{$threeDaysLater}'
                 ORDER BY L.l_from_date ASC, U.u_name ASC"
            )->getResultArray();

            if (empty($upcomingLeaves)) return;

            // Build message text — one row per leave
            $rows = [];
            foreach ($upcomingLeaves as $lv) {
                $from    = !empty($lv['l_from_date']) ? date('d M Y', strtotime($lv['l_from_date'])) : '';
                $to      = !empty($lv['l_to_date'])   ? date('d M Y', strtotime($lv['l_to_date']))   : '';
                $dateStr = ($from === $to || empty($to)) ? $from : $from . ' to ' . $to;

                $type = '';
                if (!empty($lv['l_is_halfday']) && $lv['l_is_halfday'] === 'Yes')
                    $type = ' &mdash; Half Day' . (!empty($lv['l_halfday_time']) ? ' (' . htmlspecialchars($lv['l_halfday_time']) . ')' : '');
                elseif (!empty($lv['l_is_hourly']) && $lv['l_is_hourly'] === 'Yes')
                    $type = ' &mdash; ' . (!empty($lv['l_hourly_time']) ? htmlspecialchars($lv['l_hourly_time']) : 'Hourly');

                $row = '<strong>' . htmlspecialchars($lv['u_name']) . '</strong> &mdash; ' . $dateStr . $type;
                if (!empty($lv['l_message']))
                    $row .= ' &mdash; <em>' . htmlspecialchars($lv['l_message']) . '</em>';
                $rows[] = '<li>' . $row . '</li>';
            }
            $messageText = 'Upcoming leave' . (count($rows) > 1 ? 's' : '') . ' in the next 3 days:<ul style="margin:6px 0 0 0;padding-left:18px;">' . implode('', $rows) . '</ul>';

            // Insert message record
            $nextMeId = (int)($db->query("SELECT COALESCE(MAX(me_id), 0) + 1 AS next_id FROM aa_message")->getRowArray()['next_id'] ?? 1);
            $db->table('aa_message')->insert([
                'me_id'              => $nextMeId,
                'me_u_id'            => $u_id,
                'me_p_id'            => 0,
                'me_text'            => $messageText,
                'me_datetime'        => date('Y-m-d H:i:s'),
                'leave_message'      => 'No',
                'conference_message' => 'No',
                'task_message'       => 'No',
                'schedule_message'   => 'No',
                'birthday_message'   => 'No',
                'leave_reminder'     => 'Yes',
            ]);

            // Add to this project leader's inbox (mu_read=0 = unread/visible)
            $db->table('aa_message_users')->insert([
                'mu_me_id' => $nextMeId,
                'mu_u_id'  => $u_id,
                'mu_read'  => 0,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Leave reminder check failed: ' . $e->getMessage());
        }
    }

    /**
     * Make a cURL call to the external API (cliBaseUrl).
     *
     * @param string $endpoint  e.g. 'conference/list'
     * @param string $method    GET | POST | PUT | DELETE
     * @param array  $data      Form/JSON data to send
     * @param bool   $jsonBody  Send $data as JSON body (for PUT/POST JSON)
     * @param bool   $multipart Send as multipart (file uploads)
     * @return array ['code' => int, 'body' => string, 'error' => string]
     */
    protected function callExternalApi(string $endpoint, string $method = 'GET', array $data = [], bool $jsonBody = false, bool $multipart = false): array
    {
        $url = $this->cliBaseUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json',
        ];

        $method = strtoupper($method);
        if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($jsonBody) {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $body = curl_exec($ch);
        if ($body === false) {
            $body = '';
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'API cURL error [' . $method . ' ' . $url . ']: ' . $error);
        }
        if ($code !== 200) {
            log_message('error', 'API HTTP ' . $code . ' [' . $method . ' ' . $url . ']: ' . $body);
        }

        return ['code' => $code, 'body' => $body, 'error' => $error];
    }
}
