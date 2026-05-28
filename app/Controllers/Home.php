<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\MessageModel;
use App\Models\WeeklyworkModel;
use App\Models\DependencyModel;
use App\Models\ProjectMessageModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Home extends BaseController
{
    protected $open_methods = ['login', 'logout', 'forget'];
    protected $master_methods = ['report_employee_salary'];
    protected $project_methods = ['index', 'projects', 'employees', 'report_leave', 'report_dependency', 'report_timesheet', 'report_daily', 'report_leader_employee', 'report_employee', 'report_estimated_actual', 'conference', 'company', 'companyuser', 'usertask'];

    public function index()
    {
        // For now, redirect to login if not authenticated
        if (!isset($this->admin_session['u_id'])) {
            return redirect()->to(base_url('home/login'));
        }

        // MailCoordinator has no dashboard - redirect to messages
        if (($this->admin_session['u_type'] ?? '') === 'MailCoordinator') {
            return redirect()->to(base_url('home/messages'));
        }

        // Employee has no dashboard - redirect to tasks
        if (($this->admin_session['u_type'] ?? '') === 'Employee') {
            return redirect()->to(base_url('home/tasks'));
        }

        // Load dashboard data
        $userModel = new UserModel();

        // Get all leaders (Project Leaders and above)
        $leaders = $userModel->where('u_status', 'Active')
                            ->whereIn('u_type', ['Super Admin', 'Master Admin', 'Bim Head', 'Project Leader'])
                            ->orderBy('u_name', 'ASC')
                            ->findAll();

        // Get all active employees
        $employees = $userModel->where('u_status', 'Active')
                              ->orderBy('u_name', 'ASC')
                              ->findAll();

        $this->view_data['page'] = 'index';
        $this->view_data['meta_title'] = 'Dashboard';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['leaders'] = $leaders;
        $this->view_data['employees'] = $employees;
        $this->view_data['dataURL'] = 'upcoming';

        // Initialize data with proper structure
        // Conferences, Schedules, Tasks use external API - keep as empty
        $this->view_data['conferences'] = ['status' => 'success', 'data' => []];
        $this->view_data['schedules'] = ['status' => 'success', 'data' => []];
        $this->view_data['tasks'] = ['status' => 'success', 'data' => []];

        $db = \Config\Database::connect();
        $u_type = $this->admin_session['u_type'] ?? '';
        $u_id = $this->admin_session['u_id'] ?? 0;

        // Detect aa_weekly_work_dependency column names (CI3 uses w_id/created_date/created_by, CI4 may use weekly_work_id/created_at)
        $fkCol = 'w_id';
        $dateCol = 'created_date';
        $hasCreatedBy = true;
        try {
            $cols = $db->getFieldNames('aa_weekly_work_dependency');
            $fkCol = in_array('weekly_work_id', $cols) ? 'weekly_work_id' : 'w_id';
            $dateCol = in_array('created_date', $cols) ? 'created_date' : 'created_at';
            $hasCreatedBy = in_array('created_by', $cols);
        } catch (\Exception $e) {
            // Use defaults
        }

        // Load latest dependencies (limit 20)
        try {

            $createdBySelect = $hasCreatedBy
                ? "WD.created_by AS created_by_id, CU.u_name AS created_by"
                : "WW.leader_id AS created_by_id, LU.u_name AS created_by";
            $createdByJoin = $hasCreatedBy
                ? "LEFT JOIN aa_users CU ON WD.created_by = CU.u_id"
                : "";

            $depSql = "SELECT WD.*, WW.p_id, P.p_name AS project_name,
                        {$createdBySelect},
                        WD.{$dateCol} AS created_date,
                        (SELECT GROUP_CONCAT(u2.u_name SEPARATOR ', ')
                         FROM aa_users u2
                         WHERE FIND_IN_SET(u2.u_id, WD.dep_leader_ids)) AS assigned_to
                    FROM aa_weekly_work_dependency WD
                    LEFT JOIN aa_weekly_work WW ON WD.{$fkCol} = WW.w_id
                    LEFT JOIN aa_projects P ON WW.p_id = P.p_id
                    LEFT JOIN aa_users LU ON WW.leader_id = LU.u_id
                    {$createdByJoin}
                    WHERE WD.status != 'Completed'";

            // For Project Leaders, only show their own or assigned to them
            if ($u_type == 'Project Leader') {
                $depSql .= " AND (WW.leader_id = " . intval($u_id) . " OR FIND_IN_SET(" . intval($u_id) . ", WD.dep_leader_ids))";
            }

            $depSql .= " ORDER BY
                CASE WD.status WHEN 'Pending' THEN 1 WHEN 'In Progress' THEN 2 ELSE 3 END ASC,
                CASE WD.priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 WHEN 'Low' THEN 3 ELSE 4 END ASC
                LIMIT 5";

            $this->view_data['dependencies'] = $db->query($depSql)->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Dashboard dependencies error: ' . $e->getMessage());
            $this->view_data['dependencies'] = [];
        }

        // Load weekly works (for Bim Head and above)
        try {
            $leader_id = isset($_GET['leader_id']) ? intval($_GET['leader_id']) : null;

            // Use detected column name for dependency FK
            $sql = "SELECT w.*, p.p_name AS project_name, u.u_name AS leader_name,
                (SELECT COUNT(*) FROM aa_users WHERE u_leader = w.leader_id) AS team_assigned,
                (SELECT GROUP_CONCAT(u2.u_name ORDER BY u2.u_name SEPARATOR ', ') FROM aa_weekly_work_users wu JOIN aa_users u2 ON u2.u_id = wu.u_id WHERE wu.weekly_work_id = w.w_id) AS assigned_users,
                (SELECT COUNT(*) FROM aa_projects WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,
                (SELECT COUNT(*) FROM aa_weekly_work_dependency d WHERE d.{$fkCol} = w.w_id AND d.status != 'Completed') AS incomplete_deps
                FROM aa_weekly_work w
                LEFT JOIN aa_projects p ON p.p_id = w.p_id
                LEFT JOIN aa_users u ON u.u_id = w.leader_id
                WHERE 1=1";
            if ($leader_id) $sql .= " AND w.leader_id = " . intval($leader_id);
            $sql .= " ORDER BY u.u_name DESC, w.w_id DESC LIMIT 5";
            $this->view_data['weekly_works'] = $db->query($sql)->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Dashboard weekly_works error: ' . $e->getMessage());
            $this->view_data['weekly_works'] = [];
        }

        // Load today's messages
        try {
            $today = date('Y-m-d');
            $msgBuilder = $db->table('aa_project_messages PM')
                ->select('PM.pm_id, PM.pm_text, PM.pm_datetime, PM.pm_descipline, P.p_name')
                ->join('aa_projects P', 'PM.pm_p_id = P.p_id', 'left')
                ->where('PM.pm_deleted', 0)
                ->where('DATE(PM.pm_datetime)', $today)
                ->orderBy('PM.pm_datetime', 'DESC')
                ->limit(20);

            // if ($u_type == 'Project Leader') {
            //     $msgBuilder->groupStart()
            //         ->where('PM.pm_created_by', $u_id)
            //         ->orWhereIn('PM.pm_id', function($subquery) use ($u_id) {
            //             return $subquery->select('pmu_pm_id')
            //                 ->from('aa_project_message_users')
            //                 ->where('pmu_u_id', $u_id);
            //         })
            //         ->groupEnd();
            // }
            if ($u_type == 'Project Leader') {
                $msgBuilder->groupStart()
                    ->where('PM.pm_created_by', $u_id)
                    ->orWhereIn('PM.pm_id', function ($subquery) use ($u_id) {
                        return $subquery->select('pmu_pm_id')
                            ->from('aa_project_message_users')
                            ->where('pmu_u_id', $u_id);
                    })
                    ->groupEnd();

                // ✅ ADD THIS CONDITION (IMPORTANT)
                $msgBuilder->where("FIND_IN_SET($u_id, P.p_leader) >", 0, false);
            }

            $messages = $msgBuilder->get()->getResultArray();
            // Add dependency_text field for view compatibility (used in title attribute)
            foreach ($messages as &$msg) {
                $msg['dependency_text'] = $msg['pm_text'] ?? '';
            }
            unset($msg);
            $this->view_data['todaysmessages'] = $messages;
        } catch (\Exception $e) {
            $this->view_data['todaysmessages'] = [];
        }

        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function login()
    {
        // Handle POST request (login submission) first
        if ($this->request->getMethod() === 'post') {
            // Clear any output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $username = $this->request->getPost('u_username');
            $password = $this->request->getPost('u_password');

            header('Content-Type: application/json');

            if ($username && $password) {
                $userModel = new UserModel();
                $user = $userModel->verifyLogin($username, $password);

                if ($user) {
                    // Check if already logged in on web
                    if ($user['is_web_logged_in'] == 1) {
                        echo json_encode([
                            'status' => 'fail',
                            'message' => 'User already logged in from another session.'
                        ]);
                        exit;
                    } else {
                        // Update login status
                        $userModel->updateLoginStatus($user['u_id'], 1);

                        // Set session data
                        $admin_session = [
                            'u_id' => $user['u_id'],
                            'u_name' => $user['u_name'],
                            'u_username' => $user['u_username'],
                            'u_type' => $user['u_type'],
                            'u_email' => $user['u_email'],
                            'u_mobile' => $user['u_mobile'],
                            'u_app_auth' => $user['u_app_auth'] ?? '0'
                        ];

                        $this->session->set('admin_session', $admin_session);

                        // Fetch or generate JWT token for users with u_app_auth = 1
                        $token = '';
                        $canProceed = !empty($user['u_app_auth']) && $user['u_app_auth'] == 1;

                        if ($canProceed) {
                            $db = \Config\Database::connect();
                            $jwtKey = 'af0e4b7ca1c8e091fb9a781c9a2b5f07340ea4d88f96a3b5b1b9927710460f1a';
                            $issuedAt = time();
                            $expirationTime = $issuedAt + (7 * 24 * 60 * 60); // 7 days
                            $payload = [
                                'iat' => $issuedAt,
                                'exp' => $expirationTime,
                                'u_id' => $user['u_id'],
                                'u_type' => $user['u_type'],
                            ];

                            $record_token = $db->table('aa_user_tokens')
                                ->where('u_id', $user['u_id'])
                                ->get()->getResultArray();

                            if (empty($record_token)) {
                                // No token exists — create one
                                $token = JWT::encode($payload, $jwtKey, 'HS256');
                                $db->table('aa_user_tokens')->insert([
                                    'u_id' => $user['u_id'],
                                    'token' => $token,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'expires_at' => date('Y-m-d H:i:s', $expirationTime)
                                ]);
                            } else {
                                $existing_token = $record_token[0]['token'] ?? '';
                                $expires_at = new \DateTime($record_token[0]['expires_at'] ?? '1970-01-01 00:00:00');
                                if (new \DateTime() > $expires_at) {
                                    // Token expired — regenerate
                                    $token = JWT::encode($payload, $jwtKey, 'HS256');
                                    $db->table('aa_user_tokens')->where('u_id', $user['u_id'])->update([
                                        'token' => $token,
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'expires_at' => date('Y-m-d H:i:s', $expirationTime)
                                    ]);
                                } else {
                                    // Reuse existing valid token (mobile app depends on this)
                                    $token = $existing_token;
                                }
                            }
                        }

                        $this->session->set('token', $token);

                        // Return JSON response for AJAX
                        // MailCoordinator goes directly to messages page
                        $redirectUrl = ($user['u_type'] === 'MailCoordinator')
                            ? base_url('home/messages')
                            : base_url('home');

                        echo json_encode([
                            'status' => 'pass',
                            'url' => $redirectUrl,
                            'message' => 'Login successful'
                        ]);
                        exit;
                    }
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Invalid username or password.'
                    ]);
                    exit;
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Please enter username and password.'
                ]);
                exit;
            }
        }

        // GET request - show login page
        // Clear any existing session
        $this->session->destroy();

        // Clear cache headers
        $this->response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->response->setHeader('Cache-Control', 'post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');

        // Clear all cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 3600, '/');
                setcookie($name, '', time() - 3600, '/', $_SERVER['SERVER_NAME'] ?? 'localhost');
            }
        }

        $this->view_data['page'] = 'login';
        $this->view_data['meta_title'] = 'Login';
        $this->view_data['plugins'] = ['form_validation' => true];

        return view('template', ['view_data' => $this->view_data]);
    }

    public function logout()
    {
        // Get user ID before destroying session
        $userId = $this->admin_session['u_id'] ?? $this->session->get('admin_session')['u_id'] ?? null;

        if ($userId) {
            $userModel = new UserModel();
            $userModel->updateLoginStatus($userId, 0);
        }

        // Clear session data
        $this->session->remove('admin_session');
        $this->session->remove('messages');
        $this->session->remove('token');
        $this->session->destroy();

        // Clear all cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 3600, '/');
                setcookie($name, '', time() - 3600, '/', $_SERVER['SERVER_NAME'] ?? 'localhost');
            }
        }

        return redirect()->to(base_url('home/login'));
    }

    public function tasks()
    {
        $projectModel = new ProjectModel();

        if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
            $params = [
                'sort_by' => 'p_name',
                'newcondition' => ['p_status' => 'Active']
            ];
            $projects = $projectModel->getRecords($params);

            $userModel = new UserModel();
            $users = $userModel->getActiveEmployees();
            $this->view_data['users'] = $users;
        } else {
            $u_id = $this->admin_session['u_id'];
            $projects = $projectModel->getProjectsByUser($u_id);
        }

        $this->view_data['projects'] = $projects;
        $this->view_data['u_type'] = $this->admin_session['u_type'];
        $this->view_data['page'] = 'tasks';
        $this->view_data['meta_title'] = 'Tasks';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true, 'select2' => true];

        return view('template', ['view_data' => $this->view_data]);
    }

    public function taskView($p_id, $t_id)
    {
        $db = \Config\Database::connect();

        // Get project
        $project = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
        if (!$project) {
            return redirect()->to(base_url('home/tasks'));
        }

        // Get task with creator name
        $task = $db->table('aa_tasks T')
            ->select('T.*, U.u_name')
            ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
            ->where('T.t_id', $t_id)
            ->get()->getRowArray();
        if (!$task) {
            return redirect()->to(base_url('home/tasks'));
        }

        // Get assigned users
        $assigns = $db->table('aa_task2user TU')
            ->select('TU.*, U.u_name, U.u_id')
            ->join('aa_users U', 'TU.tu_u_id = U.u_id')
            ->where('TU.tu_t_id', $t_id)
            ->where('TU.tu_removed', 'No')
            ->get()->getResultArray();
        $task['assigns'] = $assigns;

        // Get task files
        $files = $db->table('aa_task_files')->where('tf_t_id', $t_id)->get()->getResultArray();
        $task['files'] = $files;

        // Get project team
        $teamIds = [];
        if (!empty($project['p_leader'])) {
            $teamIds = array_merge($teamIds, explode(',', $project['p_leader']));
        }
        // Get users assigned to tasks in this project
        $projectUsers = $db->table('aa_task2user')
            ->distinct()
            ->select('tu_u_id')
            ->where('tu_p_id', $p_id)
            ->where('tu_removed', 'No')
            ->get()->getResultArray();
        foreach ($projectUsers as $pu) {
            $teamIds[] = $pu['tu_u_id'];
        }
        $teamIds = array_filter(array_unique($teamIds));
        $team = [];
        if (!empty($teamIds)) {
            $team = $db->table('aa_users')->whereIn('u_id', $teamIds)->get()->getResultArray();
        }
        $task['team'] = $team;

        $return_url = $_SERVER['HTTP_REFERER'] ?? base_url('home/tasks');

        $this->view_data['page'] = 'view_task';
        $this->view_data['meta_title'] = 'View Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['t_id'] = $t_id;
        $this->view_data['p_id'] = $p_id;
        $this->view_data['project'] = $project;
        $this->view_data['task'] = $task;
        $this->view_data['return_url'] = $return_url;
        $this->view_data['act'] = 'view';
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function taskEdit($p_id, $t_id)
    {
        $db = \Config\Database::connect();
        $project = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
        if (!$project) {
            return redirect()->to(base_url('home/tasks'));
        }

        $task = $db->table('aa_tasks T')
            ->select('T.*, U.u_name')
            ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
            ->where('T.t_id', $t_id)
            ->get()->getRowArray();
        if (!$task) {
            return redirect()->to(base_url('home/tasks'));
        }

        $assigns = $db->table('aa_task2user TU')
            ->select('TU.*, U.u_name, U.u_id')
            ->join('aa_users U', 'TU.tu_u_id = U.u_id')
            ->where('TU.tu_t_id', $t_id)
            ->where('TU.tu_removed', 'No')
            ->get()->getResultArray();
        $task['assigns'] = $assigns;

        $files = $db->table('aa_task_files')->where('tf_t_id', $t_id)->get()->getResultArray();
        $task['files'] = $files;

        $employees = $db->table('aa_users')
            ->where('u_status', 'Active')
            ->groupStart()
                ->where('u_type', 'Project Leader')
                ->orWhere('u_type', 'Employee')
            ->groupEnd()
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        $this->view_data['act'] = 'edit';
        $this->view_data['p_id'] = $p_id;
        $this->view_data['t_p_id'] = $p_id;
        $this->view_data['project'] = $project;
        $this->view_data['task'] = $task;
        $this->view_data['t_id'] = $t_id;
        $this->view_data['employees'] = $employees;
        $this->view_data['priorities'] = range(1, 20);
        $this->view_data['return_url'] = $_SERVER['HTTP_REFERER'] ?? base_url('home/tasks');
        $this->view_data['page'] = 'task';
        $this->view_data['meta_title'] = 'Edit Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true, 'datepicker' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function taskSub($p_id, $t_id)
    {
        $db = \Config\Database::connect();

        // Get project
        $project = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
        if (!$project) {
            return redirect()->to(base_url('home/tasks'));
        }

        // Get parent task
        $task = $db->table('aa_tasks')->where('t_id', $t_id)->get()->getRowArray();
        if (!$task || $task['t_parent'] != 0) {
            return redirect()->to(base_url('home/tasks'));
        }

        $this->view_data['page'] = 'task_sub';
        $this->view_data['meta_title'] = 'Sub Tasks';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['t_id'] = $t_id;
        $this->view_data['t_p_id'] = $p_id;
        $this->view_data['project'] = $project;
        $this->view_data['task'] = $task;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function projects()
    {
        $userModel = new UserModel();
        $params = [
            'newcondition' => [
                'u_type' => 'Project Leader',
                'u_status' => 'Active'
            ],
            'select_list' => 'u_id,u_name'
        ];
        $users = $userModel->getRecords($params);
        $this->view_data['p_leader'] = $users;

        // Project categories - default values
        $this->view_data['p_cat'] = ['BIM', 'Consultancy', 'Others'];

        $this->view_data['page'] = 'projects';
        $this->view_data['meta_title'] = 'Projects';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];

        return view('template', ['view_data' => $this->view_data]);
    }

    public function employees()
    {
        $this->view_data['page'] = 'employees';
        $this->view_data['meta_title'] = 'Employees';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];

        return view('template', ['view_data' => $this->view_data]);
    }

    public function fetchMessages()
    {
        $u_id = $this->admin_session['u_id'] ?? 0;
        $db = \Config\Database::connect();
        $messages = [];
        try {
            // Query aa_message (CI3-compatible: inbox alerts shown with different colors based on type)
            $messages = $db->query("
                SELECT M.me_id, M.me_text, M.me_p_id,
                       COALESCE(M.leave_message, 'No') AS leave_message,
                       COALESCE(M.conference_message, 'No') AS conference_message,
                       COALESCE(M.task_message, 'No') AS task_message,
                       COALESCE(M.birthday_message, 'No') AS birthday_message,
                       P.p_name
                FROM aa_message M
                LEFT JOIN aa_projects P ON P.p_id = M.me_p_id
                INNER JOIN aa_message_users MU ON MU.mu_me_id = M.me_id
                WHERE MU.mu_u_id = " . intval($u_id) . "
                  AND (MU.mu_read IS NULL OR MU.mu_read = 0)
                ORDER BY M.me_id DESC
            ")->getResultArray();
        } catch (\Exception $e) {
            $messages = [];
        }

        $this->session->set('messages', $messages);
        return $this->response->setJSON($messages);
    }

    // Stub methods for menu routes
    public function messages()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        // Get all Project Leaders for the leaders filter (for admin roles)
        $db = \Config\Database::connect();
        $allLeaders = $db->table('aa_users')
            ->select('u_id, u_name')
            ->where('u_status', 'Active')
            ->where('u_type', 'Project Leader')
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        $this->view_data['page'] = 'messages';
        $this->view_data['meta_title'] = 'Mail Links';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['users'] = $users;
        $this->view_data['allLeaders'] = $allLeaders;
        $this->view_data['plugins'] = ['datatable' => true, 'select2' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function settings()
    {
        $this->view_data['page'] = 'settings';
        $this->view_data['meta_title'] = 'Settings';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function leaves()
    {
        $db = \Config\Database::connect();
        $u_type = $this->admin_session['u_type'] ?? '';
        $u_id   = $this->admin_session['u_id'] ?? 0;

        $countSql = "SELECT L.l_status, COUNT(*) as cnt FROM aa_leaves L
            LEFT JOIN aa_users U ON L.l_u_id = U.u_id";
        if ($u_type === 'Project Leader') {
            $countSql .= " WHERE U.u_leader = " . intval($u_id);
        }
        $countSql .= " GROUP BY L.l_status";

        $leave_counts = ['Pending' => 0, 'Approved' => 0, 'Declined' => 0];
        try {
            foreach ($db->query($countSql)->getResultArray() as $row) {
                $leave_counts[$row['l_status']] = (int)$row['cnt'];
            }
        } catch (\Exception $e) {
            // use defaults
        }

        $this->view_data['leave_counts'] = $leave_counts;
        $this->view_data['page'] = 'leaves';
        $this->view_data['meta_title'] = 'Leave Request';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function holidays()
    {
        $this->view_data['page'] = 'holidays';
        $this->view_data['meta_title'] = 'Holidays';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function timesheet()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = "01-04-" . $year;
        $this->view_data['rpt_end'] = date("d-m-Y");

        $db = \Config\Database::connect();
        $at_days_back = $db->table('aa_settings')->where('s_key', 'at_days_back')->get()->getRowArray();
        $this->view_data['at_days_back'] = $at_days_back ? $at_days_back['s_value'] : 7;

        $this->view_data['page'] = 'timesheet';
        $this->view_data['meta_title'] = 'Timesheet';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function empattendance()
    {
        $db = \Config\Database::connect();

        $at_days_back = $db->table('aa_settings')->where('s_key', 'at_days_back')->get()->getRowArray();
        $this->view_data['at_days_back'] = $at_days_back ? $at_days_back['s_value'] : 7;

        $userModel = new UserModel();
        $users = $userModel->where('u_status', 'Active')->orderBy('u_name', 'ASC')->findAll();
        $this->view_data['users'] = $users;

        $this->view_data['page'] = 'empattendance';
        $this->view_data['meta_title'] = 'Employee Attendance';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function dependency()
    {
        $db = \Config\Database::connect();

        // Get projects for dropdown
        $u_type = $this->admin_session['u_type'] ?? '';
        $u_id = $this->admin_session['u_id'] ?? '';

        if (in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head', 'TaskCoordinator', 'MailCoordinator'])) {
            $projects = $db->table('aa_projects')->where('p_status', 'Active')->orderBy('p_name', 'ASC')->get()->getResultArray();
        } elseif ($u_type === 'Project Leader') {
            // Project Leader: only show projects where they are p_leader
            $projects = $db->table('aa_projects')
                ->where('p_status', 'Active')
                ->like('p_leader', $u_id)
                ->orderBy('p_name', 'ASC')
                ->get()->getResultArray();
        } else {
            // Other users: projects where assigned to tasks
            $projects = $db->query("SELECT DISTINCT P.* FROM aa_projects P
                LEFT JOIN aa_task2user TU ON P.p_id = TU.tu_p_id AND TU.tu_removed = 'No'
                WHERE P.p_status = 'Active' AND TU.tu_u_id = '{$u_id}'
                ORDER BY P.p_name ASC")->getResultArray();
        }

        // Get employees for dropdown
        $empBuilder = $db->table('aa_users')
            ->where('u_status', 'Active')
            ->whereIn('u_type', ['Project Leader', 'Employee']);

        // Project Leader: only show employees assigned to them
        if ($u_type === 'Project Leader') {
            $empBuilder->where('u_leader', $u_id);
        }

        $employees = $empBuilder->orderBy('u_name', 'ASC')->get()->getResultArray();

        // Add the logged-in Project Leader themselves to the employee list
        if ($u_type === 'Project Leader') {
            $leaderInList = array_filter($employees, fn($e) => $e['u_id'] == $u_id);
            if (empty($leaderInList)) {
                array_unshift($employees, [
                    'u_id'  => $this->admin_session['u_id'],
                    'u_name' => $this->admin_session['u_name'],
                ]);
            }
        }

        $this->view_data['projects'] = $projects;
        $this->view_data['employees'] = $employees;
        $this->view_data['page'] = 'dependency';
        $this->view_data['meta_title'] = 'Weekly Work + Dependency';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true, 'select2' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function dependencies()
    {
        $db = \Config\Database::connect();
        $u_type = $this->admin_session['u_type'] ?? '';
        $u_id = $this->admin_session['u_id'] ?? '';

        if (in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head', 'TaskCoordinator'])) {
            $projects = $db->table('aa_projects')->select('p_id, p_name, p_number')->where('p_status', 'Active')->get()->getResultArray();
        } elseif ($u_type === 'Project Leader') {
            // Project Leader: only projects where they are p_leader
            $projects = $db->table('aa_projects')
                ->select('p_id, p_name, p_number')
                ->where('p_status', 'Active')
                ->like('p_leader', $u_id)
                ->orderBy('p_name', 'ASC')
                ->get()->getResultArray();
        } else {
            // Other users: projects where assigned to tasks
            $projects = $db->query("SELECT DISTINCT P.p_id, P.p_name, P.p_number FROM aa_projects P
                LEFT JOIN aa_task2user TU ON P.p_id = TU.tu_p_id AND TU.tu_removed = 'No'
                WHERE P.p_status = 'Active' AND TU.tu_u_id = '{$u_id}'
                ORDER BY P.p_name ASC")->getResultArray();
        }

        $leaders = $db->table('aa_users')
            ->select('u_id, u_name, u_type')
            ->whereIn('u_type', ['Project Leader', 'Master Admin', 'Bim Head', 'TaskCoordinator'])
            ->where('u_status', 'Active')
            ->get()->getResultArray();

        $this->view_data['projects'] = $projects;
        $this->view_data['leaders'] = $leaders;
        $this->view_data['page'] = 'dependency/dependencies';
        $this->view_data['meta_title'] = 'Dependencies';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    // Report methods
    public function report_daily()
    {
        $rpt_start = $this->request->getGet('rpt_start');
        $txt_search = $this->request->getGet('txt_search');
        $this->view_data['rpt_start'] = $rpt_start ?? date("d-m-Y");
        $this->view_data['txt_search'] = $txt_search;
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['type'] = 'daily';
        $this->view_data['page'] = 'report_attendance';
        $this->view_data['page_title'] = 'Daily Report';
        $this->view_data['meta_title'] = 'Daily Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_employee()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $txt_search = $this->request->getGet('txt_search');
        $this->view_data['rpt_start'] = $this->request->getGet('rpt_start') ?? "01-04-" . $year;
        $this->view_data['rpt_end'] = $this->request->getGet('rpt_end') ?? date("d-m-Y");
        $this->view_data['txt_search'] = $txt_search;
        $this->view_data['type'] = 'employee';
        $this->view_data['page'] = 'report_attendance';
        $this->view_data['page_title'] = 'Employee Work Report';
        $this->view_data['meta_title'] = 'Employee Work Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_leader_employee()
    {
        $db = \Config\Database::connect();
        $today = date('d-m-Y');
        $dayOfWeek = date('w', strtotime($today));
        $monday = ($dayOfWeek == 0)
            ? date('d-m-Y', strtotime('last monday'))
            : date('d-m-Y', strtotime('monday this week'));
        $saturday = date('d-m-Y', strtotime($monday . ' +4 days'));

        $this->view_data['rpt_start'] = $this->request->getGet('rpt_start') ?? $monday;
        $this->view_data['rpt_end'] = $this->request->getGet('rpt_end') ?? $saturday;
        $this->view_data['txt_search'] = $this->request->getGet('txt_search') ?? '';
        $this->view_data['leader_id'] = $this->request->getGet('leader_id') ?? '';
        $this->view_data['leaders'] = $db->table('aa_users')
            ->select('u_id, u_name')
            ->where('u_type', 'Project Leader')
            ->where('u_status', 'Active')
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();
        $this->view_data['type'] = 'leader_employee';
        $this->view_data['page'] = 'report_leader_employee';
        $this->view_data['page_title'] = 'Leader Employee Report';
        $this->view_data['meta_title'] = 'Leader Employee Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function projectData()
    {
        $db = \Config\Database::connect();
        $u_type = $this->admin_session['u_type'];
        $u_id = $this->admin_session['u_id'];

        if (in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head'])) {
            $projects = $db->table('aa_projects')->select('p_id, p_number, p_name')->get()->getResultArray();
        } else {
            // Get projects where user is leader or assigned to tasks
            $projects = $db->query("SELECT DISTINCT P.p_id, P.p_number, P.p_name FROM aa_projects P
                LEFT JOIN aa_task2user TU ON P.p_id = TU.tu_p_id AND TU.tu_removed = 'No'
                WHERE P.p_status = 'Active' AND (P.p_leader LIKE '%{$u_id}%' OR TU.tu_u_id = '{$u_id}')
                ORDER BY P.p_name ASC")->getResultArray();
        }

        $leaders = $db->table('aa_users')
            ->select('u_id, u_name, u_status')
            ->whereIn('u_type', ['Project Leader', 'Master Admin', 'Bim Head', 'Super Admin'])
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        $this->view_data['projects'] = $projects;
        $this->view_data['leaders'] = $leaders;
        $this->view_data['weekly_works'] = [];
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['page'] = 'dependency/project_data';
        $this->view_data['meta_title'] = 'Project Data';
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_attendance_employee()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $txt_search = $this->request->getGet('txt_search');
        $this->view_data['rpt_start'] = $this->request->getGet('rpt_start') ?? "01-04-" . $year;
        $this->view_data['rpt_end'] = $this->request->getGet('rpt_end') ?? date("d-m-Y");
        $this->view_data['txt_search'] = $txt_search;
        $this->view_data['type'] = 'employee';
        $this->view_data['page'] = 'report_attendance_employee';
        $this->view_data['page_title'] = 'Employee Attendance Report';
        $this->view_data['meta_title'] = 'Employee Attendance Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_employee_salary()
    {
        $this->view_data['type'] = 'employee';
        $this->view_data['page'] = 'report_employee_salary';
        $this->view_data['page_title'] = 'Employee Salary Report';
        $this->view_data['meta_title'] = 'Employee Salary Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_dependency()
    {
        $db = \Config\Database::connect();
        $projects = $db->table('aa_projects')->orderBy('p_name', 'ASC')->get()->getResultArray();
        $this->view_data['projects'] = $projects;
        $this->view_data['page'] = 'report_dependency';
        $this->view_data['meta_title'] = 'Dependency';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_estimated_actual()
    {
        $this->view_data['page'] = 'report_estimated_actual';
        $this->view_data['meta_title'] = 'Estimated Hours v/s Actual Hours';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_leave_date()
    {
        $this->view_data['rpt_start'] = date('d-m-Y', strtotime("-1 days"));
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['page'] = 'report_leaves_date';
        $this->view_data['meta_title'] = 'Leave Report - Datewise';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_leave()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = "01-04-" . $year;
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['page'] = 'report_leaves';
        $this->view_data['meta_title'] = 'Leave Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_leave_total()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = "01-04-" . $year;
        $this->view_data['rpt_end'] = "31-03-" . ($year + 1);
        $this->view_data['page'] = 'report_leave_total';
        $this->view_data['meta_title'] = 'Leave Total Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_leave_hour_date()
    {
        $this->view_data['rpt_start'] = date('d-m-Y', strtotime("-1 days"));
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['page'] = 'report_leave_hour_date';
        $this->view_data['meta_title'] = 'Hourly Leave Report - Datewise';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_leave_hour()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = "01-04-" . $year;
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['page'] = 'report_leave_hour';
        $this->view_data['meta_title'] = 'Hourly Leave Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_message()
    {
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = "01-04-" . $year;
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['page'] = 'report_message';
        $this->view_data['meta_title'] = 'Message Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_project()
    {
        $db = \Config\Database::connect();
        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = "01-04-" . $year;
        $this->view_data['rpt_end'] = date("d-m-Y");
        $this->view_data['p_cat'] = ['BIM', 'Consultancy', 'Others'];

        $projectModel = new ProjectModel();
        if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
            $projects = $projectModel->orderBy('p_name', 'ASC')->findAll();

            $userModel = new UserModel();
            $users = $userModel->where('u_status', 'Active')->findAll();
            $this->view_data['users'] = $users;
        } else {
            $u_id = $this->admin_session['u_id'];
            $projects = $db->query("SELECT DISTINCT(p_id), P.p_name, P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New') AND u_id = '{$u_id}'")->getResultArray();
        }
        $this->view_data['projects'] = $projects;

        $this->view_data['u_type'] = $this->admin_session['u_type'];
        $this->view_data['page'] = 'report_projects';
        $this->view_data['page_title'] = 'Project Report';
        $this->view_data['meta_title'] = 'Project Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function export_project_report()
    {
        $session = session();
        $admin_session = $session->get('admin_session');
        if (!$admin_session) return redirect()->to(base_url('auth/login'));

        $db = \Config\Database::connect();
        $txt_p_status = $this->request->getPost('txt_p_status') ?? '';
        $t_p_id = $this->request->getPost('t_p_id') ?? 0;
        $txt_p_cat = $this->request->getPost('txt_p_cat') ?? '';

        $builder = $db->table('aa_tasks T');
        $builder->select('P.p_name, P.p_id, P.p_number, P.p_value, P.p_cat, P.p_status');
        $builder->join('aa_projects P', 'P.p_id = T.t_p_id', 'left');
        $builder->distinct();
        if ($txt_p_cat) $builder->where('P.p_cat', $txt_p_cat);
        if ($txt_p_status) $builder->where('P.p_status', $txt_p_status);
        if ($t_p_id > 0) $builder->where('T.t_p_id', $t_p_id);
        $builder->where('T.t_parent', 0);
        $builder->orderBy('P.p_name', 'ASC');
        $projects = $builder->get()->getResultArray();

        $tasks_by_project = [];
        $assigns_all = [];
        $hours_all = [];
        if (!empty($projects)) {
            $p_ids = array_column($projects, 'p_id');
            $tasks_all = $db->table('aa_tasks T')
                ->select('T.t_id, T.t_p_id, T.t_title, T.t_priority, T.t_createdate, U.u_name as posted_by')
                ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
                ->whereIn('T.t_p_id', $p_ids)
                ->where('T.t_parent', 0)
                ->orderBy('T.t_p_id', 'ASC')
                ->orderBy('T.t_title', 'ASC')
                ->get()->getResultArray();
            $t_ids = array_column($tasks_all, 't_id');
            foreach ($tasks_all as $task) {
                $tasks_by_project[$task['t_p_id']][] = $task;
            }
            if (!empty($t_ids)) {
                $assigns_raw = $db->query("SELECT TU.tu_t_id, TU.tu_u_id, U.u_name FROM aa_task2user TU LEFT JOIN aa_users U ON TU.tu_u_id = U.u_id WHERE TU.tu_t_id IN (" . implode(',', $t_ids) . ")")->getResultArray();
                foreach ($assigns_raw as $a) {
                    $assigns_all[$a['tu_t_id']][] = $a;
                }
                $hours_raw = $db->query("SELECT at_t_id, at_u_id, SUM((at_end - at_start) / 60) as TOTALwhours FROM aa_attendance WHERE at_t_id IN (" . implode(',', $t_ids) . ") GROUP BY at_t_id, at_u_id")->getResultArray();
                foreach ($hours_raw as $h) {
                    $hours_all[$h['at_t_id']][$h['at_u_id']] = $h['TOTALwhours'];
                }
            }
        }

        $convertHours = function ($n) {
            $whole = floor($n);
            $fraction = $n - $whole;
            if ($fraction == 0.75) return $whole + 0.45;
            elseif ($fraction == 0.25) return $whole + 0.15;
            elseif ($fraction == 0.50) return $whole + 0.30;
            else return $whole + $fraction;
        };

        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="project_report_' . date('d-m-Y') . '.xls"');
        header('Cache-Control: max-age=0');

        $show_value = ($admin_session['u_type'] == 'Master Admin');

        $td  = 'style="font-family:Arial;font-size:10pt;vertical-align:middle;padding:3px 6px;border:1px solid #999;"';
        $tdTitle  = 'style="font-family:Arial;font-size:13pt;font-weight:bold;text-align:center;background-color:#1F4E79;color:#FFFFFF;padding:4px 6px;border:1px solid #999;"';
        $tdColHdr = 'style="font-family:Arial;font-size:10pt;font-weight:bold;background-color:#D9E1F2;padding:3px 6px;border:1px solid #999;"';
        $tdProj   = 'style="font-family:Arial;font-size:11pt;font-weight:bold;background-color:#FFFF00;padding:3px 6px;border:1px solid #999;"';
        $tdSec    = 'style="font-family:Arial;font-size:10pt;font-weight:bold;color:#FF0000;text-align:center;padding:3px 6px;border:1px solid #999;"';
        $tdTotal  = 'style="font-family:Arial;font-size:10pt;font-weight:bold;padding:3px 6px;border:1px solid #999;"';

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" style="border-collapse:collapse;">';

        $total_cols = $show_value ? 6 : 5;

        // Title row
        echo '<tr><td colspan="' . $total_cols . '" ' . $tdTitle . '>Project Report</td></tr>';

        // Column sub-headings
        echo '<tr>';
        echo '<td ' . $tdColHdr . '>Sr.</td>';
        echo '<td ' . $tdColHdr . '>Project Name</td>';
        echo '<td ' . $tdColHdr . '>Project Number</td>';
        echo '<td ' . $tdColHdr . '>Project Category</td>';
        echo '<td ' . $tdColHdr . '>Status</td>';
        if ($show_value) echo '<td ' . $tdColHdr . '>Project Value</td>';
        echo '</tr>';

        $sr = 1;
        foreach ($projects as $project) {
            $p_id = $project['p_id'];
            $tasks_list = $tasks_by_project[$p_id] ?? [];

            // Project header row
            echo '<tr>';
            echo '<td ' . $tdProj . '>' . $sr++ . '</td>';
            echo '<td ' . $tdProj . '>' . htmlspecialchars($project['p_name']) . '</td>';
            echo '<td ' . $tdProj . '>' . htmlspecialchars($project['p_number']) . '</td>';
            echo '<td ' . $tdProj . '>' . htmlspecialchars($project['p_cat']) . '</td>';
            echo '<td ' . $tdProj . '>' . htmlspecialchars($project['p_status']) . '</td>';
            if ($show_value) echo '<td ' . $tdProj . '>' . htmlspecialchars($project['p_value']) . '</td>';
            echo '</tr>';

            // Tasks / Assigns section header
            echo '<tr>';
            echo '<td colspan="2" ' . $tdSec . '>Tasks</td>';
            echo '<td colspan="' . ($total_cols - 2) . '" ' . $tdSec . '>Assigns</td>';
            echo '</tr>';

            // Column header
            echo '<tr>';
            echo '<td ' . $tdColHdr . '></td><td ' . $tdColHdr . '></td>';
            echo '<td ' . $tdColHdr . '>Employee Name</td><td ' . $tdColHdr . '>Hrs</td><td ' . $tdColHdr . '>Cost</td>';
            if ($show_value) echo '<td ' . $tdColHdr . '></td>';
            echo '</tr>';

            if (empty($tasks_list)) {
                echo '<tr><td colspan="' . $total_cols . '" ' . $td . '>No Tasks</td></tr>';
            } else {
                $task_sr = 1;
                foreach ($tasks_list as $task) {
                    $assigns = $assigns_all[$task['t_id']] ?? [];
                    $total_hrs = 0;
                    $first_row = true;

                    if (empty($assigns)) {
                        echo '<tr>';
                        echo '<td ' . $td . '>' . $task_sr . '</td>';
                        echo '<td ' . $td . '>' . htmlspecialchars($task['t_title']) . '</td>';
                        echo '<td colspan="' . ($total_cols - 2) . '" ' . $td . '>No Assigns</td>';
                        echo '</tr>';
                    } else {
                        foreach ($assigns as $assign) {
                            $raw = $hours_all[$task['t_id']][$assign['tu_u_id']] ?? 0;
                            $hrs = $convertHours($raw);
                            $total_hrs += $hrs;
                            echo '<tr>';
                            if ($first_row) {
                                echo '<td ' . $td . '>' . $task_sr . '</td>';
                                echo '<td ' . $td . '>' . htmlspecialchars($task['t_title']) . '</td>';
                                $first_row = false;
                            } else {
                                echo '<td ' . $td . '></td><td ' . $td . '></td>';
                            }
                            echo '<td ' . $td . '>' . htmlspecialchars($assign['u_name']) . '</td>';
                            echo '<td ' . $td . '>' . number_format($hrs, 2) . '</td>';
                            echo '<td ' . $td . '></td>';
                            if ($show_value) echo '<td ' . $td . '></td>';
                            echo '</tr>';
                        }
                        // Total row
                        echo '<tr>';
                        echo '<td ' . $tdTotal . '></td><td ' . $tdTotal . '></td>';
                        echo '<td colspan="2" ' . $tdTotal . '>TOTAL HRS - ' . number_format($total_hrs, 2) . '</td>';
                        echo '<td ' . $tdTotal . '></td>';
                        if ($show_value) echo '<td ' . $tdTotal . '></td>';
                        echo '</tr>';
                    }
                    $task_sr++;
                }
            }

            // Blank separator row between projects
            echo '<tr><td colspan="' . $total_cols . '" ' . $td . '>&nbsp;</td></tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    public function report_project_employee()
    {
        $db = \Config\Database::connect();
        $projectModel = new ProjectModel();
        if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
            $projects = $projectModel->where('p_status', 'Active')->orderBy('p_name', 'ASC')->findAll();

            $userModel = new UserModel();
            $users = $userModel->where('u_status', 'Active')->findAll();
            $this->view_data['users'] = $users;
        } else {
            $u_id = $this->admin_session['u_id'];
            $projects = $db->query("SELECT DISTINCT(p_id), P.p_name, P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New') AND u_id = '{$u_id}'")->getResultArray();
        }
        $this->view_data['projects'] = $projects;

        $this->view_data['u_type'] = $this->admin_session['u_type'];
        $this->view_data['page'] = 'report_projects_employee';
        $this->view_data['meta_title'] = 'Project Employee Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_employee_work()
    {
        $db = \Config\Database::connect();
        $projectModel = new ProjectModel();
        $userModel = new UserModel();

        $u_type = $this->admin_session['u_type'];
        $u_id = $this->admin_session['u_id'];

        if ($this->authorization->is_bim_head_or_higher($this->admin_session) || $u_type === 'Project Leader') {
            $users = $userModel->where('u_status', 'Active')->orderBy('u_name', 'ASC')->findAll();
            $projects = $projectModel->orderBy('p_name', 'ASC')->findAll();
        } else {
            $users = $userModel->where('u_id', $u_id)->findAll();
            $projects = $db->query("SELECT DISTINCT P.p_id, P.p_name FROM aa_task2user TU INNER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_u_id = '{$u_id}' AND TU.tu_removed = 'No' ORDER BY P.p_name ASC")->getResultArray();
        }

        $year = date("Y");
        $month = (int)date("m");
        if ($month < 4) $year--;
        $this->view_data['rpt_start'] = '';
        $this->view_data['rpt_end'] = '';
        $this->view_data['users'] = $users;
        $this->view_data['projects'] = $projects;
        $this->view_data['u_type'] = $u_type;
        $this->view_data['page'] = 'report_employee_work';
        $this->view_data['page_title'] = 'Employee Work Report';
        $this->view_data['meta_title'] = 'Employee Work Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function export_employee_work_report()
    {
        $session = session();
        $admin_session = $session->get('admin_session');
        if (!$admin_session) return redirect()->to(base_url('auth/login'));

        $db = \Config\Database::connect();
        $emp_u_id = $this->request->getPost('emp_u_id') ?? '';
        $p_id = $this->request->getPost('p_id') ?? '';
        $rpt_start = convert_display2db($this->request->getPost('rpt_start') ?? '');
        $rpt_end = convert_display2db($this->request->getPost('rpt_end') ?? '');

        if (!$emp_u_id && !$p_id) {
            echo 'Please select an Employee or a Project.';
            exit;
        }

        $convertHours = function ($n) {
            $whole = floor($n);
            $fraction = $n - $whole;
            if ($fraction == 0.75) return $whole + 0.45;
            elseif ($fraction == 0.25) return $whole + 0.15;
            elseif ($fraction == 0.50) return $whole + 0.30;
            else return $whole + $fraction;
        };

        $builder = $db->table('aa_attendance A');
        $builder->select('A.at_date, U.u_name, P.p_name, T.t_title, MIN(A.at_start) as min_start, MAX(A.at_end) as max_end, SUM((A.at_end - A.at_start) / 60) as work_hours, GROUP_CONCAT(A.at_comment ORDER BY A.at_start SEPARATOR \'; \') as comments');
        $builder->join('aa_users U', 'U.u_id = A.at_u_id', 'left');
        $builder->join('aa_projects P', 'P.p_id = A.at_p_id', 'left');
        $builder->join('aa_tasks T', 'T.t_id = A.at_t_id', 'left');
        if ($emp_u_id) $builder->where('A.at_u_id', $emp_u_id);
        if ($rpt_start) $builder->where('A.at_date >=', $rpt_start);
        if ($rpt_end) $builder->where('A.at_date <=', $rpt_end);
        if ($p_id) $builder->where('A.at_p_id', $p_id);
        $builder->groupBy('A.at_date, A.at_u_id, A.at_p_id, A.at_t_id');
        $builder->orderBy('A.at_date', 'ASC');
        $builder->orderBy('U.u_name', 'ASC');
        $builder->orderBy('P.p_name', 'ASC');
        $records = $builder->get()->getResultArray();

        // Header label
        if ($emp_u_id) {
            $user = $db->table('aa_users')->select('u_name')->where('u_id', $emp_u_id)->get()->getRowArray();
            $header_label = 'Employee: ' . ($user['u_name'] ?? '');
        } else {
            $proj = $db->table('aa_projects')->select('p_name')->where('p_id', $p_id)->get()->getRowArray();
            $header_label = 'Project: ' . ($proj['p_name'] ?? '');
        }

        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="employee_work_report_' . date('d-m-Y') . '.xls"');
        header('Cache-Control: max-age=0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><style>
            td { font-family: Arial; font-size: 10pt; padding: 3px 6px; vertical-align: middle; }
            .rpt-header { background-color: #FFFF00; font-weight: bold; font-size: 11pt; }
            .col-header { background-color: #EEEEEE; font-weight: bold; }
            .total-row { background-color: #FFFFCC; font-weight: bold; }
        </style></head><body>';
        echo '<table border="1" style="border-collapse:collapse;">';

        // Report header row
        echo '<tr class="rpt-header">';
        echo '<td colspan="5">' . htmlspecialchars($header_label) . '</td>';
        echo '<td colspan="4">Period: ' . ($this->request->getPost('rpt_start') ?? '') . ' to ' . ($this->request->getPost('rpt_end') ?? '') . '</td>';
        echo '</tr>';

        // Column headers
        echo '<tr class="col-header">';
        echo '<td>Sr.</td><td>Date</td><td>Employee</td><td>Project Name</td><td>Task Name</td><td>Start Time</td><td>End Time</td><td>Hours</td><td>Comment</td>';
        echo '</tr>';

        $i = 1;
        $total_hrs = 0;
        foreach ($records as $rec) {
            $hrs = $convertHours($rec['work_hours'] ?? 0);
            $total_hrs += $hrs;
            echo '<tr>';
            echo '<td>' . $i++ . '</td>';
            echo '<td>' . convert_db2display($rec['at_date']) . '</td>';
            echo '<td>' . htmlspecialchars($rec['u_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($rec['p_name'] ?? 'Leave') . '</td>';
            echo '<td>' . htmlspecialchars($rec['t_title'] ?? 'Leave') . '</td>';
            echo '<td>' . RevTime((int)($rec['min_start'] ?? 0)) . '</td>';
            echo '<td>' . RevTime((int)($rec['max_end'] ?? 0)) . '</td>';
            echo '<td>' . number_format($hrs, 2) . '</td>';
            echo '<td>' . htmlspecialchars($rec['comments'] ?? '') . '</td>';
            echo '</tr>';
        }

        // Total row
        echo '<tr class="total-row">';
        echo '<td colspan="7" style="text-align:right;">Total Hours:</td>';
        echo '<td>' . number_format($total_hrs, 2) . '</td>';
        echo '<td></td>';
        echo '</tr>';

        echo '</table></body></html>';
        exit;
    }

    public function report_profitloss()
    {
        $this->view_data['p_cat'] = ['BIM', 'Consultancy', 'Others'];
        $this->view_data['page'] = 'report_profitloss';
        $this->view_data['page_title'] = 'Profit/Loss Report';
        $this->view_data['meta_title'] = 'Profit/Loss Report';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true, 'datepicker' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function report_timesheet()
    {
        $rpt_start = $this->request->getPost('rpt_start');
        $rpt_end = $this->request->getPost('rpt_end');
        $txt_search = $this->request->getPost('txt_search');
        $leader_id = $this->request->getPost('leader_id');
        $u_name = $this->request->getPost('u_name');
        $u_id = $this->request->getPost('u_id');
        $type = $this->request->getPost('type');
        $this->view_data['txt_search'] = $txt_search;
        $this->view_data['rpt_start'] = $rpt_start;
        $this->view_data['rpt_end'] = $rpt_end;
        $this->view_data['type'] = $type;
        $this->view_data['u_id'] = $u_id;
        $this->view_data['leader_id'] = $leader_id;
        $this->view_data['page'] = 'report_timesheet';
        $this->view_data['page_title'] = (($type == "daily") ? "Daily Report: " : "Employee Work Report: ") . $u_name . " | " . (($type == "daily") ? $rpt_start : $rpt_start . " to " . $rpt_end);
        $this->view_data['meta_title'] = (($type == "daily") ? "Daily Report Detail" : "Employee Work Report Detail");
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'datepicker' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function presentlist()
    {
        $this->view_data['page'] = 'presentlist';
        $this->view_data['meta_title'] = 'Employee Present Departmentwise';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function projectsunderwatch()
    {
        $this->view_data['page'] = 'projects_under_watch';
        $this->view_data['meta_title'] = 'Projects Under Watch';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function todayleaves()
    {
        $this->view_data['page'] = 'today_leaves';
        $this->view_data['meta_title'] = 'Today Employees on Leave';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function project_contacts($p_id = null)
    {
        $db = \Config\Database::connect();
        $project = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
        if (!$project) {
            return redirect()->to(base_url('home/projects'));
        }
        $this->view_data['p_id'] = $p_id;
        $this->view_data['project'] = $project;
        $this->view_data['page'] = 'project_contacts';
        $this->view_data['meta_title'] = 'Project Contacts';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function project_detail($p_id = null)
    {
        $db = \Config\Database::connect();
        $project = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
        if (!$project) {
            return redirect()->to(base_url('home/projects'));
        }
        $this->view_data['p_id'] = $p_id;
        $this->view_data['project'] = $project;
        $this->view_data['page'] = 'project_detail';
        $this->view_data['meta_title'] = 'Project Detail:: ' . $project['p_name'];
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function download($type, $id)
    {
        $db = \Config\Database::connect();
        if ($type == "task") {
            $result = $db->table('aa_task_files')->where('tf_id', $id)->get()->getRowArray();
            if ($result) {
                return $this->response->download(FCPATH . 'uploads/tasks/' . $result['tf_file_name'], null)->setFileName($result['tf_title']);
            }
        } else if ($type == "tm") {
            $result = $db->table('aa_task_message')->where('tm_id', $id)->get()->getRowArray();
            if ($result && !empty($result['tm_file_name'])) {
                return $this->response->download(FCPATH . 'uploads/task_messages/' . $result['tm_file_name'], null)->setFileName($result['tm_file_name']);
            }
        }
        return redirect()->to(base_url('home'));
    }

    public function taskAdd($p_id, $t_id = 0)
    {
        $db = \Config\Database::connect();
        $project = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
        if (!$project) {
            return redirect()->to(base_url('home/tasks'));
        }

        // Get employees
        $employees = $db->table('aa_users')
            ->where('u_status', 'Active')
            ->groupStart()
                ->where('u_type', 'Project Leader')
                ->orWhere('u_type', 'Employee')
            ->groupEnd()
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        $task = null;
        if ($t_id > 0) {
            $task = $db->table('aa_tasks')->where('t_id', $t_id)->get()->getRowArray();
        }

        $this->view_data['act'] = 'add';
        $this->view_data['p_id'] = $p_id;
        $this->view_data['project'] = $project;
        $this->view_data['task'] = $task;
        $this->view_data['t_id'] = $t_id;
        $this->view_data['t_p_id'] = $p_id;
        $this->view_data['employees'] = $employees;
        $this->view_data['active_projects'] = [];
        $this->view_data['active_tasks'] = [];
        $this->view_data['leaves'] = [];
        $this->view_data['priorities'] = range(1, 20);
        $this->view_data['return_url'] = $_SERVER['HTTP_REFERER'] ?? base_url('home/tasks');
        $this->view_data['page'] = 'task';
        $this->view_data['meta_title'] = 'Add Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['plugins'] = ['datatable' => true, 'form_validation' => true];
        return view('template', ['view_data' => $this->view_data]);
    }
}
