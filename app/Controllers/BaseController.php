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

                // Load unread messages count
                // TODO: Re-enable after aa_messages table is created
                // $messageModel = new \App\Models\MessageModel();
                // $params = [
                //     'u_id' => $this->admin_session['u_id'],
                //     'conditions' => [['mu_read' => 0]]
                // ];
                // $messages = $messageModel->getRecords($params);
                // $this->session->set('messages', $messages);
            } else {
                // Not logged in - redirect to login and stop execution
                // Note: return redirect() in initController() does NOT stop method execution in CI4
                header('Location: ' . base_url('home/login'));
                exit;
            }
        }
    }
}
