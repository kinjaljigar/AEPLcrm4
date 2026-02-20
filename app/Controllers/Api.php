<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Api extends BaseController
{
    public function login()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');

        $username = $request->getPost('u_username');
        $password = $request->getPost('u_password');

        if ($username && $password) {
            $userModel = new UserModel();
            $user = $userModel->verifyLogin($username, $password);

            if ($user) {
                    // Force reset login status (handles browser crash / PC shutdown scenarios)
                    // If user was previously logged in, allow re-login by resetting the flag
                    $userModel->updateLoginStatus($user['u_id'], 1);

                    // Set session data - include all user fields
                    $admin_session = [
                        'u_id' => $user['u_id'],
                        'u_name' => $user['u_name'],
                        'u_username' => $user['u_username'],
                        'u_type' => $user['u_type'],
                        'u_email' => $user['u_email'],
                        'u_mobile' => $user['u_mobile'],
                        'u_app_auth' => $user['u_app_auth'] ?? '0'
                    ];

                    $session->set('admin_session', $admin_session);

                    // Record attendance in aa_present (INSERT IGNORE handles duplicates and race conditions silently)
                    $db = \Config\Database::connect();
                    $today = date('Y-m-d');
                    $db->query("INSERT IGNORE INTO aa_present (pr_u_id, pr_date) VALUES (?, ?)", [$user['u_id'], $today]);

                    // Fetch or generate JWT token for users with u_app_auth = 1
                    $token = '';
                    $canProceed = !empty($user['u_app_auth']) && $user['u_app_auth'] == 1;

                    if ($canProceed) {
                        $db = \Config\Database::connect();
                        $jwtKey = 'af0e4b7ca1c8e091fb9a781c9a2b5f07340ea4d88f96a3b5b1b9927710460f1a';
                        $issuedAt = time();
                        $expirationTime = $issuedAt + (7 * 24 * 60 * 60);
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

                    $session->set('token', $token);

                    // Determine redirect URL (matches CI3 logic exactly)
                    $authorization = new \App\Libraries\Authorization();
                    if ($authorization->is_role_allowed($user['u_type'], ['Bim Head', 'Master Admin', 'Project Leader', 'Super Admin', 'TaskCoordinator'])) {
                        $url = base_url('home/index');
                    } else if ($authorization->is_role_allowed($user['u_type'], ['Associate User'])) {
                        $url = base_url('usertask');
                    } else {
                        if ($user['u_username'] === 'aeplit') {
                            $url = base_url('ticket/assigned');
                        } else if ($user['u_type'] === 'MailCoordinator') {
                            $url = base_url('home/messages');
                        } else {
                            $url = base_url('home/tasks');
                        }
                    }

                    // Return JSON response
                    echo json_encode([
                        'status' => 'pass',
                        'url' => $url,
                        'message' => 'Login successful'
                    ]);
                    exit;
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

    public function dashboard()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $db = \Config\Database::connect();
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $u_type = $admin_session['u_type'] ?? '';
        $u_id = $admin_session['u_id'] ?? 0;
        $type = $request->getPost('type') ?? 'basic';

        // For Project Leader, filter by assigned projects
        $isLeader = ($u_type == 'Project Leader');

        switch ($type) {
            case 'basic':
                // Get project counts
                $pBuilder = $db->table('aa_projects');
                if ($isLeader) $pBuilder->where("FIND_IN_SET($u_id, p_leader)", null, false);
                $total_projects = $pBuilder->countAllResults();

                $pBuilder = $db->table('aa_projects')->where('p_status', 'Active');
                if ($isLeader) $pBuilder->where("FIND_IN_SET($u_id, p_leader)", null, false);
                $active_projects = $pBuilder->countAllResults();

                $pBuilder = $db->table('aa_projects')->where('p_status', 'Completed');
                if ($isLeader) $pBuilder->where("FIND_IN_SET($u_id, p_leader)", null, false);
                $completed_projects = $pBuilder->countAllResults();

                $pBuilder = $db->table('aa_projects')->where('p_status', 'Hold');
                if ($isLeader) $pBuilder->where("FIND_IN_SET($u_id, p_leader)", null, false);
                $hold_projects = $pBuilder->countAllResults();

                // Get task counts
                if ($isLeader) {
                    $leaderProjects = $db->table('aa_projects')->select('p_id')->where("FIND_IN_SET($u_id, p_leader)", null, false)->get()->getResultArray();
                    $projectIds = array_column($leaderProjects, 'p_id');
                    if (!empty($projectIds)) {
                        $total_tasks = $db->table('aa_tasks')->whereIn('t_p_id', $projectIds)->countAllResults();
                        $pending_tasks = $db->table('aa_tasks')->whereIn('t_p_id', $projectIds)->where('t_status', 'Pending')->countAllResults();
                        $inprogress_tasks = $db->table('aa_tasks')->whereIn('t_p_id', $projectIds)->where('t_status', 'In Progress')->countAllResults();
                        $completed_tasks = $db->table('aa_tasks')->whereIn('t_p_id', $projectIds)->where('t_status', 'Completed')->countAllResults();
                    } else {
                        $total_tasks = $pending_tasks = $inprogress_tasks = $completed_tasks = 0;
                    }
                } else {
                    $total_tasks = $db->table('aa_tasks')->countAllResults();
                    $pending_tasks = $db->table('aa_tasks')->where('t_status', 'Pending')->countAllResults();
                    $inprogress_tasks = $db->table('aa_tasks')->where('t_status', 'In Progress')->countAllResults();
                    $completed_tasks = $db->table('aa_tasks')->where('t_status', 'Completed')->countAllResults();
                }

                // Get employee counts
                $today = date('Y-m-d');
                if ($isLeader) {
                    // Project Leader: only employees under this leader
                    $total_employee = $db->table('aa_users')->where('u_leader', $u_id)->countAllResults();

                    // Active employees (present today) under this leader, grouped by department
                    $deptQuery = $db->query("SELECT COUNT(u_id) as total, u_department FROM aa_users JOIN aa_present ON u_id = pr_u_id WHERE pr_date = " . $db->escape($today) . " AND u_leader = " . $db->escape($u_id) . " GROUP BY u_department")->getResultArray();
                } else {
                    // Other roles: all users minus 2 (system accounts)
                    $allCount = $db->table('aa_users')->countAllResults();
                    $total_employee = ($allCount - 2);

                    // Active employees (present today) grouped by department
                    $deptQuery = $db->query("SELECT COUNT(u_id) as total, u_department FROM aa_users JOIN aa_present ON u_id = pr_u_id WHERE pr_date = " . $db->escape($today) . " GROUP BY u_department")->getResultArray();
                }

                // Build department presence counts
                $presents = ['Architecture' => 0, 'MEPF' => 0, 'Admin' => 0];
                $active_employees = 0;
                foreach ($deptQuery as $val) {
                    $presents[$val['u_department'] ?? 'Other'] = $val['total'];
                    $active_employees += $val['total'];
                }

                // Build department rows HTML (horizontal format like CI3)
                $r1 = '<thead><tr>';
                $r2 = '<tbody><tr>';
                foreach ($presents as $key => $val) {
                    $r1 .= '<th>' . htmlspecialchars($key) . '</th>';
                    $r2 .= '<td>' . $val . '</td>';
                }
                $r1 .= '</tr></thead>';
                $r2 .= '</tr></tbody>';
                $deptHtml = $r1 . $r2;

                echo json_encode([
                    'status' => 'pass',
                    'data' => [
                        'box' => [
                            'total_projects' => $total_projects,
                            'active_projects' => $active_projects,
                            'completed_projects' => $completed_projects,
                            'hold_projects' => $hold_projects,
                            'total_tasks' => $total_tasks,
                            'pending_tasks' => $pending_tasks,
                            'inprogress_tasks' => $inprogress_tasks,
                            'completed_tasks' => $completed_tasks,
                            'total_employee' => $total_employee,
                            'active_employees' => $active_employees
                        ],
                        'rows' => $deptHtml
                    ]
                ]);
                exit;

            case 'leaves':
                $leaderid = $request->getPost('leaderid');
                $builder = $db->table('aa_leaves L')
                    ->select('L.*, U.u_name')
                    ->join('aa_users U', 'L.l_u_id = U.u_id', 'left')
                    ->where('L.l_status', 'Pending')
                    ->orderBy('L.l_id', 'DESC')
                    ->limit(5);

                if (!empty($leaderid)) {
                    // For project leaders, show leaves of employees under them
                    $builder->where('U.u_leader', $leaderid);
                }

                $leaves = $builder->get()->getResultArray();
                $data = [];
                foreach ($leaves as $leave) {
                    $row = [];
                    $row[] = $leave['u_name'] ?? '';
                    $row[] = !empty($leave['l_create_date']) ? convert_db2display($leave['l_create_date'], false) : '';
                    $row[] = !empty($leave['l_from_date']) ? convert_db2display($leave['l_from_date'], false) : '';
                    $row[] = !empty($leave['l_to_date']) ? convert_db2display($leave['l_to_date'], false) : '';

                    // Hourly / Half Day Leave
                    $leaveType = '';
                    if (($leave['l_is_halfday'] ?? '') === 'Yes') $leaveType = 'Half Day (' . ($leave['l_halfday_time'] ?? '') . ')';
                    elseif (($leave['l_is_hourly'] ?? '') === 'Yes') $leaveType = 'Hourly (' . ($leave['l_hourly_time'] ?? '') . ')';
                    $row[] = $leaveType;

                    // # of Days
                    $days = 1;
                    if (!empty($leave['l_from_date']) && !empty($leave['l_to_date'])) {
                        $from = strtotime($leave['l_from_date']);
                        $to = strtotime($leave['l_to_date']);
                        $days = max(1, round(($to - $from) / 86400) + 1);
                        if (($leave['l_is_halfday'] ?? '') === 'Yes') $days = 0.5;
                    }
                    $row[] = $days;

                    // Action
                    $row[] = '<a href="javascript://" onclick="Approve(' . $leave['l_id'] . ')" class="btn btn-success btn-xs">Manage</a>';
                    $data[] = $row;
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            case 'leavestoday':
                $leaderid = $request->getPost('leaderid');
                $today = date('Y-m-d');
                $builder = $db->table('aa_leaves L')
                    ->select('L.*, U.u_name, U.u_department')
                    ->join('aa_users U', 'L.l_u_id = U.u_id', 'left')
                    ->where('L.l_status', 'Approved')
                    ->where('L.l_from_date <=', $today)
                    ->where('L.l_to_date >=', $today)
                    ->orderBy('U.u_name', 'ASC')
                    ->limit(5);

                if (!empty($leaderid)) {
                    $builder->where('U.u_leader', $leaderid);
                }

                $leaves = $builder->get()->getResultArray();
                $data = [];
                foreach ($leaves as $leave) {
                    $row = [];
                    $row[] = $leave['u_name'] ?? '';
                    $row[] = $leave['u_department'] ?? '';
                    $row[] = ($leave['l_is_halfday'] ?? '') === 'Yes' ? 'Yes (' . ($leave['l_halfday_time'] ?? '') . ')' : 'No';
                    $row[] = ($leave['l_is_hourly'] ?? '') === 'Yes' ? 'Yes (' . ($leave['l_hourly_time'] ?? '') . ')' : 'No';
                    $row[] = $leave['l_approved_by'] ?? '';
                    $data[] = $row;
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            case 'present_list':
                // Show employees who logged in today (from aa_present table)
                $today = date('Y-m-d');
                $builder = $db->table('aa_users U')
                    ->select('U.u_name, U.u_email, U.u_mobile, U.u_type, U.u_department')
                    ->join('aa_present P', 'U.u_id = P.pr_u_id')
                    ->where('P.pr_date', $today)
                    ->orderBy('U.u_department', 'ASC')
                    ->orderBy('U.u_name', 'ASC');

                $employees = $builder->get()->getResultArray();
                $data = [];
                foreach ($employees as $emp) {
                    $data[] = [
                        $emp['u_name'],
                        $emp['u_email'] ?? '',
                        $emp['u_mobile'] ?? '',
                        $emp['u_type'],
                        $emp['u_department'] ?? ''
                    ];
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            case 'present_list_limit':
                // Show employees who logged in today (from aa_present table) - limited for dashboard
                $today = date('Y-m-d');
                $builder = $db->table('aa_users U')
                    ->select('U.u_name, U.u_email, U.u_mobile, U.u_type, U.u_department')
                    ->join('aa_present P', 'U.u_id = P.pr_u_id')
                    ->where('P.pr_date', $today)
                    ->orderBy('U.u_department', 'ASC')
                    ->orderBy('U.u_name', 'ASC')
                    ->limit(5);

                $employees = $builder->get()->getResultArray();
                $data = [];
                foreach ($employees as $emp) {
                    $data[] = [
                        $emp['u_name'],
                        $emp['u_email'] ?? '',
                        $emp['u_mobile'] ?? '',
                        $emp['u_type'],
                        $emp['u_department'] ?? ''
                    ];
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            case 'under_watch':
                $projects = $db->table('aa_projects')
                    ->where('p_show_dashboard', 'Yes')
                    ->orderBy('p_name', 'ASC')
                    ->limit(5)
                    ->get()->getResultArray();

                $data = [];
                foreach ($projects as $project) {
                    // Get total expenses
                    $expenses = $db->table('aa_project_expense')
                        ->selectSum('pe_val')
                        ->where('pe_p_id', $project['p_id'])
                        ->get()->getRowArray();
                    $total_expense = floatval($expenses['pe_val'] ?? 0);
                    $p_value = floatval($project['p_value'] ?? 0);
                    $profit = $p_value - $total_expense;

                    $isMasterAdmin = ($admin_session['u_type'] ?? '') === 'Master Admin';
                    $data[] = [
                        htmlspecialchars($project['p_name']),
                        !empty($project['p_created']) ? convert_db2display($project['p_created'], false) : '',
                        $isMasterAdmin ? number_format($p_value, 2) : '0.00',
                        $isMasterAdmin ? number_format($total_expense, 2) : '0.00',
                        $isMasterAdmin ? number_format($profit, 2) : '0.00',
                        $project['p_status']
                    ];
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            case 'under_watch_all':
                $projects = $db->table('aa_projects')
                    ->where('p_show_dashboard', 'Yes')
                    ->orderBy('p_name', 'ASC')
                    ->get()->getResultArray();

                $data = [];
                $isMasterAdmin = ($admin_session['u_type'] ?? '') === 'Master Admin';
                foreach ($projects as $project) {
                    $expenses = $db->table('aa_project_expense')
                        ->selectSum('pe_val')
                        ->where('pe_p_id', $project['p_id'])
                        ->get()->getRowArray();
                    $total_expense = floatval($expenses['pe_val'] ?? 0);
                    $p_value = floatval($project['p_value'] ?? 0);
                    $profit = $p_value - $total_expense;

                    $data[] = [
                        htmlspecialchars($project['p_name']),
                        !empty($project['p_created']) ? convert_db2display($project['p_created'], false) : '',
                        $isMasterAdmin ? number_format($p_value, 2) : '0.00',
                        $isMasterAdmin ? number_format($total_expense, 2) : '0.00',
                        $isMasterAdmin ? number_format($profit, 2) : '0.00',
                        $project['p_status']
                    ];
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            case 'leavestoday_all':
                $today = date('Y-m-d');
                $leaves = $db->table('aa_leaves L')
                    ->select('L.*, U.u_name, U.u_department')
                    ->join('aa_users U', 'L.l_u_id = U.u_id', 'left')
                    ->where('L.l_status', 'Approved')
                    ->where('L.l_from_date <=', $today)
                    ->where('L.l_to_date >=', $today)
                    ->orderBy('U.u_name', 'ASC')
                    ->get()->getResultArray();

                $data = [];
                foreach ($leaves as $leave) {
                    $data[] = [
                        $leave['u_name'] ?? '',
                        $leave['u_department'] ?? '',
                        !empty($leave['l_from_date']) ? convert_db2display($leave['l_from_date'], false) : '',
                        !empty($leave['l_to_date']) ? convert_db2display($leave['l_to_date'], false) : '',
                        ($leave['l_is_halfday'] ?? '') === 'Yes' ? 'Yes (' . ($leave['l_halfday_time'] ?? '') . ')' : 'No',
                        ($leave['l_is_hourly'] ?? '') === 'Yes' ? 'Yes (' . ($leave['l_hourly_time'] ?? '') . ')' : 'No',
                        $leave['l_approved_by'] ?? '',
                    ];
                }

                echo json_encode([
                    'draw' => $request->getPost('draw') ?? 1,
                    'recordsTotal' => count($data),
                    'recordsFiltered' => count($data),
                    'data' => $data
                ]);
                exit;

            default:
                echo json_encode(['status' => 'fail', 'message' => 'Unknown dashboard type.']);
                exit;
        }
    }

    public function fetchDesktopNotifications()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $session = service('session');
        $admin_session = $session->get('admin_session');
        $u_id = $admin_session['u_id'] ?? 0;

        if (!$u_id) {
            echo json_encode([]);
            exit;
        }

        try {
            $db = \Config\Database::connect();

            // Fetch unsent notifications for this user
            $notifications = $db->table('aa_desktop_notification_queue')
                ->where('u_id', $u_id)
                ->where('is_sent', 0)
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();

            if (!empty($notifications)) {
                // Delete fetched notifications
                $ids = array_column($notifications, 'id');
                $db->table('aa_desktop_notification_queue')
                    ->whereIn('id', $ids)
                    ->delete();
            }

            // Garbage collection: keep only last 10 sent notifications per user
            $sentNotifications = $db->table('aa_desktop_notification_queue')
                ->where('u_id', $u_id)
                ->where('is_sent', 1)
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();
            $latest10Ids = array_column(array_slice($sentNotifications, 0, 10), 'id');
            if (!empty($latest10Ids)) {
                $db->table('aa_desktop_notification_queue')
                    ->where('u_id', $u_id)
                    ->where('is_sent', 1)
                    ->whereNotIn('id', $latest10Ids)
                    ->delete();
            }

            echo json_encode($notifications);
        } catch (\Exception $e) {
            log_message('error', 'Exception in fetchDesktopNotifications: ' . $e->getMessage());
            echo json_encode([]);
        }
        exit;
    }
    public function getProjectUsers($projectId)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->response->setHeader('Content-Type', 'application/json');

        $db = \Config\Database::connect();

        // Get project leaders
        $project = $db->table('aa_projects')
            ->select('p_leader')
            ->where('p_id', $projectId)
            ->get()
            ->getRowArray();

        if (!$project || empty($project['p_leader'])) {
            return $this->response->setJSON([]);
        }

        // Convert comma-separated leaders to array
        $leaderIds = array_filter(array_unique(explode(',', $project['p_leader'])));

        if (empty($leaderIds)) {
            return $this->response->setJSON([]);
        }

        // Fetch only Active Leaders
        $leaders = $db->table('aa_users')
            ->select('u_id, u_name, u_type')
            ->whereIn('u_id', $leaderIds)
            ->where('u_status', 'Active')
            ->orderBy('u_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($leaders);
    }

    public function getProjectUsers_OLD($projectId)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $db = \Config\Database::connect();

        // Get the project to find its leaders and employees
        $project = $db->table('aa_projects')->where('p_id', $projectId)->get()->getRowArray();

        if (!$project) {
            echo json_encode([]);
            exit;
        }

        // Collect all user IDs from project
        $userIds = [];
        if (!empty($project['p_leader'])) {
            $userIds = array_merge($userIds, explode(',', $project['p_leader']));
        }
        // Get users assigned to tasks in this project
        $projectUsers = $db->table('aa_task2user')
            ->distinct()
            ->select('tu_u_id')
            ->where('tu_p_id', $projectId)
            ->where('tu_removed', 'No')
            ->get()->getResultArray();
        foreach ($projectUsers as $pu) {
            $userIds[] = $pu['tu_u_id'];
        }
        $userIds = array_unique(array_filter($userIds));

        if (empty($userIds)) {
            echo json_encode([]);
            exit;
        }

        $users = $db->table('aa_users')
            ->select('u_id, u_name, u_type')
            ->whereIn('u_id', $userIds)
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        echo json_encode($users);
        exit;
    }

    public function tasks()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();
        $act = $request->getPost('act') ?? 'list';

        switch ($act) {
            case 't_loghours':
                $t_id = $request->getPost('t_id');
                $draw = $request->getPost('draw') ?? 1;
                $callfrom = $request->getPost('callfrom');
                $emp_id = $request->getPost('emp_id');
                $t_name = $request->getPost('t_name');
                $p_name = $request->getPost('p_name');

                $builder = $db->table('aa_attendance A')
                    ->select('U.u_name, A.*')
                    ->join('aa_users U', 'U.u_id = A.at_u_id', 'left')
                    ->where('A.at_t_id', $t_id);
                if ($emp_id) $builder->where('A.at_u_id', $emp_id);
                $builder->orderBy('A.at_date', 'DESC');
                $records = $builder->get()->getResultArray();

                $result = [];
                $whours = 0;
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $rec['u_name'] ?? '';
                    if ($callfrom == 'report') {
                        $row[] = $p_name ?? 'Leave';
                        $row[] = $t_name ?? 'Leave';
                    }
                    $row[] = isset($rec['at_date']) ? convert_db2display($rec['at_date']) : '';
                    $row[] = RevTime($rec['at_start']) . ' - ' . RevTime($rec['at_end']);
                    $whours += (($rec['at_end'] - $rec['at_start']) / 60);
                    $row[] = $rec['at_comment'] ?? '';
                    $result[] = $row;
                }
                if ($whours > 0) {
                    $total_hrs = '<b>Total Hours Worked : ' . number_format($this->convertHours($whours), 2) . ' hr</b>';
                    if ($callfrom == 'report')
                        $new = ['', '', '', '', $total_hrs, ''];
                    else
                        $new = ['', '', $total_hrs, ''];
                    $result[] = $new;
                }

                echo json_encode([
                    'draw' => intval($draw),
                    'recordsTotal' => count($records),
                    'recordsFiltered' => count($records),
                    'data' => $result
                ]);
                exit;

            case 'tm_list':
                $t_id = $request->getPost('t_id');
                $draw = $request->getPost('draw') ?? 1;

                $records = $db->table('aa_task_message tm')
                    ->select('tm.*, u.u_name')
                    ->join('aa_users u', 'u.u_id = tm.tm_u_id')
                    ->where('tm.tm_t_id', $t_id)
                    ->get()->getResultArray();

                $result = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = isset($rec['tm_date']) ? convert_db2display($rec['tm_date']) : '';
                    $row[] = $rec['tm_text'] ?? '';
                    $row[] = !empty($rec['tm_file_name']) ? '<a href="' . base_url('home/download/tm/' . $rec['tm_id']) . '" target="_blank">' . $rec['tm_file_name'] . '</a>' : '';
                    $row[] = $rec['u_name'] ?? '';
                    $result[] = $row;
                }

                echo json_encode([
                    'draw' => intval($draw),
                    'recordsTotal' => count($result),
                    'recordsFiltered' => count($result),
                    'data' => $result
                ]);
                exit;

            case 'tm_add':
                $t_id = $request->getPost('t_id');
                $tm_text = $request->getPost('tm_text');

                $data = [
                    'tm_t_id' => $t_id,
                    'tm_text' => $tm_text,
                    'tm_date' => date('Y-m-d H:i:s'),
                    'tm_u_id' => $admin_session['u_id'],
                ];
                $db->table('aa_task_message')->insert($data);
                $tm_id = $db->insertID();

                // Handle file upload
                $tm_file = $request->getFile('tm_file');
                if ($tm_file && $tm_file->isValid()) {
                    $ext = strtolower($tm_file->getClientExtension());
                    $db->table('aa_task_message')->where('tm_id', $tm_id)->update([
                        'tm_file_name' => $tm_file->getClientName(),
                        'tm_file_type' => $ext,
                    ]);
                    $directory = ceil($tm_id / 500);
                    $task_message_files = FCPATH . 'assets/task_message_files/' . $directory . '/';
                    if (!is_dir($task_message_files)) {
                        mkdir($task_message_files, 0777, true);
                    }
                    $tm_file->move($task_message_files, (string)$tm_id);
                }

                echo json_encode(['status' => 'pass', 'message' => 'Message is saved.']);
                exit;

            case 'add':
                if ($admin_session['u_type'] == 'Employee') {
                    echo json_encode(['status' => 'session', 'message' => 'You do not have access for this section.']);
                    exit;
                }
                $t_id = $request->getPost('t_id') ?? 0;
                $t_p_id = $request->getPost('t_p_id');
                $t_title = $request->getPost('t_title');
                $t_priority = $request->getPost('t_priority');
                $t_status = $request->getPost('t_status');
                $t_hours = $request->getPost('t_hours');
                $t_hours_planned = $request->getPost('t_hours_planned') ?? 0;
                $t_description = $request->getPost('t_description');
                $t_dependancy = $request->getPost('t_dependancy');
                $t_parent = $request->getPost('t_parent');
                $t_u_id = $admin_session['u_id'];
                $t_assign = $request->getPost('u_id');
                $tf_lbl = $request->getPost('tf_lbl');
                if ($t_parent == '') $t_parent = 0;

                $taskData = [
                    't_p_id' => $t_p_id,
                    't_title' => $t_title,
                    't_priority' => $t_priority,
                    't_status' => $t_status,
                    't_hours' => $t_hours,
                    't_hours_planned' => $t_hours_planned,
                    't_description' => $t_description,
                    't_dependancy' => $t_dependancy,
                    't_parent' => $t_parent,
                    't_u_id' => $t_u_id,
                    't_createdate' => date('Y-m-d H:i:s'),
                ];

                $is_edit = false;
                if ($t_id > 0) {
                    $is_edit = true;
                    unset($taskData['t_u_id'], $taskData['t_parent'], $taskData['t_createdate']);
                    $db->table('aa_tasks')->where('t_id', $t_id)->update($taskData);
                } else {
                    $db->table('aa_tasks')->insert($taskData);
                    $t_id = $db->insertID();
                    // Assign users
                    if (!empty($t_assign)) {
                        $assignees = is_array($t_assign) ? $t_assign : [$t_assign];
                        foreach ($assignees as $uid) {
                            $db->table('aa_task2user')->insert([
                                'tu_t_id' => $t_id,
                                'tu_u_id' => $uid,
                                'tu_p_id' => $t_p_id,
                                'tu_removed' => 'No',
                            ]);
                            // Notify each assigned user (skip if it's the creator)
                            if ($uid != $t_u_id) {
                                $db->table('aa_desktop_notification_queue')->insert([
                                    'u_id'    => $uid,
                                    'title'   => 'New Task Assigned',
                                    'message' => 'Task "' . $t_title . '" has been assigned to you by ' . $admin_session['u_name'],
                                    'payload' => json_encode(['screen_name' => 'Task', 'id' => $t_id]),
                                    'is_sent' => 0,
                                ]);
                            }
                        }
                    }
                }

                // Handle file uploads
                $files = $request->getFiles();
                if (isset($files['tf_file'])) {
                    $taskFiles = is_array($files['tf_file']) ? $files['tf_file'] : [$files['tf_file']];
                    foreach ($taskFiles as $idx => $file) {
                        if ($file->isValid()) {
                            $label = is_array($tf_lbl) ? ($tf_lbl[$idx] ?? '') : $tf_lbl;
                            $db->table('aa_task_files')->insert([
                                'tf_t_id' => $t_id,
                                'tf_title' => $label,
                                'tf_file_name' => $file->getClientName(),
                            ]);
                            $tf_id = $db->insertID();
                            $directory = ceil($tf_id / 500);
                            $task_files_dir = FCPATH . 'assets/task_files/' . $directory . '/';
                            if (!is_dir($task_files_dir)) {
                                mkdir($task_files_dir, 0777, true);
                            }
                            $file->move($task_files_dir, (string)$tf_id);
                        }
                    }
                }

                echo json_encode(['status' => 'pass', 'message' => 'Task is saved.']);
                exit;

            case 'assigns':
                if ($admin_session['u_type'] == 'Employee') {
                    echo json_encode(['status' => 'session', 'message' => 'You do not have access for this section.']);
                    exit;
                }
                $t_id = $request->getPost('t_id');
                $t_p_id = $request->getPost('t_p_id');
                $t_assign = $request->getPost('u_id');
                $act_sub = $request->getPost('act_sub');

                if ($act_sub == 'add') {
                    // Check if already assigned
                    $existing = $db->table('aa_task2user')
                        ->where('tu_t_id', $t_id)
                        ->where('tu_u_id', $t_assign)
                        ->get()->getRowArray();
                    if ($existing) {
                        $db->table('aa_task2user')
                            ->where('tu_t_id', $t_id)
                            ->where('tu_u_id', $t_assign)
                            ->update(['tu_removed' => 'No']);
                    } else {
                        $db->table('aa_task2user')->insert([
                            'tu_t_id' => $t_id,
                            'tu_u_id' => $t_assign,
                            'tu_p_id' => $t_p_id,
                            'tu_removed' => 'No',
                        ]);
                    }
                    // Notify the assigned user (skip if assigning to self)
                    if ($t_assign != $admin_session['u_id']) {
                        $taskRow = $db->table('aa_tasks')->select('t_title')->where('t_id', $t_id)->get()->getRowArray();
                        $db->table('aa_desktop_notification_queue')->insert([
                            'u_id'    => $t_assign,
                            'title'   => 'Task Assigned',
                            'message' => 'Task "' . ($taskRow['t_title'] ?? '') . '" has been assigned to you by ' . $admin_session['u_name'],
                            'payload' => json_encode(['screen_name' => 'Task', 'id' => $t_id]),
                            'is_sent' => 0,
                        ]);
                    }
                } elseif ($act_sub == 'del') {
                    $db->table('aa_task2user')
                        ->where('tu_t_id', $t_id)
                        ->where('tu_u_id', $t_assign)
                        ->update(['tu_removed' => 'Yes']);
                }
                echo json_encode(['status' => 'pass', 'message' => 'Task is updated.']);
                exit;

            case 'file_del':
                if ($admin_session['u_type'] == 'Employee') {
                    echo json_encode(['status' => 'session', 'message' => 'You do not have access for this section.']);
                    exit;
                }
                $tf_id = $request->getPost('tf_id');
                if ($tf_id > 0) {
                    $directory = ceil($tf_id / 500);
                    $filepath = FCPATH . 'assets/task_files/' . $directory . '/' . $tf_id;
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    $db->table('aa_task_files')->where('tf_id', $tf_id)->delete();
                }
                echo json_encode(['status' => 'pass', 'message' => 'Task File is deleted.']);
                exit;

            case 'del':
                $t_id = $request->getPost('t_id');
                if ($admin_session['u_type'] == 'Employee') {
                    echo json_encode(['status' => 'session', 'message' => 'You do not have access for this section.']);
                    exit;
                }
                if ($t_id > 0) {
                    // Check for attendance
                    $attCount = $db->table('aa_attendance')->where('at_t_id', $t_id)->countAllResults();
                    if ($attCount > 0) {
                        echo json_encode(['status' => 'fail', 'message' => 'Cannot delete Task, Attendance is added for this task.']);
                        exit;
                    }
                    // Check for assigned users
                    $assignCount = $db->table('aa_task2user')->where('tu_t_id', $t_id)->where('tu_removed', 'No')->countAllResults();
                    if ($assignCount > 0) {
                        echo json_encode(['status' => 'fail', 'message' => 'Cannot delete Task, Task was assigned to user.']);
                        exit;
                    }
                    // Check for sub tasks
                    $subCount = $db->table('aa_tasks')->where('t_parent', $t_id)->countAllResults();
                    if ($subCount > 0) {
                        echo json_encode(['status' => 'fail', 'message' => 'Cannot delete Task, Task has sub tasks.']);
                        exit;
                    }
                    $db->table('aa_tasks')->where('t_id', $t_id)->delete();
                    $db->table('aa_task2user')->where('tu_t_id', $t_id)->delete();
                    echo json_encode(['status' => 'pass', 'message' => 'Task has been deleted successfully.']);
                }
                exit;

            case 'list':
            default:
                $t_id = $request->getPost('t_id');
                $t_p_id = $request->getPost('t_p_id');
                $t_parent = $request->getPost('t_parent');

                // Single record fetch
                if ($t_id > 0) {
                    $record = $db->table('aa_tasks T')
                        ->select('T.*, P.p_name, U.u_name')
                        ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
                        ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
                        ->where('T.t_id', $t_id)
                        ->get()->getRowArray();
                    if ($record) {
                        $assigns = $db->table('aa_task2user TU')
                            ->select('TU.*, U.u_name, U.u_id')
                            ->join('aa_users U', 'TU.tu_u_id = U.u_id')
                            ->where('TU.tu_t_id', $t_id)
                            ->where('TU.tu_removed', 'No')
                            ->get()->getResultArray();
                        $files = $db->table('aa_task_files')->where('tf_t_id', $t_id)->get()->getResultArray();
                        echo json_encode(['status' => 'pass', 'data' => $record, 'assigns' => $assigns, 'files' => $files]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Selected record is not available.']);
                    }
                    exit;
                }

                // Get filters
                $txt_projects = $request->getPost('txt_projects');
                $txt_status = $request->getPost('txt_status');
                $txt_employee = $request->getPost('txt_employee');
                $draw = $request->getPost('draw') ?? 1;

                // Determine if listing subtasks or main tasks
                $isSubtask = ($t_parent > 0);

                // Build query
                $builder = $db->table('aa_tasks T');
                $builder->select('T.*, P.p_name, P.p_number, U.u_name, GROUP_CONCAT(DISTINCT U2.u_name SEPARATOR ", ") as assigned_to_names');
                $builder->join('aa_projects P', 'P.p_id = T.t_p_id', 'left');
                $builder->join('aa_users U', 'U.u_id = T.t_u_id', 'left');
                $builder->join('aa_task2user TU', 'TU.tu_t_id = T.t_id AND TU.tu_removed = "No"', 'left');
                $builder->join('aa_users U2', 'TU.tu_u_id = U2.u_id', 'left');

                if ($isSubtask) {
                    $builder->where('T.t_parent', $t_parent);
                } else {
                    $builder->where('T.t_parent', 0);
                }
                $builder->where('P.p_status', 'Active');

                // Project Leader / Employee: only show tasks assigned to them
                if (in_array($admin_session['u_type'], ['Project Leader', 'Employee'])) {
                    $builder->where('TU.tu_u_id', $admin_session['u_id']);
                }

                if (!empty($txt_projects)) {
                    $builder->like('P.p_name', $txt_projects);
                }
                if (!empty($txt_status)) {
                    $builder->where('T.t_status', $txt_status);
                }
                if (!empty($txt_employee)) {
                    $builder->where('TU.tu_u_id', $txt_employee);
                }
                if ($t_p_id > 0) {
                    $builder->where('T.t_p_id', $t_p_id);
                }

                $builder->groupBy('T.t_id');
                $builder->orderBy('T.t_priority', 'ASC');

                $tasks = $builder->get()->getResultArray();
                $totalRecords = count($tasks);

                // Format data for DataTables
                $data = [];
                $sr = 1;
                foreach ($tasks as $task) {
                    $row = [];
                    $row[] = $sr++;
                    // Skip project name column when viewing a specific project (project_detail page)
                    if (!($t_p_id > 0)) {
                        $row[] = $task['p_name'] ?? '';
                    }
                    $row[] = $task['t_title'] ?? '';
                    $row[] = $task['t_priority'] ?? '';
                    $row[] = isset($task['t_createdate']) ? convert_db2display($task['t_createdate'], false) : '';
                    $row[] = $task['u_name'] ?? '';
                    $row[] = $task['assigned_to_names'] ?? '';

                    // Sub-tasks show estimated hours column
                    if ($isSubtask) {
                        $row[] = $task['t_hours'] ?? '';
                    }

                    $row[] = $task['t_status'] ?? '';

                    // Action buttons
                    $anchors = '';
                    $anchors .= '<a href="' . base_url("home/task/view/{$task['t_p_id']}/{$task['t_id']}") . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i></a>&nbsp; ';
                    if (in_array($admin_session['u_type'], ['Bim Head', 'Master Admin']) || $admin_session['u_id'] == ($task['t_u_id'] ?? '')) {
                        $anchors .= '<a href="' . base_url("home/task/edit/{$task['t_p_id']}/{$task['t_id']}") . '" class="btn btn-success btn-md"><i class="fa fa-edit"></i></a>&nbsp; ';
                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $task['t_id'] . '\', \'' . $task['t_p_id'] . '\')"><i class="fa fa-trash"></i></a>&nbsp; ';
                    }
                    // Only main tasks show sub-tasks button
                    if (!$isSubtask && $task['t_parent'] == 0) {
                        $anchors .= '<a href="' . base_url("home/task/sub/{$task['t_p_id']}/{$task['t_id']}") . '" class="btn btn-warning btn-md"><i class="fa fa-tasks"></i></a>&nbsp; ';
                    }

                    $row[] = $anchors;
                    $data[] = $row;
                }

                $response = [
                    'draw' => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data
                ];

                echo json_encode($response);
                exit;
        }
    }

    public function projects()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');

        $act = $request->getPost('act');
        $p_id = $request->getPost('p_id');
        $db = \Config\Database::connect();

        // Handle Add/Edit
        if ($act === 'add') {
            $p_id = $p_id ?? 0;
            $projectData = [
                'p_number' => $request->getPost('p_number'),
                'p_name' => $request->getPost('p_name'),
                'p_value' => $request->getPost('p_value') ?? 0,
                'p_contact' => $request->getPost('p_contact') ?? '',
                'p_cat' => $request->getPost('p_cat') ?? '',
                'p_status' => $request->getPost('p_status') ?? 'Active',
                'p_address' => $request->getPost('p_address') ?? '',
                'p_scope' => $request->getPost('p_scope') ?? '',
                'p_show_dashboard' => $request->getPost('p_show_dashboard') ?? 'No',
                'p_leader' => is_array($request->getPost('p_leader')) ? implode(',', $request->getPost('p_leader')) : ($request->getPost('p_leader') ?? ''),
            ];

            if ($p_id > 0) {
                $projectData['p_updated'] = date('Y-m-d H:i:s');
                $db->table('aa_projects')->where('p_id', $p_id)->update($projectData);
            } else {
                $projectData['p_created'] = date('Y-m-d H:i:s');
                $projectData['p_updated'] = date('Y-m-d H:i:s');
                $db->table('aa_projects')->insert($projectData);
                $p_id = $db->insertID();
            }

            // Handle project expenses
            if ($admin_session['u_type'] == 'Master Admin') {
                $db->table('aa_project_expense')->where('pe_p_id', $p_id)->delete();
                $pe_lbls = $request->getPost('pe_lbl') ?? [];
                $pe_vals = $request->getPost('pe_val') ?? [];
                if (is_array($pe_lbls)) {
                    foreach ($pe_lbls as $i => $lbl) {
                        if (!empty(trim($lbl ?? ''))) {
                            $db->table('aa_project_expense')->insert([
                                'pe_p_id' => $p_id,
                                'pe_lbl' => $lbl,
                                'pe_val' => $pe_vals[$i] ?? 0,
                            ]);
                        }
                    }
                }
            }

            echo json_encode(['status' => 'pass', 'message' => 'Project saved successfully.']);
            exit;
        }

        // Handle Delete
        if ($act === 'del') {
            if ($p_id) {
                $db->table('aa_projects')->where('p_id', $p_id)->delete();
                $db->table('aa_project_expense')->where('pe_p_id', $p_id)->delete();
                echo json_encode(['status' => 'pass', 'message' => 'Project deleted successfully.']);
            } else {
                echo json_encode(['status' => 'fail', 'message' => 'Invalid project.']);
            }
            exit;
        }

        // Handle Teams tab (project_detail)
        if ($act === 'teams') {
            $draw = $request->getPost('draw') ?? 1;
            try {
                $sql = "SELECT total_salary as final_salary, u_salary, u_email, work_hour_total, u_id, u_name FROM (SELECT (SUM((at_end - at_start) / 60 * US.u_salary)) as total_salary, SUM((at_end - at_start) / 60) as work_hour_total, U.u_id as UserId, U.u_name as u_name, U.u_salary, U.u_email, U.u_id FROM aa_attendance A INNER JOIN aa_users_salary US ON A.at_u_id = US.u_id, aa_users as U WHERE at_p_id = ? and at_date >= US.u_start_date and at_date < US.u_end_date and U.u_id = US.u_id group by U.u_id) as FinnalDB";
                $records = $db->query($sql, [$p_id])->getResultArray();
            } catch (\Exception $e) {
                // Fallback if aa_users_salary table doesn't exist
                $records = [];
            }
            $totalData = count($records);
            $result = [];
            $total = 0;
            foreach ($records as $rec) {
                $rec['work_hour_total'] = $rec['work_hour_total'] ?? 0;
                $nestedData = [];
                $nestedData[] = '<label class="check_container"><input type="checkbox" id="u_ids_' . $rec['u_id'] . '" name="u_id[]" value="' . $rec['u_email'] . '" class="teammet"><span class="checkmark"></span></label>';
                $nestedData[] = $rec['u_name'];
                $nestedData[] = $rec['u_email'];

                $whole = floor($rec['work_hour_total']);
                $fraction = $rec['work_hour_total'] - $whole;
                if ($fraction == '.75') $work_hour_total = str_replace($fraction, '.75', '.45');
                else if ($fraction == '.25') $work_hour_total = str_replace($fraction, '.25', '.15');
                else if ($fraction == '.50') $work_hour_total = str_replace($fraction, '.50', '.30');
                else $work_hour_total = $fraction;
                $nestedData[] = number_format($whole + $work_hour_total, 2);

                if ($admin_session['u_type'] == 'Master Admin') {
                    $nestedData[] = $rec['u_salary'];
                    $nestedData[] = $rec['final_salary'] ?? 0;
                } else {
                    $nestedData[] = 0;
                    $nestedData[] = 0;
                }
                $result[] = $nestedData;
                if ($admin_session['u_type'] == 'Master Admin')
                    $total = $total + ($rec['final_salary'] ?? 0);
            }
            echo json_encode([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalData + 1),
                'recordsFiltered' => intval($totalData + 1),
                'data' => $result,
                'total_val' => number_format($total, 2)
            ]);
            exit;
        }

        // Handle Accounts tab (project_detail)
        if ($act === 'accounts') {
            $draw = $request->getPost('draw') ?? 1;
            $isMasterAdmin = ($admin_session['u_type'] ?? '') === 'Master Admin';
            $expenses = $db->table('aa_project_expense')
                ->where('pe_p_id', $p_id)
                ->get()->getResultArray();
            $totalData = count($expenses);
            $result = [];
            $total = 0;
            foreach ($expenses as $exp) {
                $expVal = $isMasterAdmin ? floatval($exp['pe_val']) : 0;
                $result[] = [$exp['pe_lbl'], number_format($expVal, 2)];
                $total += $expVal;
            }
            // Add salary row
            try {
                $sql = "SELECT SUM(total_salary) as final_salary FROM (SELECT ((at_end - at_start) / 60 * u_salary) as total_salary FROM aa_attendance A INNER JOIN aa_users_salary U ON A.at_u_id = U.u_id WHERE at_p_id = ? and at_date >= u_start_date and at_date < u_end_date) as FinnalDB";
                $salaryResult = $db->query($sql, [$p_id])->getRowArray();
                $totalSalary = $isMasterAdmin ? floatval($salaryResult['final_salary'] ?? 0) : 0;
            } catch (\Exception $e) {
                $totalSalary = 0;
            }
            $result[] = ['Salary', number_format($totalSalary, 2)];
            echo json_encode([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalData + 1),
                'recordsFiltered' => intval($totalData + 1),
                'data' => $result,
                'total_val' => number_format($total + $totalSalary, 2)
            ]);
            exit;
        }

        // Handle Verbal Communication add
        if ($act === 'vcom_add') {
            $pv_p_id = $request->getPost('pv_p_id');
            $pv_text = $request->getPost('pv_text');
            try {
                $db->table('aa_project_vcom')->insert([
                    'pv_p_id' => $pv_p_id,
                    'pv_u_id' => $admin_session['u_id'],
                    'pv_text' => $pv_text,
                    'pv_datetime' => date('Y-m-d H:i:s'),
                ]);
                echo json_encode(['status' => 'pass', 'message' => 'Record is saved.']);
            } catch (\Exception $e) {
                echo json_encode(['status' => 'fail', 'type' => 'popup', 'message' => $e->getMessage()]);
            }
            exit;
        }

        // Handle Verbal Communication list
        if ($act === 'vcom_list') {
            $pv_p_id = $request->getPost('pv_p_id');
            $draw = $request->getPost('draw') ?? 1;
            $records = $db->table('aa_project_vcom V')
                ->select('V.*, U.u_name')
                ->join('aa_users U', 'V.pv_u_id = U.u_id')
                ->where('V.pv_p_id', $pv_p_id)
                ->orderBy('V.pv_datetime', 'DESC')
                ->get()->getResultArray();
            $totalData = count($records);
            $result = [];
            foreach ($records as $rec) {
                $result[] = [
                    convert_db2display($rec['pv_datetime']),
                    $rec['pv_text'],
                    $rec['u_name']
                ];
            }
            echo json_encode([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalData + 1),
                'recordsFiltered' => intval($totalData + 1),
                'data' => $result,
            ]);
            exit;
        }

        // Handle Email send
        if ($act === 'email') {
            $email_list = $request->getPost('email_list');
            $email_subject = $request->getPost('email_subject');
            $email_message = $request->getPost('email_message');
            try {
                $email = \Config\Services::email();
                $email->setFrom('noreply@dummyproject.com', 'CRM');
                $email->setTo($email_list);
                $email->setSubject($email_subject);
                $email->setMessage($email_message);
                $email->send();
                echo json_encode(['status' => 'pass', 'message' => 'Email sent successfully.']);
            } catch (\Exception $e) {
                echo json_encode(['status' => 'fail', 'message' => 'Email could not be sent: ' . $e->getMessage()]);
            }
            exit;
        }

        // Handle single record fetch for edit
        if ($act === 'list' && $p_id) {
            $projectModel = new \App\Models\ProjectModel();
            $project = $projectModel->find($p_id);

            if ($project) {
                // Get project expenses if Master Admin
                $pe = [];
                if ($admin_session['u_type'] == 'Master Admin') {
                    $pe = $db->table('aa_project_expense')
                        ->where('pe_p_id', $p_id)
                        ->get()
                        ->getResultArray();
                }

                echo json_encode([
                    'status' => 'pass',
                    'data' => $project,
                    'pe' => $pe
                ]);
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Project not found'
                ]);
            }
            exit;
        }

        // Handle DataTables list request
        // Get filters
        $txt_search = $request->getPost('txt_search');
        $txt_p_cat = $request->getPost('txt_p_cat');
        $txt_p_status = $request->getPost('txt_p_status') ?: 'Active';
        $txt_p_leader = $request->getPost('txt_p_leader');

        // Build query
        $builder = $db->table('aa_projects P');
        $builder->select('P.*');

        // Apply filters
        if (!empty($txt_search)) {
            $builder->groupStart()
                ->like('P.p_number', $txt_search)
                ->orLike('P.p_name', $txt_search)
            ->groupEnd();
        }

        if (!empty($txt_p_cat)) {
            $builder->where('P.p_cat', $txt_p_cat);
        }

        if (!empty($txt_p_status)) {
            $builder->where('P.p_status', $txt_p_status);
        }

        if (!empty($txt_p_leader)) {
            // Use FIND_IN_SET for comma-separated leader IDs
            $builder->where("FIND_IN_SET('{$txt_p_leader}', P.p_leader) >", 0, false);
        }

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Get filtered data
        $projects = $builder->get()->getResultArray();

        // Format data for DataTables
        $data = [];
        foreach ($projects as $project) {
            $row = [];
            $row[] = $project['p_number'];
            $row[] = $project['p_name'];
            $row[] = $project['p_address'] ?? '';

            // Add cost/expense/profit columns for Master Admin
            if ($admin_session['u_type'] == 'Master Admin') {
                // Get project value and expenses
                $p_value = floatval($project['p_value'] ?? 0);

                // Get total expenses
                $expenses = $db->table('aa_project_expense')
                    ->selectSum('pe_val')
                    ->where('pe_p_id', $project['p_id'])
                    ->get()
                    ->getRowArray();
                $total_expense = floatval($expenses['pe_val'] ?? 0);

                $profit = $p_value - $total_expense;

                $row[] = number_format($p_value, 2);
                $row[] = number_format($total_expense, 2);
                $row[] = number_format($profit, 2);
            }

            $row[] = $project['p_status'];

            // Get leader names
            $leader_names = [];
            if (!empty($project['p_leader'])) {
                $leader_ids = explode(',', $project['p_leader']);
                $leaders = $db->table('aa_users')
                    ->select('u_name')
                    ->whereIn('u_id', $leader_ids)
                    ->get()
                    ->getResultArray();
                foreach ($leaders as $leader) {
                    $leader_names[] = $leader['u_name'];
                }
            }
            $row[] = implode(', ', $leader_names);

            // Action buttons
            $actions = '<div class="actions">';
            $actions .= '<a href="' . base_url('home/project_detail/' . $project['p_id']) . '" class="btn btn-success btn-xs" title="View"><i class="fa fa-eye"></i></a> ';
            $actions .= '<a href="javascript://" onclick="showAddEditForm(' . $project['p_id'] . ', \'' . $admin_session['u_type'] . '\')" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a> ';
            $actions .= '<a href="' . base_url('home/project_contacts/' . $project['p_id']) . '" class="btn btn-warning btn-xs" title="Project Contacts"><i class="fa fa-phone"></i></a> ';

            if ($admin_session['u_type'] == 'Super Admin' || $admin_session['u_type'] == 'Master Admin') {
                $actions .= '<a href="javascript://" onclick="deleteRecord(' . $project['p_id'] . ')" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';
            }
            $actions .= '</div>';

            $row[] = $actions;

            $data[] = $row;
        }

        $response = [
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];

        echo json_encode($response);
        exit;
    }

    public function employees()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();

        $act = $request->getPost('act');
        $u_id = $request->getPost('u_id');

        // Handle Add/Edit
        if ($act === 'add') {
            $u_id = $u_id ?? 0;
            $userData = [
                'u_username' => $request->getPost('u_username'),
                'u_name' => $request->getPost('u_name'),
                'u_type' => $request->getPost('u_type') ?? 'Employee',
                'u_mobile' => $request->getPost('u_mobile') ?? '',
                'u_email' => $request->getPost('u_email') ?? '',
                'u_address' => $request->getPost('u_address') ?? '',
                'u_qualification' => $request->getPost('u_qualification') ?? '',
                'u_department' => $request->getPost('u_department') ?? 'Architecture',
                'u_status' => $request->getPost('u_status') ?? 'Active',
                'u_leader' => $request->getPost('u_leader') ?? 0,
                'u_salary' => $request->getPost('u_salary') ?? 0,
                'u_app_auth' => $request->getPost('u_app_auth') ?? '0',
                'u_join_date' => !empty($request->getPost('u_join_date')) ? convert_display2db($request->getPost('u_join_date')) : date('Y-m-d'),
                'u_leave_date' => !empty($request->getPost('u_leave_date')) ? convert_display2db($request->getPost('u_leave_date')) : '0000-00-00',
                'u_comments' => $request->getPost('u_comments') ?? '',
            ];

            $password = $request->getPost('u_password');
            if (!empty($password)) {
                $userData['u_password'] = md5($password);
            }

            if ($u_id > 0) {
                $userData['updated_at'] = date('Y-m-d H:i:s');
                $db->table('aa_users')->where('u_id', $u_id)->update($userData);
            } else {
                if (empty($password)) {
                    echo json_encode(['status' => 'fail', 'message' => 'Password is required for new employee.', 'type' => 'popup']);
                    exit;
                }
                // Check if username exists
                $existing = $db->table('aa_users')->where('u_username', $userData['u_username'])->get()->getRowArray();
                if ($existing) {
                    echo json_encode(['status' => 'fail', 'message' => 'Username already exists.', 'type' => 'popup']);
                    exit;
                }
                $userData['created_at'] = date('Y-m-d H:i:s');
                $userData['updated_at'] = date('Y-m-d H:i:s');
                $db->table('aa_users')->insert($userData);
                $u_id = $db->insertID();
            }

            // Handle photo upload (save to root assets/logos/ to match URL)
            $photoFile = $request->getFile('logo_file');
            if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
                $photoDir = ROOTPATH . 'assets/logos/';
                if (!is_dir($photoDir)) mkdir($photoDir, 0777, true);
                $photoFile->move($photoDir, 'ulogo_' . $u_id . '.jpg');
            }

            echo json_encode(['status' => 'pass', 'message' => 'Employee saved successfully.']);
            exit;
        }

        // Handle Delete
        if ($act === 'del') {
            if ($u_id) {
                $db->table('aa_users')->where('u_id', $u_id)->delete();
                echo json_encode(['status' => 'pass', 'message' => 'Employee deleted successfully.']);
            } else {
                echo json_encode(['status' => 'fail', 'message' => 'Invalid employee.']);
            }
            exit;
        }

        // Handle task-page employee search (list_task)
        if ($act === 'list_task') {
            $txt_search = $request->getPost('txt_search') ?? '';
            $today = date('Y-m-d');
            $qb = $db->table('aa_users')
                ->select('u_id, u_username, u_name')
                ->whereIn('u_type', ['Project Leader', 'Employee', 'Bim Head', 'TaskCoordinator'])
                ->where('u_status', 'Active')
                ->orderBy('u_name', 'ASC');
            if ($txt_search) $qb->like('u_name', $txt_search);
            $users = $qb->get()->getResultArray();

            $data = [];
            $active_projects = [];
            $active_tasks = [];
            $leaves = [];
            foreach ($users as $u) {
                $uid = $u['u_id'];
                $data[] = [$uid, $u['u_username'], $u['u_name']];
                $active_projects[$uid] = $db->table('aa_task2user')->distinct()->select('tu_p_id')->where('tu_u_id', $uid)->where('tu_removed', 'No')->countAllResults();
                $active_tasks[$uid] = $db->table('aa_tasks T')->join('aa_task2user TU', 'T.t_id = TU.tu_t_id')->where('TU.tu_u_id', $uid)->where('TU.tu_removed', 'No')->whereNotIn('T.t_status', ['Completed', 'Cancelled'])->countAllResults();
                $leaveRec = $db->table('aa_leaves')->select('l_from_date, l_to_date')->where('l_u_id', $uid)->where('l_status', 'Approved')->where('l_from_date <=', $today)->where('l_to_date >=', $today)->get()->getRowArray();
                $leaves[$uid] = $leaveRec ? date('d-m-Y', strtotime($leaveRec['l_from_date'])) . ' - ' . date('d-m-Y', strtotime($leaveRec['l_to_date'])) : '';
            }
            echo json_encode(['status' => 'pass', 'data' => $data, 'active_projects' => $active_projects, 'active_tasks' => $active_tasks, 'leaves' => $leaves]);
            exit;
        }

        // Handle single record fetch for edit
        if ($act === 'list' && $u_id) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($u_id);

            if ($user) {
                $photoPath = ROOTPATH . 'assets/logos/ulogo_' . $u_id . '.jpg';
                $user['u_photo'] = file_exists($photoPath) ? base_url('assets/logos/ulogo_' . $u_id . '.jpg') : '';
                echo json_encode([
                    'status' => 'pass',
                    'data' => $user
                ]);
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Employee not found'
                ]);
            }
            exit;
        }

        // Handle DataTables list request
        $txt_search = $request->getPost('txt_search');
        $txt_U_Type = $request->getPost('txt_U_Type');
        $txt_U_Status = $request->getPost('txt_U_Status');

        // Build query
        $builder = $db->table('aa_users');
        $builder->select('*');
        $builder->where('u_type !=', 'Associate User');
        $builder->where('u_type !=', 'Master Admin');

        // Apply filters
        if (!empty($txt_search)) {
            $builder->like('u_name', $txt_search);
        }

        if (!empty($txt_U_Type)) {
            $builder->where('u_type', $txt_U_Type);
        }

        if (!empty($txt_U_Status)) {
            $builder->where('u_status', $txt_U_Status);
        }
        else {
                $builder->where('u_status', 'Active');
        }

        $builder->orderBy('u_id', 'DESC');

        // Get total count
        $totalRecords = $builder->countAllResults(false);

        // Get filtered data
        $employees = $builder->get()->getResultArray();

        $isMasterAdmin = (($admin_session['u_type'] ?? '') === 'Master Admin');

        // Format data for DataTables
        $data = [];
        foreach ($employees as $employee) {
            $row = [];
            $row[] = $employee['u_username'];
            $row[] = $employee['u_name'];
            $row[] = $employee['u_email'];
            $row[] = $employee['u_mobile'];
            $row[] = $isMasterAdmin ? ($employee['u_salary'] ?? '0') : '0';
            $row[] = $employee['u_type'];

            // Action buttons
            $actions = '<div class="actions">';
            $actions .= '<a href="javascript://" onclick="showAddEditForm(' . $employee['u_id'] . ', \'' . $admin_session['u_type'] . '\')" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a> ';

            if ($admin_session['u_type'] == 'Master Admin' || $admin_session['u_type'] == 'Bim Head' || $admin_session['u_type'] == 'Super Admin') {
                $actions .= '<a href="javascript://" onclick="deleteRecord(' . $employee['u_id'] . ')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>';
            }
            $actions .= '</div>';

            $row[] = $actions;

            $data[] = $row;
        }

        $response = [
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];

        echo json_encode($response);
        exit;
    }

    public function drop_get()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $db = \Config\Database::connect();

        $dropobjs = $request->getPost('dropobjs');
        $response = [
            'status' => 'pass',
            'data' => []
        ];

        if (is_array($dropobjs)) {
            foreach ($dropobjs as $obj) {
                $type = $obj['type'] ?? '';
                $id = $obj['id'] ?? '';

                switch ($type) {
                    case 'team_leader':
                        // Get all project leaders
                        $builder = $db->table('aa_users');
                        $builder->select('u_id, u_name');
                        $builder->where('u_type', 'Project Leader');
                        $builder->where('u_status', 'Active');
                        $builder->orderBy('u_name', 'ASC');
                        $leaders = $builder->get()->getResultArray();

                        $html = '';
                        foreach ($leaders as $leader) {
                            $selected = '';
                            if (!empty($id)) {
                                $ids = explode(',', $id);
                                if (in_array($leader['u_id'], $ids)) {
                                    $selected = ' selected';
                                }
                            }
                            $html .= '<option value="' . $leader['u_id'] . '"' . $selected . '>' . htmlspecialchars($leader['u_name']) . '</option>';
                        }
                        $response['data']['team_leader'] = $html;
                        break;

                    case 'employees':
                        // Get all active employees
                        $builder = $db->table('aa_users');
                        $builder->select('u_id, u_name');
                        $builder->where('u_status', 'Active');
                        $builder->where('u_type !=', 'Associate User');
                        $builder->where('u_type !=', 'Master Admin');
                        $builder->orderBy('u_id', 'ASC');
                        $employees = $builder->get()->getResultArray();

                        $html = '<option value="">Select Employee</option>';
                        foreach ($employees as $employee) {
                            $html .= '<option value="' . $employee['u_id'] . '">' . htmlspecialchars($employee['u_name']) . '</option>';
                        }
                        $response['data']['employees'] = $html;
                        break;

                    case 'projects':
                        // Get all active projects
                        $builder = $db->table('aa_projects');
                        $builder->select('p_id, p_name, p_number');
                        $builder->where('p_status', 'Active');
                        $builder->orderBy('p_name', 'ASC');
                        $projects = $builder->get()->getResultArray();

                        $html = '<option value="">Select Project</option>';
                        foreach ($projects as $project) {
                            $html .= '<option value="' . $project['p_id'] . '">' . htmlspecialchars($project['p_name']) . '</option>';
                        }
                        $response['data']['projects'] = $html;
                        break;

                    case 'Leaderassignprojects':
                        // Get projects - for leaders, only their assigned projects
                        $session = service('session');
                        $admin_session = $session->get('admin_session');
                        $builder = $db->table('aa_projects');
                        $builder->select('p_id, p_name, p_number');

                        $active_only = $obj['active_only'] ?? false;
                        if ($active_only) {
                            $builder->where('p_status', 'Active');
                        }

                        // Filter by leader if user is Project Leader
                        if (isset($admin_session['u_type']) && $admin_session['u_type'] == 'Project Leader') {
                            $builder->like('p_leader', $admin_session['u_id']);
                        }

                        $builder->orderBy('p_name', 'ASC');
                        $projects = $builder->get()->getResultArray();

                        $html = '<option value="">Select Project</option>';
                        foreach ($projects as $project) {
                            $display = !empty($project['p_number']) ? $project['p_number'] . ' - ' . $project['p_name'] : $project['p_name'];
                            $html .= '<option value="' . $project['p_id'] . '">' . htmlspecialchars($display) . '</option>';
                        }
                        $response['data']['Leaderassignprojects'] = $html;
                        break;

                    case 'assigned_projects':
                        // Get projects assigned to current user
                        $session = service('session');
                        $admin_session = $session->get('admin_session');
                        $u_id = $admin_session['u_id'];
                        $u_type = $admin_session['u_type'];

                        if (in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head', 'TaskCoordinator', 'MailCoordinator'])) {
                            $builder = $db->table('aa_projects');
                            $builder->select('p_id, p_name, p_number');
                            $builder->where('p_status', 'Active');
                            $builder->orderBy('p_name', 'ASC');
                            $projects = $builder->get()->getResultArray();
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

                        $html = '<option value="">-- Select Project --</option>';
                        foreach ($projects as $project) {
                            $html .= '<option value="' . $project['p_id'] . '">' . htmlspecialchars($project['p_number'] . ' - ' . $project['p_name']) . '</option>';
                        }
                        $response['data']['assigned_projects'] = $html;
                        break;

                    case 'empprojects':
                        // Get projects assigned to a specific employee (via task assignments)
                        $u_id = $obj['u_id'] ?? '';
                        $records = $db->query("SELECT DISTINCT(p_id), P.p_name, P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New') AND u_id = '{$u_id}'")->getResultArray();

                        $html = isset($obj['title']) && $obj['title'] ? '<option value="-1">' . $obj['title'] . '</option>' : '<option value="-1">Select Project</option>';
                        if ($records) {
                            foreach ($records as $row) {
                                $selected = (isset($obj['id']) && $obj['id'] == $row['p_id']) ? ' selected="selected"' : '';
                                $html .= '<option value="' . $row['p_id'] . '"' . $selected . '>' . htmlspecialchars($row['p_number'] . ' - ' . $row['p_name']) . '</option>';
                            }
                        }
                        if (isset($obj['leave'])) {
                            $selected = (isset($obj['id']) && $obj['id'] == 0) ? ' selected="selected"' : '';
                            $html .= '<option value="0"' . $selected . '>On Leave</option>';
                        }
                        $response['data']['empprojects'] = $html;
                        break;

                    case 'emptasks':
                        // Get tasks assigned to a specific employee within a project
                        $u_id = $obj['u_id'] ?? '';
                        $p_id = $obj['p_id'] ?? '';
                        $records = $db->table('aa_tasks T')
                            ->select('T.t_id, T.t_title')
                            ->join('aa_task2user TU', 'TU.tu_t_id = T.t_id')
                            ->where('T.t_p_id', $p_id)
                            ->where('T.t_parent', 0)
                            ->where('TU.tu_u_id', $u_id)
                            ->where('TU.tu_removed', 'No')
                            ->where('T.t_status !=', 'Completed')
                            ->get()->getResultArray();

                        $html = isset($obj['title']) && $obj['title'] ? '<option value="">' . $obj['title'] . '</option>' : '<option value="">Select Task</option>';
                        if ($records) {
                            foreach ($records as $row) {
                                $selected = (isset($obj['id']) && $obj['id'] == $row['t_id']) ? ' selected="selected"' : '';
                                $html .= '<option value="' . $row['t_id'] . '"' . $selected . '>' . htmlspecialchars($row['t_title']) . '</option>';
                                // Add subtasks
                                $subtasks = $db->table('aa_tasks')->select('t_id, t_title')->where('t_parent', $row['t_id'])->get()->getResultArray();
                                if ($subtasks) {
                                    foreach ($subtasks as $sub) {
                                        $selected2 = (isset($obj['id']) && $obj['id'] == $sub['t_id']) ? ' selected="selected"' : '';
                                        $html .= '<option value="' . $sub['t_id'] . '"' . $selected2 . '>&nbsp;&nbsp;&nbsp;' . htmlspecialchars($sub['t_title']) . '</option>';
                                    }
                                }
                            }
                        }
                        $response['data']['emptasks'] = $html;
                        break;

                    case 'tasks':
                        // Get tasks assigned to the logged-in user within a project (for timesheet)
                        $session = service('session');
                        $admin_session = $session->get('admin_session');
                        $ts_u_id = $admin_session['u_id'];
                        $p_id = $obj['p_id'] ?? '';
                        $records = $db->table('aa_tasks T')
                            ->select('T.t_id, T.t_title')
                            ->join('aa_task2user TU', 'TU.tu_t_id = T.t_id')
                            ->where('T.t_p_id', $p_id)
                            ->where('T.t_parent', 0)
                            ->where('TU.tu_u_id', $ts_u_id)
                            ->where('TU.tu_removed', 'No')
                            ->where('T.t_status !=', 'Completed')
                            ->get()->getResultArray();

                        $html = isset($obj['title']) && $obj['title'] ? '<option value="">' . $obj['title'] . '</option>' : '<option value="">Select Task</option>';
                        if ($records) {
                            foreach ($records as $row) {
                                $selected = (isset($obj['id']) && $obj['id'] == $row['t_id']) ? ' selected="selected"' : '';
                                $html .= '<option value="' . $row['t_id'] . '"' . $selected . '>' . htmlspecialchars($row['t_title']) . '</option>';
                                // Add subtasks
                                $subtasks = $db->table('aa_tasks')->select('t_id, t_title')->where('t_parent', $row['t_id'])->get()->getResultArray();
                                if ($subtasks) {
                                    foreach ($subtasks as $sub) {
                                        $selected2 = (isset($obj['id']) && $obj['id'] == $sub['t_id']) ? ' selected="selected"' : '';
                                        $html .= '<option value="' . $sub['t_id'] . '"' . $selected2 . '>&nbsp;&nbsp;&nbsp;' . htmlspecialchars($sub['t_title']) . '</option>';
                                    }
                                }
                            }
                        }
                        $response['data']['tasks'] = $html;
                        break;

                    case 'timesheetprojects':
                        // Projects for timesheet - only those with non-completed tasks assigned
                        $session = service('session');
                        $admin_session = $session->get('admin_session');
                        $ts_u_id = $obj['u_id'] ?? $admin_session['u_id'];
                        if (in_array($admin_session['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'MailCoordinator', 'TaskCoordinator'])) {
                            $records = $db->table('aa_projects')->select('p_id, p_name, p_number')->where('p_status', 'Active')->orderBy('p_name', 'ASC')->get()->getResultArray();
                        } else {
                            $records = $db->query("SELECT DISTINCT P.p_id, P.p_name, P.p_number, U.u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_tasks T ON T.t_id = TU.tu_t_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND P.p_status IN ('Active', 'New') AND T.t_status != 'Completed' AND U.u_id = " . $db->escape($ts_u_id) . " ORDER BY P.p_name")->getResultArray();
                        }
                        $html = isset($obj['title']) && $obj['title'] ? '<option value="-1">' . $obj['title'] . '</option>' : '<option value="-1">Select Project</option>';
                        if ($records) {
                            foreach ($records as $row) {
                                $selected = (isset($obj['id']) && $obj['id'] == $row['p_id']) ? ' selected="selected"' : '';
                                $html .= '<option value="' . $row['p_id'] . '"' . $selected . '>' . htmlspecialchars($row['p_number'] . ' - ' . $row['p_name']) . '</option>';
                            }
                        }
                        if (isset($obj['leave'])) {
                            $selected = (isset($obj['id']) && $obj['id'] == 0) ? ' selected="selected"' : '';
                            $html .= '<option value="0"' . $selected . '>On Leave</option>';
                        }
                        $response['data']['timesheetprojects'] = $html;
                        break;

                    case 'project_leaders':
                        // Get leaders of a specific project + Master Admin, Bim Head, TaskCoordinator
                        $p_id = $obj['p_id'] ?? '';
                        if ($p_id) {
                            $project = $db->table('aa_projects')->select('p_leader')->where('p_id', $p_id)->get()->getRowArray();
                            $leaderIds = [];
                            if ($project && !empty($project['p_leader'])) {
                                $leaderIds = array_filter(explode(',', $project['p_leader']));
                            }

                            // Also get Master Admin, Bim Head, TaskCoordinator users
                            $adminUsers = $db->table('aa_users')
                                ->select('u_id')
                                ->where('u_status', 'Active')
                                ->whereIn('u_type', ['Master Admin', 'Bim Head', 'TaskCoordinator'])
                                ->get()->getResultArray();
                            foreach ($adminUsers as $au) {
                                $leaderIds[] = $au['u_id'];
                            }
                            $leaderIds = array_unique(array_filter($leaderIds));

                            $html = '<option value="">Select Leader</option>';
                            if (!empty($leaderIds)) {
                                $leaders = $db->table('aa_users')
                                    ->select('u_id, u_name, u_type')
                                    ->whereIn('u_id', $leaderIds)
                                    ->where('u_status', 'Active')
                                    ->orderBy('u_name', 'ASC')
                                    ->get()->getResultArray();
                                foreach ($leaders as $leader) {
                                    $html .= '<option value="' . $leader['u_id'] . '">' . htmlspecialchars($leader['u_name'] . ' (' . $leader['u_type'] . ')') . '</option>';
                                }
                            }
                            $response['data']['project_leaders'] = $html;
                        }
                        break;
                }
            }
        }

        echo json_encode($response);
        exit;
    }

    public function settings()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $db = \Config\Database::connect();

        $id = $request->getPost('id');
        $act = $request->getPost('act');

        // Handle Add/Edit (update setting value)
        if ($act === 'add' && $id) {
            $s_value = $request->getPost('s_value') ?? '';
            $db->table('aa_settings')->where('id', $id)->update(['s_value' => $s_value]);
            echo json_encode(['status' => 'pass', 'message' => 'Setting updated successfully.']);
            exit;
        }

        // Handle single record fetch for edit
        if ($act === 'list' && $id) {
            $builder = $db->table('aa_settings');
            $setting = $builder->where('id', $id)->get()->getRowArray();

            if ($setting) {
                echo json_encode([
                    'status' => 'pass',
                    'data' => $setting
                ]);
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Setting not found'
                ]);
            }
            exit;
        }

        // Handle DataTables list request
        $builder = $db->table('aa_settings');
        $settings = $builder->get()->getResultArray();

        $data = [];
        foreach ($settings as $setting) {
            $row = [];
            $row[] = $setting['s_title'] ?? '';
            $row[] = $setting['s_key'] ?? '';
            $row[] = $setting['s_value'] ?? '';
            $row[] = '<a href="javascript://" onclick="showAddEditForm(' . ($setting['id'] ?? 0) . ')" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>';
            $data[] = $row;
        }

        $response = [
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => count($settings),
            'recordsFiltered' => count($settings),
            'data' => $data
        ];

        echo json_encode($response);
        exit;
    }

    public function leaves()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();

        $act = $request->getPost('act');
        $l_id = $request->getPost('l_id');

        // Handle Add/Edit leave request
        if ($act === 'add') {
            $leaveData = [
                'l_u_id' => $admin_session['u_id'],
                'l_from_date' => !empty($request->getPost('l_from_date')) ? convert_display2db($request->getPost('l_from_date')) : null,
                'l_to_date' => !empty($request->getPost('l_to_date')) ? convert_display2db($request->getPost('l_to_date')) : null,
                'l_message' => $request->getPost('l_message') ?? '',
                'l_is_halfday' => $request->getPost('l_is_halfday') ?? 'No',
                'l_halfday_time' => $request->getPost('l_halfday_time') ?? '',
                'l_is_hourly' => $request->getPost('l_is_hourly') ?? 'No',
                'l_hourly_time' => $request->getPost('l_hourly_time') ?? '',
                'l_hourly_time_hour' => $request->getPost('l_hourly_time_hour') ?? '',
            ];

            try {
                if ($l_id > 0) {
                    // Reset status to Pending so it goes back for approval
                    $leaveData['l_status'] = 'Pending';
                    $leaveData['l_approved_by'] = '';
                    $leaveData['l_approved_by_id'] = '';
                    $leaveData['l_reply'] = '';
                    $db->table('aa_leaves')->where('l_id', $l_id)->update($leaveData);
                } else {
                    $leaveData['l_create_date'] = date('Y-m-d');
                    $leaveData['l_status'] = 'Pending';
                    $db->table('aa_leaves')->insert($leaveData);
                }
                echo json_encode(['status' => 'pass', 'message' => 'Leave request saved successfully.']);
            } catch (\Exception $ex) {
                echo json_encode(['status' => 'fail', 'type' => 'popup', 'message' => $ex->getMessage()]);
            }
            exit;
        }

        // Handle Delete
        if ($act === 'del') {
            if ($l_id > 0) {
                $leave = $db->table('aa_leaves')->where('l_id', $l_id)->get()->getRowArray();
                if (!$leave) {
                    echo json_encode(['status' => 'fail', 'message' => 'Leave not found.']);
                    exit;
                }

                $isMasterAdminOrBimHead = in_array($admin_session['u_type'] ?? '', ['Master Admin', 'Bim Head']);
                $isOwnLeave = ($leave['l_u_id'] == $admin_session['u_id']);

                // Master Admin and Bim Head can delete any leave; creator can delete only if Pending
                if ($isMasterAdminOrBimHead || ($isOwnLeave && ($leave['l_status'] ?? '') === 'Pending')) {
                    $db->table('aa_leaves')->where('l_id', $l_id)->delete();
                    echo json_encode(['status' => 'pass', 'message' => 'Leave deleted successfully.']);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'You are not authorized to delete this leave.']);
                }
            } else {
                echo json_encode(['status' => 'fail', 'message' => 'Invalid leave record.']);
            }
            exit;
        }

        // Handle Approve/Decline
        if ($act === 'Approve') {
            if ($l_id > 0) {
                // Project Leader can only approve leaves of their assigned employees
                if (($admin_session['u_type'] ?? '') === 'Project Leader') {
                    $leaveUser = $db->table('aa_leaves L')
                        ->select('U.u_leader')
                        ->join('aa_users U', 'L.l_u_id = U.u_id', 'left')
                        ->where('L.l_id', $l_id)
                        ->get()->getRowArray();
                    if (!$leaveUser || ($leaveUser['u_leader'] ?? '') != $admin_session['u_id']) {
                        echo json_encode(['status' => 'fail', 'message' => 'You are not authorized to approve this leave.']);
                        exit;
                    }
                }

                $l_status = $request->getPost('l_status');
                $l_reply = $request->getPost('l_reply') ?? '';
                $leaveOwner = $db->table('aa_leaves')->select('l_u_id, l_from_date, l_to_date')->where('l_id', $l_id)->get()->getRowArray();
                $db->table('aa_leaves')->where('l_id', $l_id)->update([
                    'l_status' => $l_status,
                    'l_reply' => $l_reply,
                    'l_approved_by' => $admin_session['u_name'],
                    'l_approved_by_id' => $admin_session['u_id'],
                ]);
                // Notify the leave owner
                if ($leaveOwner && $leaveOwner['l_u_id'] != $admin_session['u_id']) {
                    $leaveDate = date('d-m-Y', strtotime($leaveOwner['l_from_date']));
                    $db->table('aa_desktop_notification_queue')->insert([
                        'u_id'    => $leaveOwner['l_u_id'],
                        'title'   => 'Leave ' . $l_status,
                        'message' => 'Your leave request for ' . $leaveDate . ' has been ' . strtolower($l_status) . ' by ' . $admin_session['u_name'],
                        'payload' => json_encode(['screen_name' => 'Leave']),
                        'is_sent' => 0,
                    ]);
                }
                echo json_encode(['status' => 'pass', 'message' => 'Leave ' . $l_status . ' successfully.']);
            }
            exit;
        }

        // Handle loadinfo (for Approve modal)
        if ($act === 'loadinfo') {
            if ($l_id > 0) {
                try {
                    $leave = $db->table('aa_leaves L')
                        ->select('L.*, U.u_name, U.u_mobile, U.u_email')
                        ->join('aa_users U', 'L.l_u_id = U.u_id', 'left')
                        ->where('L.l_id', $l_id)
                        ->get()->getRowArray();
                } catch (\Exception $e) {
                    echo json_encode(['status' => 'fail', 'message' => 'Error loading leave info: ' . $e->getMessage()]);
                    exit;
                }

                if ($leave) {
                    // Count active projects
                    try {
                        $activeProjects = $db->table('aa_task2user')
                            ->distinct()->select('tu_p_id')
                            ->where('tu_u_id', $leave['l_u_id'])
                            ->where('tu_removed', 'No')
                            ->countAllResults();
                    } catch (\Exception $e) {
                        $activeProjects = 0;
                    }

                    // Count onhand tasks
                    try {
                        $onhandTasks = $db->table('aa_tasks T')
                            ->join('aa_task2user TU', 'T.t_id = TU.tu_t_id', 'inner')
                            ->where('TU.tu_u_id', $leave['l_u_id'])
                            ->where('TU.tu_removed', 'No')
                            ->whereNotIn('T.t_status', ['Completed', 'Cancelled'])
                            ->countAllResults();
                    } catch (\Exception $e) {
                        $onhandTasks = 0;
                    }

                    $from = !empty($leave['l_from_date']) ? convert_db2display($leave['l_from_date'], false) : '';
                    $to = !empty($leave['l_to_date']) ? convert_db2display($leave['l_to_date'], false) : '';
                    $dateStr = ($from === $to) ? $from : $from . ' to ' . $to;

                    $msgHtml = '<b>Date:</b> ' . $dateStr . '<br/>';
                    if (($leave['l_is_halfday'] ?? '') === 'Yes') {
                        $msgHtml .= '<b>Half Day:</b> ' . ($leave['l_halfday_time'] ?? '') . '<br/>';
                    }
                    if (($leave['l_is_hourly'] ?? '') === 'Yes') {
                        $msgHtml .= '<b>Hourly:</b> ' . ($leave['l_hourly_time'] ?? '') . ' (' . ($leave['l_hourly_time_hour'] ?? '') . ' hrs)<br/>';
                    }
                    $msgHtml .= ($leave['l_message'] ?? '');

                    $imgUrl = '';

                    echo json_encode([
                        'status' => 'pass',
                        'data' => [
                            'l_id' => $leave['l_id'],
                            'u_name' => $leave['u_name'] ?? '',
                            'u_mobile' => $leave['u_mobile'] ?? '',
                            'u_email' => $leave['u_email'] ?? '',
                            'u_active' => $activeProjects,
                            'u_tasks' => $onhandTasks,
                            'l_message' => $msgHtml,
                        ],
                        'img_url' => $imgUrl,
                    ]);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Leave not found.']);
                }
            }
            exit;
        }

        // Handle single record fetch for edit
        if ($act === 'list' && $l_id > 0) {
            $leave = $db->table('aa_leaves')->where('l_id', $l_id)->get()->getRowArray();
            if ($leave) {
                $leave['l_from_date'] = !empty($leave['l_from_date']) ? convert_db2display($leave['l_from_date'], false) : '';
                $leave['l_to_date'] = !empty($leave['l_to_date']) ? convert_db2display($leave['l_to_date'], false) : '';
                echo json_encode(['status' => 'pass', 'data' => $leave]);
            } else {
                echo json_encode(['status' => 'fail', 'message' => 'Leave not found.']);
            }
            exit;
        }

        // Handle DataTables list request
        $builder = $db->table('aa_leaves L');
        $builder->select('L.*, U.u_name as employee_name, U.u_leader');
        $builder->join('aa_users U', 'L.l_u_id = U.u_id', 'left');

        // Non-admin users only see their own leaves
        if (($admin_session['u_type'] ?? '') === 'Project Leader') {
            // Project Leader sees own leaves + leaves of employees assigned to them (u_leader)
            $builder->groupStart()
                ->where('L.l_u_id', $admin_session['u_id'])
                ->orWhere('U.u_leader', $admin_session['u_id'])
            ->groupEnd();
        } elseif (!in_array($admin_session['u_type'] ?? '', ['Master Admin', 'Super Admin', 'Bim Head'])) {
            $builder->where('L.l_u_id', $admin_session['u_id']);
        }

        $builder->orderBy('L.l_id', 'DESC');
        $leaves = $builder->get()->getResultArray();

        $data = [];
        foreach ($leaves as $leave) {
            $row = [];
            $row[] = $leave['employee_name'] ?? '';
            $row[] = isset($leave['l_create_date']) ? convert_db2display($leave['l_create_date'], false) : '';
            $row[] = isset($leave['l_from_date']) ? convert_db2display($leave['l_from_date'], false) : '';
            $row[] = isset($leave['l_to_date']) ? convert_db2display($leave['l_to_date'], false) : '';
            $row[] = $leave['l_message'] ?? '';
            $row[] = $leave['l_status'] ?? '';
            $row[] = ($leave['l_is_halfday'] ?? '') === 'Yes' ? 'Yes (' . ($leave['l_halfday_time'] ?? '') . ')' : 'No';
            $row[] = ($leave['l_is_hourly'] ?? '') === 'Yes' ? 'Yes (' . ($leave['l_hourly_time'] ?? '') . ')' : 'No';

            // Action buttons
            $actions = '<div class="actions">';
            $isCreator = ($leave['l_u_id'] == $admin_session['u_id']);
            $isPending = (($leave['l_status'] ?? '') === 'Pending');
            $isAdmin = in_array($admin_session['u_type'] ?? '', ['Master Admin', 'Super Admin', 'Bim Head']);
            $isLeader = (($admin_session['u_type'] ?? '') === 'Project Leader');

            // Creator can edit and delete (delete only if still Pending)
            if ($isCreator) {
                $actions .= '<a href="javascript://" onclick="showAddEditForm(' . $leave['l_id'] . ')" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a> ';
                if ($isPending) {
                    $actions .= '<a href="javascript://" onclick="deleteRecord(' . $leave['l_id'] . ')" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a> ';
                }
            }

            // Approve/Decline only when Pending
            // Note: visibility query already ensures Project Leader only sees their own employees' leaves
            if ($isPending) {
                if ($isAdmin || ($isLeader && !$isCreator)) {
                    $actions .= '<a href="javascript://" onclick="Approve(' . $leave['l_id'] . ')" class="btn btn-success btn-xs" title="Manage"><i class="fa fa-check"></i></a> ';
                }
            }

            // Master Admin and Bim Head can delete any leave
            if (in_array($admin_session['u_type'] ?? '', ['Master Admin', 'Bim Head']) && !$isCreator) {
                $actions .= '<a href="javascript://" onclick="deleteRecord(' . $leave['l_id'] . ')" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a> ';
            }
            $actions .= '</div>';
            $row[] = $actions;

            $data[] = $row;
        }

        $response = [
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => count($leaves),
            'recordsFiltered' => count($leaves),
            'data' => $data
        ];

        echo json_encode($response);
        exit;
    }

    public function holidays()
    {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $request = service('request');
        $db = \Config\Database::connect();
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $act = $request->getPost('act');

        // Only Master Admin / Bim Head can add or delete
        if (!in_array($admin_session['u_type'], ['Master Admin', 'Bim Head']) && in_array($act, ['add', 'del'])) {
            echo json_encode(['status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.']);
            exit;
        }

        switch ($act) {
            case 'add':
                $h_id = $request->getPost('h_id');
                $h_date = $request->getPost('h_date');
                $h_title = $request->getPost('h_title');

                $data = [
                    'h_date' => convert_display2db($h_date),
                    'h_title' => $h_title,
                ];

                try {
                    if ($h_id > 0) {
                        $db->table('aa_holidays')->where('h_id', $h_id)->update($data);
                    } else {
                        $db->table('aa_holidays')->insert($data);
                    }
                    echo json_encode(['status' => 'pass', 'message' => 'Holiday is saved.']);
                } catch (\Exception $ex) {
                    echo json_encode(['status' => 'fail', 'type' => 'popup', 'message' => $ex->getMessage()]);
                }
                exit;

            case 'del':
                $h_id = $request->getPost('h_id');
                if ($h_id > 0) {
                    try {
                        $db->table('aa_holidays')->where('h_id', $h_id)->delete();
                        echo json_encode(['status' => 'pass', 'message' => 'Holiday has been deleted successfully.']);
                    } catch (\Exception $ex) {
                        echo json_encode(['status' => 'fail', 'message' => $ex->getMessage()]);
                    }
                }
                exit;

            case 'list':
            default:
                $h_id = $request->getPost('h_id');

                // Single record fetch (for edit form)
                if ($h_id > 0) {
                    $record = $db->table('aa_holidays')->where('h_id', $h_id)->get()->getRowArray();
                    if ($record) {
                        $record['h_date'] = convert_db2display($record['h_date'], false);
                        echo json_encode(['status' => 'pass', 'data' => $record]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Selected record is not available.']);
                    }
                    exit;
                }

                // List all holidays
                $draw = $request->getPost('draw') ?? 1;
                $offset = $request->getPost('start') ?? 0;
                $limit = $request->getPost('length') ?? -1;

                $builder = $db->table('aa_holidays');
                $builder->orderBy('h_date', 'ASC');
                $holidays = $builder->get()->getResultArray();

                $totalData = count($holidays);

                $data = [];
                foreach ($holidays as $single_record) {
                    $row = [];
                    $row[] = date("l", strtotime($single_record['h_date']));
                    $row[] = date("M d, Y", strtotime($single_record['h_date']));
                    $row[] = $single_record['h_title'] ?? '';

                    if (in_array($admin_session['u_type'], ['Master Admin', 'Bim Head'])) {
                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['h_id'] . '\')"><i class="fa fa-edit"></i></a>&nbsp; ';
                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['h_id'] . '\')"><i class="fa fa-trash"></i></a>';
                        $row[] = $anchors;
                    } else {
                        $row[] = '';
                    }

                    $data[] = $row;
                }

                $response = [
                    'draw' => intval($draw),
                    'recordsTotal' => $totalData,
                    'recordsFiltered' => $totalData,
                    'data' => $data
                ];

                echo json_encode($response);
                exit;
        }
    }

    public function projectmessages()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();

        $act = $request->getPost('act');

        switch ($act) {
            case 'add':
                $pm_id = $request->getPost('pm_id') ?? 0;
                $pm_p_id = $request->getPost('pm_p_id');
                $pm_text = $request->getPost('pm_text');
                $pm_descipline = $request->getPost('pm_descipline') ?? 'ALL';
                $u_ids = $request->getPost('u_ids') ?? [];

                $data = [
                    'pm_p_id' => $pm_p_id ?: null,
                    'pm_created_by' => $admin_session['u_id'],
                    'pm_text' => $pm_text,
                    'pm_datetime' => date('Y-m-d H:i:s'),
                    'pm_descipline' => $pm_descipline,
                    'pm_deleted' => 0
                ];

                if ($pm_id > 0) {
                    $db->table('aa_project_messages')->where('pm_id', $pm_id)->update($data);
                } else {
                    $db->table('aa_project_messages')->insert($data);
                    $pm_id = $db->insertID();
                }

                // Add recipients
                if (!empty($u_ids)) {
                    if (in_array('ALL_PROJECT', $u_ids) && $pm_p_id) {
                        // Get all project members
                        $project = $db->table('aa_projects')->where('p_id', $pm_p_id)->get()->getRowArray();
                        $u_ids = [];
                        if ($project) {
                            $leader_ids = !empty($project['p_leader']) ? explode(',', $project['p_leader']) : [];
                            $u_ids = array_merge($u_ids, $leader_ids);
                        }
                        // Get users assigned to tasks in this project
                        $projectUsers = $db->table('aa_task2user')
                            ->distinct()
                            ->select('tu_u_id')
                            ->where('tu_p_id', $pm_p_id)
                            ->where('tu_removed', 'No')
                            ->get()->getResultArray();
                        foreach ($projectUsers as $pu) {
                            $u_ids[] = $pu['tu_u_id'];
                        }
                        $u_ids = array_unique(array_filter($u_ids));
                    }
                    foreach ($u_ids as $uid) {
                        if (!is_numeric($uid)) continue;
                        $exists = $db->table('aa_project_message_users')
                            ->where('pmu_pm_id', $pm_id)
                            ->where('pmu_u_id', $uid)
                            ->countAllResults();
                        if (!$exists) {
                            $db->table('aa_project_message_users')->insert([
                                'pmu_pm_id' => $pm_id,
                                'pmu_u_id' => $uid,
                                'pmu_read' => 0,
                                'pmu_added_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }

                // Desktop notification for new message
                if (!empty($u_ids) && $pm_id > 0) {
                    $notifTitle = "New Message Added";
                    $notifPayload = json_encode(['screen_name' => 'Message', 'id' => $pm_id]);
                    foreach (array_unique($u_ids) as $nuid) {
                        if (!is_numeric($nuid) || $nuid == $admin_session['u_id']) continue;
                        $db->table('aa_desktop_notification_queue')->insert([
                            'u_id' => $nuid, 'title' => $notifTitle, 'message' => $pm_text,
                            'payload' => $notifPayload, 'is_sent' => 0,
                        ]);
                    }
                }

                echo json_encode(['status' => 'pass', 'message' => 'Message saved.']);
                break;

            case 'list':
                $pm_id = $request->getPost('pm_id') ?? $request->getPost('id');

                if ($pm_id) {
                    $msg = $db->table('aa_project_messages')->where('pm_id', $pm_id)->get()->getRowArray();
                    if ($msg) {
                        echo json_encode(['status' => 'pass', 'data' => $msg]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Message not found.']);
                    }
                    break;
                }

                // DataTables list
                $draw = $request->getPost('draw') ?? 1;
                $start = $request->getPost('start') ?? 0;
                $length = $request->getPost('length') ?? 25;
                $project_id = $request->getPost('project_id');
                $search_date = $request->getPost('search_date');
                $search_discipline = $request->getPost('search_discipline');
                $leader_id = $request->getPost('leader_id');

                $builder = $db->table('aa_project_messages PM');
                $builder->select('PM.*, P.p_name, P.p_number, U.u_name as creator_name,
                    (SELECT COUNT(*) FROM aa_project_message_replies WHERE pmr_pm_id = PM.pm_id) as reply_count');
                $builder->join('aa_projects P', 'PM.pm_p_id = P.p_id', 'left');
                $builder->join('aa_users U', 'PM.pm_created_by = U.u_id', 'left');
                $builder->where('PM.pm_deleted', 0);

                // Role-based filtering
                $u_type = $admin_session['u_type'];
                if ($u_type === 'Project Leader') {
                    // Project Leader: only messages they created or were sent to, AND only from their assigned projects
                    $plProjects = $db->table('aa_projects')
                        ->select('p_id')
                        ->like('p_leader', $admin_session['u_id'])
                        ->get()->getResultArray();
                    $plProjectIds = array_column($plProjects, 'p_id');

                    $builder->groupStart()
                        ->where('PM.pm_created_by', $admin_session['u_id'])
                        ->orWhereIn('PM.pm_id', function($subquery) use ($admin_session) {
                            return $subquery->select('pmu_pm_id')
                                ->from('aa_project_message_users')
                                ->where('pmu_u_id', $admin_session['u_id']);
                        })
                        ->groupEnd();

                    if (!empty($plProjectIds)) {
                        $builder->whereIn('PM.pm_p_id', $plProjectIds);
                    } else {
                        $builder->where('PM.pm_id', 0); // no assigned projects, no results
                    }
                } elseif (!in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head', 'TaskCoordinator', 'MailCoordinator'])) {
                    $builder->groupStart()
                        ->where('PM.pm_created_by', $admin_session['u_id'])
                        ->orWhereIn('PM.pm_id', function($subquery) use ($admin_session) {
                            return $subquery->select('pmu_pm_id')
                                ->from('aa_project_message_users')
                                ->where('pmu_u_id', $admin_session['u_id']);
                        })
                        ->groupEnd();
                }

                // Filter by selected leader - show messages from projects where this leader is p_leader
                if (!empty($leader_id)) {
                    $leaderProjects = $db->table('aa_projects')
                        ->select('p_id')
                        ->like('p_leader', $leader_id)
                        ->where('p_status', 'Active')
                        ->get()->getResultArray();
                    $leaderProjectIds = array_column($leaderProjects, 'p_id');
                    if (!empty($leaderProjectIds)) {
                        $builder->whereIn('PM.pm_p_id', $leaderProjectIds);
                    } else {
                        $builder->where('PM.pm_id', 0); // no projects, no results
                    }
                }

                if (!empty($project_id)) {
                    $builder->where('PM.pm_p_id', $project_id);
                }
                if (!empty($search_date)) {
                    $builder->where('DATE(PM.pm_datetime)', $search_date);
                }
                if (!empty($search_discipline)) {
                    $builder->where('PM.pm_descipline', $search_discipline);
                }

                $totalRecords = $builder->countAllResults(false);
                $builder->orderBy('PM.pm_datetime', 'DESC');
                if ($length != -1) {
                    $builder->limit($length, $start);
                }
                $messages = $builder->get()->getResultArray();

                // Mark messages as read for current user
                foreach ($messages as $msg) {
                    $db->table('aa_project_message_users')
                        ->where('pmu_pm_id', $msg['pm_id'])
                        ->where('pmu_u_id', $admin_session['u_id'])
                        ->update(['pmu_read' => 1]);
                }

                $data = [];
                foreach ($messages as $msg) {
                    $row = [];
                    $row[] = date('M d, Y', strtotime($msg['pm_datetime']));
                    $row[] = $msg['p_name'] ?? 'General';
                    $row[] = htmlspecialchars($msg['pm_text'] ?? '');
                    $row[] = $msg['pm_descipline'] ?? 'ALL';
                    $row[] = $msg['reply_count'] ?? 0;

                    $actions = '<a href="javascript://" onclick="showThreadModal(' . $msg['pm_id'] . ')" class="btn btn-info btn-xs"><i class="fa fa-comments"></i></a> ';
                    if (in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head']) || $msg['pm_created_by'] == $admin_session['u_id']) {
                        $actions .= '<a href="javascript://" onclick="deleteProjectMessage(' . $msg['pm_id'] . ')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>';
                    }
                    $row[] = $actions;
                    $data[] = $row;
                }

                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data
                ]);
                break;

            case 'thread':
                $pm_id = $request->getPost('pm_id');

                $msg = $db->table('aa_project_messages PM')
                    ->select('PM.*, U.u_name as creator_name, P.p_name')
                    ->join('aa_users U', 'PM.pm_created_by = U.u_id', 'left')
                    ->join('aa_projects P', 'PM.pm_p_id = P.p_id', 'left')
                    ->where('PM.pm_id', $pm_id)
                    ->get()->getRowArray();

                if (!$msg) {
                    echo json_encode(['status' => 'fail', 'message' => 'Message not found.']);
                    break;
                }

                $replies = $db->table('aa_project_message_replies R')
                    ->select('R.*, U.u_name')
                    ->join('aa_users U', 'R.pmr_u_id = U.u_id', 'left')
                    ->where('R.pmr_pm_id', $pm_id)
                    ->orderBy('R.pmr_datetime', 'ASC')
                    ->get()->getResultArray();

                // Build HTML
                $header = '<div class="main-msg">';
                $header .= '<strong>' . htmlspecialchars($msg['creator_name'] ?? '') . '</strong>';
                $header .= ' <small class="text-muted">' . date('M d, Y h:i A', strtotime($msg['pm_datetime'])) . '</small>';
                if ($msg['p_name']) {
                    $header .= ' <span class="label label-info">' . htmlspecialchars($msg['p_name']) . '</span>';
                }
                $header .= '<p>' . nl2br(htmlspecialchars($msg['pm_text'])) . '</p>';
                $header .= '</div>';

                $replies_html = '';
                foreach ($replies as $reply) {
                    $replies_html .= '<div class="reply-msg" style="margin-left:20px; padding:5px; border-left:2px solid #ddd; margin-bottom:10px;">';
                    $replies_html .= '<strong>' . htmlspecialchars($reply['u_name'] ?? '') . '</strong>';
                    $replies_html .= ' <small class="text-muted">' . date('M d, Y h:i A', strtotime($reply['pmr_datetime'])) . '</small>';
                    $replies_html .= '<p>' . nl2br(htmlspecialchars($reply['pmr_text'])) . '</p>';
                    $replies_html .= '</div>';
                }

                echo json_encode([
                    'status' => 'pass',
                    'data' => [
                        'header' => $header,
                        'replies_html' => $replies_html
                    ]
                ]);
                break;

            case 'reply':
                $pm_id = $request->getPost('pm_id');
                $rep_text = $request->getPost('rep_text');

                $db->table('aa_project_message_replies')->insert([
                    'pmr_pm_id' => $pm_id,
                    'pmr_u_id' => $admin_session['u_id'],
                    'pmr_text' => $rep_text,
                    'pmr_datetime' => date('Y-m-d H:i:s')
                ]);

                // Mark other participants as unread
                $db->table('aa_project_message_users')
                    ->where('pmu_pm_id', $pm_id)
                    ->where('pmu_u_id !=', $admin_session['u_id'])
                    ->update(['pmu_read' => 0]);

                // Desktop notification for reply - notify message creator
                $origMsg = $db->table('aa_project_messages')->select('pm_created_by')->where('pm_id', $pm_id)->get()->getRowArray();
                if ($origMsg && $origMsg['pm_created_by'] != $admin_session['u_id']) {
                    $senderName = $admin_session['u_name'] ?? 'Someone';
                    $db->table('aa_desktop_notification_queue')->insert([
                        'u_id' => $origMsg['pm_created_by'],
                        'title' => 'New Reply',
                        'message' => $senderName . ' replied to your message',
                        'payload' => json_encode(['screen_name' => 'Message', 'pm_id' => $pm_id]),
                        'is_sent' => 0,
                    ]);
                }

                echo json_encode(['status' => 'pass', 'message' => 'Reply saved.']);
                break;

            case 'del':
                $pm_id = $request->getPost('pm_id');

                // Check for replies
                $replyCount = $db->table('aa_project_message_replies')
                    ->where('pmr_pm_id', $pm_id)
                    ->countAllResults();

                if ($replyCount > 0) {
                    echo json_encode(['status' => 'fail', 'message' => 'Cannot delete message with replies.']);
                    break;
                }

                $db->table('aa_project_messages')
                    ->where('pm_id', $pm_id)
                    ->update(['pm_deleted' => 1]);

                echo json_encode(['status' => 'pass', 'message' => 'Message deleted.']);
                break;

            case 'get_project_leaders':
                $project_id = $request->getPost('project_id');
                $leaders = [];
                if ($project_id) {
                    $project = $db->table('aa_projects')->select('p_leader')->where('p_id', $project_id)->get()->getRowArray();
                    if ($project && !empty($project['p_leader'])) {
                        $leaderIds = array_filter(explode(',', $project['p_leader']));
                        if (!empty($leaderIds)) {
                            // Only return Project Leader type users from the p_leader list
                            $leaders = $db->table('aa_users')
                                ->select('u_id, u_name')
                                ->whereIn('u_id', $leaderIds)
                                ->where('u_status', 'Active')
                                ->where('u_type', 'Project Leader')
                                ->orderBy('u_name', 'ASC')
                                ->get()->getResultArray();
                        }
                    }
                }
                echo json_encode(['status' => 'pass', 'data' => $leaders]);
                break;

            default:
                echo json_encode(['status' => 'fail', 'message' => 'Invalid action.']);
                break;
        }
        exit;
    }

    public function tickets()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();

        echo json_encode([
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ]);
        exit;
    }

    public function ticket_categories()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        echo json_encode([
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ]);
        exit;
    }

    public function timesheet()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();
        $act = $request->getPost('act');
        $at_u_id = $request->getPost('at_u_id') ?? $admin_session['u_id'];

        // Get at_days_back from settings
        $setting = $db->table('aa_settings')->where('s_key', 'at_days_back')->get()->getRowArray();
        $at_days_back = $setting ? (int)$setting['s_value'] : 7;

        switch ($act) {
            case 'add':
                $at_id = $request->getPost('at_id');
                $data = [
                    'at_p_id' => $request->getPost('at_p_id'),
                    'at_t_id' => $request->getPost('at_t_id'),
                    'at_u_id' => $at_u_id,
                    'at_date' => convert_display2db($request->getPost('at_date')),
                    'at_start' => $request->getPost('at_start'),
                    'at_end' => $request->getPost('at_end'),
                    'at_comment' => $request->getPost('at_comment'),
                ];

                // Validate date (within at_days_back days)
                $utype = $admin_session['u_type'];
                if ($utype != 'Bim Head') {
                    $in_date = new \DateTime($data['at_date']);
                    $startdate = new \DateTime("today");
                    $enddate = new \DateTime("today");
                    $startdate->modify('-' . $at_days_back . ' day');
                    if ($in_date < $startdate || $in_date > $enddate) {
                        echo json_encode(['status' => 'fail', 'type' => 'popup', 'message' => 'You can only add/edit records for today and ' . $at_days_back . ' days behind.']);
                        exit;
                    }
                }
                // Validate time
                if ($data['at_start'] > $data['at_end']) {
                    echo json_encode(['status' => 'fail', 'type' => 'popup', 'message' => 'TO time must be more than FROM time.']);
                    exit;
                }

                if ($at_id > 0) {
                    $existing = $db->table('aa_attendance')->where(['at_id' => $at_id, 'at_u_id' => $at_u_id])->get()->getRowArray();
                    if ($existing) {
                        $db->table('aa_attendance')->where('at_id', $at_id)->update($data);
                        $this->updateTaskHours($db, $existing['at_t_id']);
                    }
                } else {
                    $db->table('aa_attendance')->insert($data);
                    $this->updateTaskHours($db, $data['at_t_id']);
                }
                echo json_encode(['status' => 'pass', 'message' => 'Timesheet data is saved.']);
                break;

            case 'del':
                $at_id = $request->getPost('at_id');
                if ($at_id > 0) {
                    $existing = $db->table('aa_attendance')->where(['at_id' => $at_id, 'at_u_id' => $at_u_id])->get()->getRowArray();
                    if ($existing) {
                        $db->table('aa_attendance')->where('at_id', $at_id)->delete();
                        $this->updateTaskHours($db, $existing['at_t_id']);
                        echo json_encode(['status' => 'pass', 'message' => 'Timesheet data has been deleted successfully.']);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Record does not exist.']);
                    }
                }
                break;

            case 'total_time':
                $at_id = $request->getPost('at_id');
                if ($at_id > 0) {
                    $record = $db->table('aa_attendance A')
                        ->select('A.*, P.p_name, T.t_title')
                        ->join('aa_projects P', 'P.p_id = A.at_p_id', 'left')
                        ->join('aa_tasks T', 'T.t_id = A.at_t_id', 'left')
                        ->where(['A.at_id' => $at_id, 'A.at_u_id' => $at_u_id])
                        ->get()->getRowArray();
                    if ($record) {
                        $record['at_date'] = convert_db2display($record['at_date']);
                        echo json_encode(['status' => 'pass', 'data' => $record]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Selected record is not available.']);
                    }
                } else {
                    $at_start_sdate = convert_display2db($request->getPost('at_start_sdate'));
                    $at_date = convert_display2db($request->getPost('at_date'));
                    $result = $db->query("SELECT u_name, u_id, SUM(whours) as work_hours FROM (SELECT u_name, u_id, ((at_end - at_start) / 60) as whours FROM aa_users U INNER JOIN aa_attendance ATT ON U.u_id = ATT.at_u_id AND ATT.at_date BETWEEN '{$at_start_sdate}' AND '{$at_date}' WHERE u_id = '{$at_u_id}') AS DB2 GROUP BY u_id")->getResultArray();
                    if (!empty($result)) {
                        $n = $result[0]['work_hours'];
                        $whole = floor($n);
                        $fraction = $n - $whole;
                        if ($fraction == 0.75) $total_salary = 0.45;
                        elseif ($fraction == 0.25) $total_salary = 0.15;
                        elseif ($fraction == 0.50) $total_salary = 0.30;
                        else $total_salary = $fraction;
                        $total_hrs = "<b>Total Hours Worked :  " . number_format($whole + $total_salary, 2) . " hr</b>";
                    } else {
                        $total_hrs = "<b>Total Hours Worked : 0 hr</b>";
                    }
                    echo json_encode(['status' => 'pass', 'message' => '', 'total_hrs' => $total_hrs]);
                }
                break;

            case 'list':
            default:
                $at_id = $request->getPost('at_id');
                if ($at_id > 0) {
                    $record = $db->table('aa_attendance A')
                        ->select('A.*, P.p_name, T.t_title')
                        ->join('aa_projects P', 'P.p_id = A.at_p_id', 'left')
                        ->join('aa_tasks T', 'T.t_id = A.at_t_id', 'left')
                        ->where(['A.at_id' => $at_id, 'A.at_u_id' => $at_u_id])
                        ->get()->getRowArray();
                    if ($record) {
                        $record['at_date'] = convert_db2display($record['at_date']);
                        echo json_encode(['status' => 'pass', 'data' => $record]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Selected record is not available.']);
                    }
                } else {
                    $draw = $request->getPost('draw') ?? 1;
                    $offset = $request->getPost('start') ?? 0;
                    $limit = $request->getPost('length') ?? 25;
                    $at_start_sdate = convert_display2db($request->getPost('at_start_sdate'));
                    $at_date = convert_display2db($request->getPost('at_date'));

                    $builder = $db->table('aa_attendance A');
                    $builder->select('A.*, P.p_name, T.t_title');
                    $builder->join('aa_projects P', 'P.p_id = A.at_p_id', 'left');
                    $builder->join('aa_tasks T', 'T.t_id = A.at_t_id', 'left');
                    $builder->where('A.at_u_id', $at_u_id);
                    if ($at_start_sdate) $builder->where('A.at_date >=', $at_start_sdate);
                    if ($at_date) $builder->where('A.at_date <=', $at_date);
                    $builder->orderBy('A.at_date', 'ASC');
                    $builder->orderBy('A.at_start', 'ASC');

                    $totalRecords = $builder->countAllResults(false);
                    if ($limit > 0 && $limit != -1) {
                        $builder->limit($limit, $offset);
                    }
                    $records = $builder->get()->getResultArray();

                    // Validate date for edit permission
                    $in_date_check = new \DateTime($at_date ?: date('Y-m-d'));
                    $startdate = new \DateTime("today");
                    $startdate->modify('-' . $at_days_back . ' day');
                    $canEdit = ($in_date_check >= $startdate);

                    $data = [];
                    foreach ($records as $rec) {
                        $row = [];
                        $row[] = $rec['p_name'] ?? 'Leave';
                        $row[] = $rec['t_title'] ?? 'Leave';
                        $row[] = convert_db2display($rec['at_date']);
                        $row[] = RevTime($rec['at_start']) . ' - ' . RevTime($rec['at_end']);
                        $row[] = $rec['at_comment'];
                        if ($canEdit) {
                            $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $rec['at_id'] . '\')"><i class="fa fa-edit"></i></a>&nbsp; ';
                            $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $rec['at_id'] . '\')"><i class="fa fa-trash"></i></a>&nbsp; ';
                            $row[] = $anchors;
                        } else {
                            $row[] = '';
                        }
                        $data[] = $row;
                    }

                    echo json_encode([
                        'draw' => intval($draw),
                        'recordsTotal' => intval($totalRecords),
                        'recordsFiltered' => intval($totalRecords),
                        'data' => $data
                    ]);
                }
                break;
        }
        exit;
    }

    private function updateTaskHours($db, $task_id)
    {
        if (empty($task_id)) return;
        $tasks_ids = [$task_id];
        $task = $db->table('aa_tasks')->select('t_parent')->where('t_id', $task_id)->get()->getRowArray();
        if ($task) {
            if ($task['t_parent'] == 0) {
                $sub_tasks = $db->table('aa_tasks')->select('t_id')->where('t_parent', $task_id)->get()->getResultArray();
                foreach ($sub_tasks as $val) {
                    $tasks_ids[] = $val['t_id'];
                }
            }
        }
        $total = 0;
        if (!empty($tasks_ids[0])) {
            $result = $db->query("SELECT SUM(whours) as work_hours FROM (SELECT ((at_end - at_start) / 60) as whours FROM aa_attendance WHERE at_t_id IN (" . implode(",", $tasks_ids) . ")) AS DB2")->getRowArray();
            $total = $result['work_hours'] ?? 0;
        }
        $db->table('aa_tasks')->where('t_id', $task_id)->update(['t_hours_total' => $total]);
        if ($task && $task['t_parent'] > 0) {
            $this->updateTaskHours($db, $task['t_parent']);
        }
    }

    public function empattendance()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        echo json_encode([
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ]);
        exit;
    }

    public function dependency()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        echo json_encode([
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ]);
        exit;
    }

    public function weeklywork()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();
        $act = $request->getPost('act');
        $u_id = $admin_session['u_id'] ?? '';
        $u_type = $admin_session['u_type'] ?? '';

        switch ($act) {
            case 'list':
                $draw = $request->getPost('draw') ?? 1;
                $start = $request->getPost('start') ?? 0;
                $length = $request->getPost('length') ?? 25;
                $from_date = $request->getPost('from_date') ?? '';
                $to_date = $request->getPost('to_date') ?? '';
                $project_id = $request->getPost('project_id') ?? '';
                $filter_status = $request->getPost('filter_status') ?? 'All';

                $builder = $db->table('aa_weekly_work W');
                $builder->select('W.*, P.p_name, P.p_number, U.u_name as leader_name');
                $builder->join('aa_projects P', 'W.p_id = P.p_id', 'left');
                $builder->join('aa_users U', 'W.leader_id = U.u_id', 'left');

                // Filter by user role
                if (!in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head'])) {
                    $builder->where('W.leader_id', $u_id);
                }

                if (!empty($project_id)) {
                    $builder->where('W.p_id', $project_id);
                }
                if (!empty($from_date)) {
                    $builder->where('W.week_from >=', $from_date);
                }
                if (!empty($to_date)) {
                    $builder->where('W.week_to <=', $to_date);
                }
                if (!empty($filter_status) && $filter_status !== 'All') {
                    $builder->where('W.status', $filter_status);
                }

                $builder->orderBy('W.w_id', 'DESC');

                $totalRecords = $builder->countAllResults(false);
                if ($length != -1) {
                    $builder->limit($length, $start);
                }
                $records = $builder->get()->getResultArray();

                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    // Week
                    $wf = (!empty($rec['week_from']) && $rec['week_from'] !== '0000-00-00') ? $rec['week_from'] : '';
                    $wt = (!empty($rec['week_to']) && $rec['week_to'] !== '0000-00-00') ? $rec['week_to'] : '';
                    $row[] = ($wf && $wt) ? $wf . ' to ' . $wt : ($wf ?: $wt);
                    // Project
                    $row[] = $rec['p_name'] ?? '';
                    // Task
                    $row[] = $rec['task_name'] ?? '';
                    // Submission Date
                    $sd = $rec['submission_date'] ?? '';
                    $row[] = (!empty($sd) && $sd !== '0000-00-00') ? $sd : '';
                    // No. of Persons
                    $row[] = $rec['no_of_persons'] ?? 0;
                    // Assigned Employees
                    $empNames = [];
                    $assignedUsers = $db->table('aa_weekly_work_users WU')
                        ->select('U.u_name')
                        ->join('aa_users U', 'WU.u_id = U.u_id', 'left')
                        ->where('WU.weekly_work_id', $rec['w_id'])
                        ->get()->getResultArray();
                    foreach ($assignedUsers as $au) {
                        $empNames[] = $au['u_name'];
                    }
                    $row[] = implode(', ', $empNames);
                    // Status
                    $statusClass = 'label-default';
                    if ($rec['status'] == 'WIP') $statusClass = 'label-warning';
                    elseif ($rec['status'] == 'COMPLETED') $statusClass = 'label-success';
                    elseif ($rec['status'] == 'HOLD') $statusClass = 'label-danger';
                    elseif ($rec['status'] == 'PAUSE') $statusClass = 'label-info';
                    $row[] = '<span class="label ' . $statusClass . '">' . ($rec['status'] ?? '') . '</span>';
                    // Created By (for Bim Head / Master Admin)
                    if (in_array($u_type, ['Bim Head', 'Master Admin'])) {
                        $row[] = $rec['leader_name'] ?? '';
                    }
                    // Action
                    $row[] = '<a href="javascript://" onclick="editWeeklyWork(' . $rec['w_id'] . ')" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a> '
                           . '<a href="javascript://" onclick="deleteWeeklyWork(' . $rec['w_id'] . ')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>';
                    $data[] = $row;
                }

                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data
                ]);
                break;

            case 'add':
                // dependency_text[] and dep_leader[] come as arrays from the dynamic row form
                // Flatten them to strings for the summary columns on aa_weekly_work
                $depTextPost = $request->getPost('dependency_text');
                $depTextSummary = is_array($depTextPost) ? implode('; ', array_filter((array)$depTextPost)) : ($depTextPost ?? '');

                $depLeaderPost = $request->getPost('dep_leader');
                if (is_array($depLeaderPost)) {
                    $flatIds = [];
                    array_walk_recursive($depLeaderPost, function($id) use (&$flatIds) { if ($id !== '') $flatIds[] = $id; });
                    $depLeaderIds = implode(',', array_unique($flatIds));
                } else {
                    $depLeaderIds = $depLeaderPost ?? '';
                }

                $weeklyData = [
                    'p_id' => $request->getPost('p_id'),
                    'leader_id' => $u_id,
                    'week_from' => $request->getPost('week_from'),
                    'week_to' => $request->getPost('week_to'),
                    'task_name' => $request->getPost('task_name'),
                    'submission_date' => $request->getPost('submission_date') ?: null,
                    'no_of_persons' => $request->getPost('no_of_persons') ?? 0,
                    'status' => $request->getPost('status') ?? 'WIP',
                    'dependency_type' => $request->getPost('dependency_type') ?? '',
                    'dependency_text' => $depTextSummary,
                    'dep_leader_ids' => $depLeaderIds,
                    'dependency_date' => $request->getPost('dependency_date') ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $db->table('aa_weekly_work')->insert($weeklyData);
                $w_id = $db->insertID();

                // Save assigned employees
                $employee_ids = $request->getPost('employee_ids') ?? [];
                if (!empty($employee_ids)) {
                    $weeklyData['no_of_persons'] = count($employee_ids);
                    $db->table('aa_weekly_work')->where('w_id', $w_id)->update(['no_of_persons' => count($employee_ids)]);
                    foreach ($employee_ids as $emp_id) {
                        $db->table('aa_weekly_work_users')->insert([
                            'weekly_work_id' => $w_id,
                            'u_id' => $emp_id,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // Save dependencies
                $dep_texts = $request->getPost('dependency_text[]') ?? $request->getPost('dep_text') ?? [];
                $dep_types = $request->getPost('dep_type') ?? [];
                $dep_priorities = $request->getPost('dep_priority') ?? [];
                $dep_statuses = $request->getPost('dep_status') ?? [];
                $dep_target_dates = $request->getPost('dep_target_date') ?? [];
                $dep_leaders = $request->getPost('dep_leader') ?? [];

                if (is_array($dep_texts)) {
                    foreach ($dep_texts as $i => $depText) {
                        if (empty(trim($depText ?? ''))) continue;
                        $db->table('aa_weekly_work_dependency')->insert([
                            'w_id' => $w_id,
                            'dependency_text' => $depText,
                            'dependency_type' => $dep_types[$i] ?? '',
                            'priority' => $dep_priorities[$i] ?? '',
                            'status' => $dep_statuses[$i] ?? 'Pending',
                            'target_date' => !empty($dep_target_dates[$i]) ? $dep_target_dates[$i] : null,
                            'dep_leader_ids' => $dep_leaders[$i] ?? '',
                            'created_date' => date('Y-m-d'),
                            'created_by' => $u_id,
                        ]);
                    }
                }

                // Desktop notifications for Internal dependencies
                if (is_array($dep_texts) && !empty(array_filter($dep_texts))) {
                    $allLeaderIds = [];
                    foreach ($dep_leaders as $dl) {
                        if (!empty($dl)) {
                            $ids = is_array($dl) ? $dl : explode(',', $dl);
                            $allLeaderIds = array_merge($allLeaderIds, $ids);
                        }
                    }
                    $allLeaderIds = array_unique(array_filter($allLeaderIds));

                    if (!empty($allLeaderIds)) {
                        $depUserNames = $db->table('aa_users')->select('u_name')->whereIn('u_id', $allLeaderIds)->get()->getResultArray();
                        $depUserList = implode(', ', array_column($depUserNames, 'u_name'));

                        $bimHeadIds = array_column($db->table('aa_users')->select('u_id')->where('u_type', 'Bim Head')->get()->getResultArray(), 'u_id');
                        $notifyIds = array_unique(array_merge($allLeaderIds, $bimHeadIds));

                        $title = "New Dependency Created by " . $admin_session['u_name'];
                        $messageLoad = "A new dependency has been created by " . $admin_session['u_name'] . " for the following users: " . $depUserList;
                        $payload = json_encode(['screen_name' => 'Dependency', 'action' => $title, 'id' => $w_id]);

                        foreach ($notifyIds as $nuid) {
                            $db->table('aa_desktop_notification_queue')->insert([
                                'u_id' => $nuid, 'title' => $title, 'message' => $messageLoad,
                                'payload' => $payload, 'is_sent' => 0,
                            ]);
                        }
                    }
                }

                echo json_encode(['status' => 'pass', 'message' => 'Weekly work added successfully.']);
                break;

            case 'edit':
                $w_id = $request->getPost('w_id');
                $work = $db->table('aa_weekly_work')->where('w_id', $w_id)->get()->getRowArray();
                if (!$work) {
                    echo json_encode(['status' => 'fail', 'message' => 'Record not found.']);
                    break;
                }

                // Get assigned employees
                $assigned = $db->table('aa_weekly_work_users')
                    ->select('u_id')
                    ->where('weekly_work_id', $w_id)
                    ->get()->getResultArray();
                $assigned_employees = array_column($assigned, 'u_id');

                // Get dependencies
                $dependencies = $db->table('aa_weekly_work_dependency')
                    ->where('w_id', $w_id)
                    ->get()->getResultArray();

                echo json_encode([
                    'status' => 'pass',
                    'data' => $work,
                    'assigned_employees' => $assigned_employees,
                    'dependencies' => $dependencies
                ]);
                break;

            case 'update':
                $w_id = $request->getPost('w_id');
                if (!$w_id) {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid record.']);
                    break;
                }

                $updateData = [
                    'p_id' => $request->getPost('p_id'),
                    'week_from' => $request->getPost('week_from'),
                    'week_to' => $request->getPost('week_to'),
                    'task_name' => $request->getPost('task_name'),
                    'submission_date' => $request->getPost('submission_date') ?: null,
                    'no_of_persons' => $request->getPost('no_of_persons') ?? 0,
                    'status' => $request->getPost('status') ?? 'WIP',
                    'dependency_type' => $request->getPost('dependency_type') ?? '',
                    'dependency_text' => $request->getPost('dependency_text_main') ?? '',
                    'dep_leader_ids' => is_array($request->getPost('dep_leader_main')) ? implode(',', $request->getPost('dep_leader_main')) : ($request->getPost('dep_leader_main') ?? ''),
                    'dependency_date' => $request->getPost('dependency_date') ?: null,
                ];
                $db->table('aa_weekly_work')->where('w_id', $w_id)->update($updateData);

                // Update assigned employees
                $db->table('aa_weekly_work_users')->where('weekly_work_id', $w_id)->delete();
                $employee_ids = $request->getPost('employee_ids') ?? [];
                if (!empty($employee_ids)) {
                    $db->table('aa_weekly_work')->where('w_id', $w_id)->update(['no_of_persons' => count($employee_ids)]);
                    foreach ($employee_ids as $emp_id) {
                        $db->table('aa_weekly_work_users')->insert([
                            'weekly_work_id' => $w_id,
                            'u_id' => $emp_id,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // Update dependencies
                $dep_ids = $request->getPost('dep_id') ?? [];
                $dep_texts = $request->getPost('dependency_text[]') ?? $request->getPost('dep_text') ?? [];
                $dep_types = $request->getPost('dep_type') ?? [];
                $dep_priorities = $request->getPost('dep_priority') ?? [];
                $dep_statuses = $request->getPost('dep_status') ?? [];
                $dep_target_dates = $request->getPost('dep_target_date') ?? [];
                $dep_leaders = $request->getPost('dep_leader') ?? [];

                // Remove old dependencies not in the update
                $keep_ids = array_filter($dep_ids);
                if (!empty($keep_ids)) {
                    $db->table('aa_weekly_work_dependency')
                        ->where('w_id', $w_id)
                        ->whereNotIn('wd_id', $keep_ids)
                        ->delete();
                } else {
                    $db->table('aa_weekly_work_dependency')->where('w_id', $w_id)->delete();
                }

                if (is_array($dep_texts)) {
                    foreach ($dep_texts as $i => $depText) {
                        if (empty(trim($depText ?? ''))) continue;
                        $depData = [
                            'w_id' => $w_id,
                            'dependency_text' => $depText,
                            'dependency_type' => $dep_types[$i] ?? '',
                            'priority' => $dep_priorities[$i] ?? '',
                            'status' => $dep_statuses[$i] ?? 'Pending',
                            'target_date' => !empty($dep_target_dates[$i]) ? $dep_target_dates[$i] : null,
                            'dep_leader_ids' => $dep_leaders[$i] ?? '',
                        ];
                        if (!empty($dep_ids[$i])) {
                            $db->table('aa_weekly_work_dependency')->where('wd_id', $dep_ids[$i])->update($depData);
                        } else {
                            $depData['created_date'] = date('Y-m-d');
                            $depData['created_by'] = $u_id;
                            $db->table('aa_weekly_work_dependency')->insert($depData);
                        }
                    }
                }

                // Desktop notifications for dependency update
                if (is_array($dep_texts) && !empty(array_filter($dep_texts))) {
                    $allLeaderIds = [];
                    foreach ($dep_leaders as $dl) {
                        if (!empty($dl)) {
                            $ids = is_array($dl) ? $dl : explode(',', $dl);
                            $allLeaderIds = array_merge($allLeaderIds, $ids);
                        }
                    }
                    $allLeaderIds = array_unique(array_filter($allLeaderIds));

                    if (!empty($allLeaderIds)) {
                        $depUserNames = $db->table('aa_users')->select('u_name')->whereIn('u_id', $allLeaderIds)->get()->getResultArray();
                        $depUserList = implode(', ', array_column($depUserNames, 'u_name'));

                        $project = $db->table('aa_projects')->select('p_name')->where('p_id', $request->getPost('p_id'))->get()->getRowArray();
                        $projectName = $project ? $project['p_name'] : 'Unknown Project';

                        $bimHeadIds = array_column($db->table('aa_users')->select('u_id')->where('u_type', 'Bim Head')->get()->getResultArray(), 'u_id');
                        $notifyIds = array_unique(array_merge($allLeaderIds, $bimHeadIds));

                        $title = "Dependency Updated by " . $admin_session['u_name'];
                        $messageLoad = "The dependency in project " . $projectName . " has been updated by " . $admin_session['u_name'] . " for: " . $depUserList . ".";
                        $payload = json_encode(['screen_name' => 'Dependency', 'action' => $title, 'id' => $w_id]);

                        foreach ($notifyIds as $nuid) {
                            $db->table('aa_desktop_notification_queue')->insert([
                                'u_id' => $nuid, 'title' => $title, 'message' => $messageLoad,
                                'payload' => $payload, 'is_sent' => 0,
                            ]);
                        }
                    }
                }

                echo json_encode(['status' => 'pass', 'message' => 'Weekly work updated successfully.']);
                break;

            case 'delete':
                $w_id = $request->getPost('w_id');
                if ($w_id) {
                    $db->table('aa_weekly_work_users')->where('weekly_work_id', $w_id)->delete();
                    $db->table('aa_weekly_work_dependency')->where('w_id', $w_id)->delete();
                    $db->table('aa_weekly_work')->where('w_id', $w_id)->delete();
                    echo json_encode(['status' => 'pass', 'message' => 'Weekly work deleted successfully.']);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid record.']);
                }
                break;

            case 'dependencies':
                $w_id = $request->getPost('w_id');
                $type = $request->getPost('type') ?? 'all';

                $builder = $db->table('aa_weekly_work_dependency WD');
                $builder->select('WD.*, W.week_from, W.week_to, P.p_name as project_name, CU.u_name as created_by, AU.u_name as assigned_to');
                $builder->join('aa_weekly_work W', 'WD.w_id = W.w_id', 'left');
                $builder->join('aa_projects P', 'W.p_id = P.p_id', 'left');
                $builder->join('aa_users CU', 'W.leader_id = CU.u_id', 'left');
                $builder->join('aa_users AU', 'WD.dep_leader_ids = AU.u_id', 'left');
                $builder->where('WD.w_id', $w_id);

                if ($type === 'incomplete') {
                    $builder->where('WD.status !=', 'Completed');
                }

                $deps = $builder->get()->getResultArray();
                echo json_encode(['status' => 'pass', 'data' => $deps]);
                break;

            case 'dependencies_list':
                $project_id = $request->getPost('project_id') ?? '';
                $status = $request->getPost('status') ?? '';
                $type = $request->getPost('type') ?? '';
                $priority = $request->getPost('priority') ?? '';
                $leader = $request->getPost('leader') ?? '';
                $createdby = $request->getPost('createdby') ?? '';
                $assigned_to = $request->getPost('assigned_to') ?? '';
                $from_date = $request->getPost('from_date') ?? '';
                $to_date = $request->getPost('to_date') ?? '';

                $builder = $db->table('aa_weekly_work_dependency WD');
                $builder->select('WD.*, W.p_id, W.leader_id, P.p_name, P.p_number, CU.u_name as created_by_name');
                $builder->join('aa_weekly_work W', 'WD.w_id = W.w_id', 'left');
                $builder->join('aa_projects P', 'W.p_id = P.p_id', 'left');
                $builder->join('aa_users CU', 'WD.created_by = CU.u_id', 'left');

                // Project Leader filter: only show dependencies from their assigned projects
                if ($u_type === 'Project Leader') {
                    $builder->like('P.p_leader', $u_id);

                    // Sub-filter for Project Leader
                    if ($createdby === 'own') {
                        $builder->where('WD.created_by', $u_id);
                    } elseif ($createdby === 'assigned') {
                        $builder->where('WD.created_by !=', $u_id);
                        $builder->like('WD.dep_leader_ids', $u_id);
                    } elseif ($createdby === 'myall' || empty($createdby)) {
                        $builder->groupStart()
                            ->where('WD.created_by', $u_id)
                            ->orLike('WD.dep_leader_ids', $u_id)
                        ->groupEnd();
                    }
                    // 'all' = no additional filter within assigned projects
                } elseif ($u_type === 'TaskCoordinator') {
                    // TaskCoordinator sub-filter
                    if ($createdby === 'own') {
                        $builder->where('WD.created_by', $u_id);
                    } elseif ($createdby === 'assigned') {
                        $builder->where('WD.created_by !=', $u_id);
                        $builder->like('WD.dep_leader_ids', $u_id);
                    } elseif ($createdby === 'myall') {
                        $builder->groupStart()
                            ->where('WD.created_by', $u_id)
                            ->orLike('WD.dep_leader_ids', $u_id)
                        ->groupEnd();
                    }
                    // 'all' or empty = no filter, see everything
                } elseif (!in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head'])) {
                    // Other roles: only their own or assigned to them
                    $builder->groupStart()
                        ->where('WD.created_by', $u_id)
                        ->orLike('WD.dep_leader_ids', $u_id)
                    ->groupEnd();
                }

                if (!empty($project_id)) {
                    $builder->where('W.p_id', $project_id);
                }
                if (!empty($status) && $status !== 'All') {
                    $builder->where('WD.status', $status);
                }
                if (!empty($type)) {
                    $builder->where('WD.dependency_type', $type);
                }
                if (!empty($priority)) {
                    $builder->where('WD.priority', $priority);
                }
                if (!empty($leader)) {
                    $builder->groupStart()
                        ->where('WD.created_by', $leader)
                        ->orLike('WD.dep_leader_ids', $leader)
                    ->groupEnd();
                }
                if (!empty($from_date)) {
                    $builder->where('WD.created_date >=', $from_date);
                }
                if (!empty($to_date)) {
                    $builder->where('WD.created_date <=', $to_date . ' 23:59:59');
                }

                $builder->orderBy('WD.wd_id', 'DESC');
                $deps = $builder->get()->getResultArray();

                $data = [];
                $sr = 1;
                foreach ($deps as $dep) {
                    // Get assigned leader names
                    $assignedNames = '';
                    if (!empty($dep['dep_leader_ids'])) {
                        $leaderIds = array_filter(explode(',', $dep['dep_leader_ids']));
                        if (!empty($leaderIds)) {
                            $leaders = $db->table('aa_users')
                                ->select('u_name')
                                ->whereIn('u_id', $leaderIds)
                                ->get()->getResultArray();
                            $assignedNames = implode(', ', array_column($leaders, 'u_name'));
                        }
                    }

                    $createdDate = $dep['created_date'] ?? '';
                    if ($createdDate === '0000-00-00' || $createdDate === '0000-00-00 00:00:00') $createdDate = '';
                    $targetDate = $dep['target_date'] ?? '';
                    if ($targetDate === '0000-00-00') $targetDate = '';
                    $completedAssignDate = $dep['completed_assign_date'] ?? '';
                    if ($completedAssignDate === '0000-00-00 00:00:00') $completedAssignDate = '';

                    $data[] = [
                        '#' => $sr++,
                        'wd_id' => $dep['wd_id'],
                        'created_date' => $createdDate,
                        'project_name' => $dep['p_name'] ?? '',
                        'dependency_text' => $dep['dependency_text'] ?? '',
                        'created_by' => $dep['created_by_name'] ?? '',
                        'created_by_id' => $dep['created_by'] ?? '',
                        'assigned_to' => $assignedNames,
                        'dep_leader_ids' => $dep['dep_leader_ids'] ?? '',
                        'dependency_type' => $dep['dependency_type'] ?? '',
                        'priority' => $dep['priority'] ?? '',
                        'status' => $dep['status'] ?? '',
                        'target_date' => $targetDate,
                        'completed_by_assigned' => $dep['completed_by_assigned'] ?? 0,
                        'completed_assign_date' => $completedAssignDate,
                        'completed_day_diff' => $dep['completed_day_diff'] ?? null,
                        'completed_assign_status' => $dep['completed_assign_status'] ?? '',
                    ];
                }

                echo json_encode(['data' => $data]);
                break;

            case 'complete_dependency':
                $wd_id = $request->getPost('wd_id');
                if ($wd_id) {
                    $db->table('aa_weekly_work_dependency')->where('wd_id', $wd_id)->update([
                        'status' => 'Completed',
                        'completed_date' => date('Y-m-d'),
                    ]);
                    echo json_encode(['status' => 'success', 'message' => 'Dependency completed.']);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid dependency.']);
                }
                break;

            case 'assigned_complete_dependency':
                $wd_id = $request->getPost('wd_id');
                if ($wd_id) {
                    $dep = $db->table('aa_weekly_work_dependency')->where('wd_id', $wd_id)->get()->getRowArray();
                    if (!$dep) {
                        echo json_encode(['status' => 'fail', 'message' => 'Dependency not found.']);
                        break;
                    }

                    $targetDate = $dep['target_date'] ?? null;
                    $today = date('Y-m-d');
                    $dayDiff = null;
                    $assignStatus = '';
                    if (!empty($targetDate) && $targetDate !== '0000-00-00') {
                        $diff = (strtotime($today) - strtotime($targetDate)) / 86400;
                        $dayDiff = (int)$diff;
                        if ($dayDiff < 0) $assignStatus = 'Early';
                        elseif ($dayDiff == 0) $assignStatus = 'On Time';
                        else $assignStatus = 'Delayed';
                    }

                    $db->table('aa_weekly_work_dependency')->where('wd_id', $wd_id)->update([
                        'completed_by_assigned' => $u_id,
                        'completed_assign_date' => date('Y-m-d H:i:s'),
                        'completed_day_diff' => $dayDiff,
                        'completed_assign_status' => $assignStatus,
                    ]);

                    // Notify the creator
                    $work = $db->table('aa_weekly_work W')
                        ->select('W.leader_id, P.p_name')
                        ->join('aa_projects P', 'W.p_id = P.p_id', 'left')
                        ->where('W.w_id', $dep['w_id'])
                        ->get()->getRowArray();

                    if ($work) {
                        $title = "Dependency Completed by " . $admin_session['u_name'];
                        $msg = "Dependency in project " . ($work['p_name'] ?? '') . " has been marked complete by " . $admin_session['u_name'];
                        $db->table('aa_desktop_notification_queue')->insert([
                            'u_id' => $work['leader_id'],
                            'title' => $title,
                            'message' => $msg,
                            'payload' => json_encode(['screen_name' => 'Dependencies']),
                            'is_sent' => 0,
                        ]);
                    }

                    echo json_encode(['status' => 'success', 'message' => 'Dependency marked as complete.']);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid dependency.']);
                }
                break;

            case 'get_dependency':
                $wd_id = $request->getPost('wd_id');
                if ($wd_id) {
                    $dep = $db->table('aa_weekly_work_dependency')->where('wd_id', $wd_id)->get()->getRowArray();
                    if ($dep) {
                        echo json_encode(['status' => 'success', 'data' => $dep]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Dependency not found.']);
                    }
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid dependency.']);
                }
                break;

            case 'update_dependency':
                $wd_id = $request->getPost('wd_id');
                if ($wd_id) {
                    $depLeaderIds = $request->getPost('dep_leader_ids');
                    $updateData = [
                        'dependency_text' => $request->getPost('dependency_text') ?? '',
                        'dependency_type' => $request->getPost('dependency_type') ?? '',
                        'priority' => $request->getPost('priority') ?? '',
                        'status' => $request->getPost('status') ?? '',
                        'target_date' => !empty($request->getPost('dep_target_date')) ? $request->getPost('dep_target_date') : null,
                        'dep_leader_ids' => is_array($depLeaderIds) ? implode(',', $depLeaderIds) : ($depLeaderIds ?? ''),
                    ];
                    if ($updateData['status'] === 'Completed') {
                        $updateData['completed_date'] = date('Y-m-d');
                    }
                    $db->table('aa_weekly_work_dependency')->where('wd_id', $wd_id)->update($updateData);
                    echo json_encode(['status' => 'success', 'message' => 'Dependency updated successfully.']);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid dependency.']);
                }
                break;

            case 'reverse_dependency':
                $wd_id = $request->getPost('wd_id');
                if ($wd_id) {
                    $db->table('aa_weekly_work_dependency')->where('wd_id', $wd_id)->update([
                        'completed_by_assigned' => null,
                        'completed_assign_date' => null,
                        'completed_day_diff' => null,
                        'completed_assign_status' => null,
                    ]);
                    echo json_encode(['status' => 'success', 'message' => 'Dependency reversed successfully.']);
                } else {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid dependency.']);
                }
                break;

            default:
                echo json_encode(['status' => 'pass', 'data' => []]);
                break;
        }
        exit;
    }

    public function project_contacts()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $db = \Config\Database::connect();
        $act = $request->getPost('act');

        switch ($act) {
            case 'list':
                $pc_p_id = $request->getPost('pc_p_id');
                $pc_id = $request->getPost('pc_id');

                if ($pc_id > 0) {
                    $contact = $db->table('aa_project_contacts')
                        ->where('pc_id', $pc_id)
                        ->get()->getRowArray();
                    echo json_encode(['status' => 'pass', 'data' => $contact ?? []]);
                    break;
                }

                $draw = $request->getPost('draw') ?? 1;
                $builder = $db->table('aa_project_contacts PC');
                $builder->select('PC.*');

                if (!empty($pc_p_id)) {
                    $builder->where('PC.pc_p_id', $pc_p_id);
                }

                $builder->orderBy('PC.pc_id', 'DESC');
                $contacts = $builder->get()->getResultArray();

                $data = [];
                foreach ($contacts as $c) {
                    $row = [];
                    $row[] = $c['pc_name'] ?? '';
                    $row[] = $c['pc_designation'] ?? '';
                    $row[] = $c['pc_email'] ?? '';
                    $row[] = $c['pc_mobile'] ?? '';
                    $row[] = '<a href="javascript://" onclick="showAddEditForm(' . $c['pc_id'] . ')" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a> '
                           . '<a href="javascript://" onclick="deleteRecord(' . $c['pc_id'] . ')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>';
                    $data[] = $row;
                }

                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => count($contacts),
                    'recordsFiltered' => count($contacts),
                    'data' => $data
                ]);
                break;

            case 'add':
                $data = [
                    'pc_p_id' => $request->getPost('pc_p_id'),
                    'pc_name' => $request->getPost('pc_name'),
                    'pc_email' => $request->getPost('pc_email'),
                    'pc_mobile' => $request->getPost('pc_mobile'),
                    'pc_designation' => $request->getPost('pc_designation'),
                ];
                $id = $request->getPost('pc_id');
                if ($id > 0) {
                    $db->table('aa_project_contacts')->where('pc_id', $id)->update($data);
                } else {
                    $db->table('aa_project_contacts')->insert($data);
                }
                echo json_encode(['status' => 'pass', 'message' => 'Contact saved.']);
                break;

            case 'del':
                $id = $request->getPost('pc_id');
                $db->table('aa_project_contacts')->where('pc_id', $id)->delete();
                echo json_encode(['status' => 'pass', 'message' => 'Contact deleted.']);
                break;

            default:
                echo json_encode(['status' => 'pass', 'data' => []]);
                break;
        }
        exit;
    }

    public function reports()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();

        $type = $request->getPost('type') ?? '';
        $draw = $request->getPost('draw') ?? 1;
        $rpt_start = convert_display2db($request->getPost('rpt_start') ?? '');
        $rpt_end = convert_display2db($request->getPost('rpt_end') ?? '');
        $txt_search = $request->getPost('txt_search') ?? '';

        switch ($type) {
            case 'attendence':
                $sub_type = $request->getPost('sub_type');
                if ($sub_type == 'daily') $rpt_end = $rpt_start;
                $sql = "SELECT u_name, u_id, SUM(whours) as work_hours FROM (SELECT u_name, u_id, ((at_end - at_start) / 60) as whours FROM aa_users U INNER JOIN aa_attendance ATT ON U.u_id = ATT.at_u_id AND ATT.at_date BETWEEN '{$rpt_start}' AND '{$rpt_end}'";
                if ($txt_search) $sql .= " AND U.u_name LIKE '%{$txt_search}%'";
                $sql .= ") AS DB2 GROUP BY u_id ORDER BY u_id ASC";
                $records = $db->query($sql)->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $rec['u_name'];
                    $row[] = number_format($this->convertHours($rec['work_hours']), 2);
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="postFormData(\'' . $rec['u_id'] . '\', \'' . $rec['u_name'] . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'Leaderempattendance':
                $leader_id = $request->getPost('leader_id');
                $sql = "SELECT u_name, u_id, SUM(whours) as work_hours FROM (SELECT U.u_name, U.u_id, ((ATT.at_end - ATT.at_start) / 60) AS whours FROM aa_users U INNER JOIN aa_attendance ATT ON U.u_id = ATT.at_u_id WHERE ATT.at_date BETWEEN '{$rpt_start}' AND '{$rpt_end}' AND U.u_leader = " . intval($leader_id);
                if ($txt_search) $sql .= " AND U.u_name LIKE '%{$txt_search}%'";
                $sql .= ") AS DB2 GROUP BY u_id ORDER BY u_name ASC";
                $records = $db->query($sql)->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $rec['u_name'];
                    $row[] = number_format($this->convertHours($rec['work_hours']), 2);
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="postFormData(\'' . $rec['u_id'] . '\', \'' . $rec['u_name'] . '\', \'' . $leader_id . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'timesheet':
                $u_id = $request->getPost('u_id');
                $sub_type = $request->getPost('sub_type');
                if ($sub_type == 'daily') $rpt_end = $rpt_start;
                $records = $db->table('aa_attendance A')
                    ->select('A.*, P.p_name, T.t_title, U.u_name')
                    ->join('aa_projects P', 'P.p_id = A.at_p_id', 'left')
                    ->join('aa_tasks T', 'T.t_id = A.at_t_id', 'left')
                    ->join('aa_users U', 'U.u_id = A.at_u_id', 'left')
                    ->where('A.at_u_id', $u_id)
                    ->where('A.at_date >=', $rpt_start)
                    ->where('A.at_date <=', $rpt_end)
                    ->orderBy('A.at_date', 'ASC')
                    ->orderBy('A.at_start', 'ASC')
                    ->get()->getResultArray();
                $data = [];
                $whours = 0;
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $rec['u_name'] ?? '';
                    $row[] = $rec['p_name'] ?? 'Leave';
                    $row[] = $rec['t_title'] ?? 'Leave';
                    $row[] = convert_db2display($rec['at_date']);
                    $row[] = RevTime($rec['at_start']) . ' - ' . RevTime($rec['at_end']);
                    $whours += (($rec['at_end'] - $rec['at_start']) / 60);
                    $row[] = $rec['at_comment'];
                    $data[] = $row;
                }
                if ($whours > 0) {
                    $total_hrs = '<b>Total Hours Worked: ' . number_format($this->convertHours($whours), 2) . ' hr</b>';
                    $data[] = ['', '', '', '', $total_hrs, ''];
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($records), 'recordsFiltered' => count($records), 'data' => $data]);
                break;

            case 'leave':
                $records = $db->query("SELECT u_name, l_u_id, SUM(CASE WHEN l_is_halfday = 'Yes' THEN 0.5 ELSE DATEDIFF(l_to_date, l_from_date) + 1 END) as final_leave FROM aa_leaves L INNER JOIN aa_users U ON U.u_id = L.l_u_id WHERE l_from_date >= '{$rpt_start}' AND l_to_date <= '{$rpt_end}' AND l_is_hourly = 'No'" . ($txt_search ? " AND u_name LIKE '%{$txt_search}%'" : "") . " GROUP BY l_u_id ORDER BY U.u_id ASC")->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $approved = $db->query("SELECT COALESCE(SUM(CASE WHEN l_is_halfday = 'Yes' THEN 0.5 ELSE DATEDIFF(l_to_date, l_from_date) + 1 END), 0) as approved_leave FROM aa_leaves WHERE l_u_id = '{$rec['l_u_id']}' AND l_from_date >= '{$rpt_start}' AND l_to_date <= '{$rpt_end}' AND l_status = 'Approved' AND l_is_hourly = 'No'")->getRowArray();
                    $declined = $db->query("SELECT COALESCE(SUM(CASE WHEN l_is_halfday = 'Yes' THEN 0.5 ELSE DATEDIFF(l_to_date, l_from_date) + 1 END), 0) as declined_leave FROM aa_leaves WHERE l_u_id = '{$rec['l_u_id']}' AND l_from_date >= '{$rpt_start}' AND l_to_date <= '{$rpt_end}' AND l_status = 'Declined' AND l_is_hourly = 'No'")->getRowArray();
                    $row = [];
                    $row[] = $rec['u_name'];
                    $row[] = (fmod($rec['final_leave'], 1) !== 0.0) ? $rec['final_leave'] : (int)$rec['final_leave'];
                    $row[] = (fmod($approved['approved_leave'], 1) !== 0.0) ? $approved['approved_leave'] : (int)$approved['approved_leave'];
                    $row[] = (fmod($declined['declined_leave'], 1) !== 0.0) ? $declined['declined_leave'] : (int)$declined['declined_leave'];
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['l_u_id'] . '\', \'' . $rec['u_name'] . '\', \'' . $rpt_start . '\', \'' . $rpt_end . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'leave_date':
                $records = $db->table('aa_leaves L')
                    ->select('L.*, U.u_name')
                    ->join('aa_users U', 'U.u_id = L.l_u_id')
                    ->where('L.l_is_hourly', 'No')
                    ->where('L.l_from_date <=', $rpt_start)
                    ->where('L.l_to_date >=', $rpt_start)
                    ->orderBy('U.u_id', 'ASC')
                    ->get()->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $rec['u_name'];
                    $row[] = date("d-m-Y", strtotime($rec['l_create_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_from_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_to_date']));
                    $diff = strtotime($rec['l_to_date']) - strtotime($rec['l_from_date']);
                    $interval = ($rec['l_is_halfday'] == 'Yes') ? abs(round($diff / 86400)) + 0.5 : abs(round($diff / 86400)) + 1;
                    $row[] = $interval;
                    $row[] = $rec['l_status'];
                    $half_time = ($rec['l_is_halfday'] == 'Yes') ? ' - ' . $rec['l_halfday_time'] . ' Half' : '';
                    $row[] = $rec['l_is_halfday'] . $half_time;
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['l_u_id'] . '\', \'' . $rec['u_name'] . '\', \'' . $rpt_start . '\', \'' . $rpt_start . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'leave_detail':
                $l_u_id = $request->getPost('l_u_id');
                $rpt_start = $request->getPost('rpt_start');
                $rpt_end = $request->getPost('rpt_end');
                $user = $db->table('aa_users')->select('u_name')->where('u_id', $l_u_id)->get()->getRowArray();
                $records = $db->table('aa_leaves L')
                    ->select('L.*')
                    ->where('L.l_u_id', $l_u_id)
                    ->where('L.l_is_hourly', 'No')
                    ->where('L.l_from_date >=', $rpt_start)
                    ->where('L.l_to_date <=', $rpt_end)
                    ->get()->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $user['u_name'] ?? '';
                    $row[] = date("d-m-Y", strtotime($rec['l_create_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_from_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_to_date']));
                    $diff = strtotime($rec['l_to_date']) - strtotime($rec['l_from_date']);
                    $interval = ($rec['l_is_halfday'] == 'Yes') ? abs(round($diff / 86400)) + 0.5 : abs(round($diff / 86400)) + 1;
                    $row[] = $interval;
                    $row[] = $rec['l_status'];
                    $half_time = ($rec['l_is_halfday'] == 'Yes') ? ' - ' . ($rec['l_halfday_time'] ?? '') . ' Half' : '';
                    $row[] = ($rec['l_is_halfday'] ?? 'No') . $half_time;
                    $row[] = $rec['l_message'] . ((!empty($rec['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $rec['l_reply'] : "");
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'leaves_total':
                $fromdate = $request->getPost('rpt_start');
                $todate = $request->getPost('rpt_end');
                $rs = convert_display2db($fromdate);
                $re = convert_display2db($todate);
                $total_leaves_setting = $db->table('aa_settings')->where('s_key', 'total_emp_leaves')->get()->getRowArray();
                $total_leaves = $total_leaves_setting ? $total_leaves_setting['s_value'] : 0;
                $where = $txt_search ? " AND u_name LIKE '%{$txt_search}%'" : "";
                $users = $db->query("SELECT * FROM aa_users WHERE ((u_status = 'Deactive' AND u_leave_date >= '{$rs}') OR (u_status = 'Active')){$where} ORDER BY u_id ASC")->getResultArray();
                $data = [];
                foreach ($users as $user) {
                    $uid = $user['u_id'];
                    // CI3-equivalent 8-union query: handles all 4 date-overlap cases for both halfday and full-day leaves
                    $approved = $db->query("
                        SELECT COALESCE(SUM(total_days), 0) as approved_leave FROM (
                            (SELECT l_id, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND l_from_date BETWEEN '{$rs}' AND '{$re}' AND l_to_date BETWEEN '{$rs}' AND '{$re}' AND l_is_halfday = 'No' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND '{$rs}' BETWEEN l_from_date AND l_to_date AND '{$re}' BETWEEN l_from_date AND l_to_date AND l_is_halfday = 'No' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND '{$rs}' BETWEEN l_from_date AND l_to_date AND l_to_date BETWEEN '{$rs}' AND '{$re}' AND l_is_halfday = 'No' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND l_from_date BETWEEN '{$rs}' AND '{$re}' AND '{$re}' BETWEEN l_from_date AND l_to_date AND l_is_halfday = 'No' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF(l_to_date, l_from_date) + 0.5 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND l_from_date BETWEEN '{$rs}' AND '{$re}' AND l_to_date BETWEEN '{$rs}' AND '{$re}' AND l_is_halfday = 'Yes' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF('{$re}', '{$rs}') + 0.5 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND '{$rs}' BETWEEN l_from_date AND l_to_date AND '{$re}' BETWEEN l_from_date AND l_to_date AND l_is_halfday = 'Yes' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF(l_to_date, '{$rs}') + 0.5 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND '{$rs}' BETWEEN l_from_date AND l_to_date AND l_to_date BETWEEN '{$rs}' AND '{$re}' AND l_is_halfday = 'Yes' AND l_is_hourly = 'No')
                            UNION
                            (SELECT l_id, DATEDIFF('{$re}', l_from_date) + 0.5 as total_days FROM aa_leaves WHERE l_u_id = '{$uid}' AND l_status = 'Approved' AND l_from_date BETWEEN '{$rs}' AND '{$re}' AND '{$re}' BETWEEN l_from_date AND l_to_date AND l_is_halfday = 'Yes' AND l_is_hourly = 'No')
                        ) AS FinalTb
                    ")->getRowArray();
                    // CI3-equivalent: convert HH.MM stored values to minutes, sum, convert back to HH.MM string
                    $approved_hourly = $db->query("
                        SELECT CONCAT(
                            FLOOR(COALESCE(SUM(FLOOR(l_hourly_time_hour) * 60 + ROUND((l_hourly_time_hour - FLOOR(l_hourly_time_hour)) * 100)), 0) / 60),
                            '.',
                            LPAD(MOD(COALESCE(SUM(FLOOR(l_hourly_time_hour) * 60 + ROUND((l_hourly_time_hour - FLOOR(l_hourly_time_hour)) * 100)), 0), 60), 2, '0')
                        ) as approved_leave
                        FROM aa_leaves
                        WHERE l_u_id = '{$uid}' AND l_from_date >= '{$rs}' AND l_to_date <= '{$re}'
                        AND l_status = 'Approved' AND l_is_hourly = 'Yes'
                    ")->getRowArray();
                    $hourly_str = $approved_hourly['approved_leave'] ?? '0.00';

                    $app = round((float)($approved['approved_leave'] ?? 0), 2);
                    $remaining = round($total_leaves - $app, 2);

                    $fmt = function($v) { return (fmod($v, 1) !== 0.0) ? number_format($v, 2) : (int)$v; };

                    $row = [];
                    $row[] = $user['u_name'];
                    $row[] = $fmt($app);
                    $row[] = $hourly_str;
                    $row[] = $fmt($remaining);
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $uid . '\', \'' . $user['u_name'] . '\', \'' . $rs . '\', \'' . $re . '\')"><i class="fa fa-eye"></i></a>';
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showDataHour(\'' . $uid . '\', \'' . $user['u_name'] . '\', \'' . $rs . '\', \'' . $re . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'hourly_leave':
                // Aggregated per-employee view (5 columns)
                $qb = $db->table('aa_leaves L')
                    ->select('U.u_id, U.u_name,
                        SUM(L.l_hourly_time_hour) AS total_hours,
                        SUM(CASE WHEN L.l_status = "Approved" THEN L.l_hourly_time_hour ELSE 0 END) AS approved_hours,
                        SUM(CASE WHEN L.l_status = "Declined" THEN L.l_hourly_time_hour ELSE 0 END) AS declined_hours')
                    ->join('aa_users U', 'U.u_id = L.l_u_id')
                    ->where('L.l_is_hourly', 'Yes')
                    ->where('L.l_from_date >=', $rpt_start)
                    ->where('L.l_to_date <=', $rpt_end)
                    ->groupBy('U.u_id')
                    ->orderBy('U.u_id', 'ASC');
                if ($txt_search) $qb->like('U.u_name', $txt_search);
                $records = $qb->get()->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = htmlspecialchars($rec['u_name']);
                    $row[] = number_format($rec['total_hours'] ?? 0, 2);
                    $row[] = number_format($rec['approved_hours'] ?? 0, 2);
                    $row[] = number_format($rec['declined_hours'] ?? 0, 2);
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['u_id'] . '\', \'' . addslashes($rec['u_name']) . '\', \'' . $rpt_start . '\', \'' . $rpt_end . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'hourly_leave_date':
                // Individual records for a specific date (8 columns)
                $records = $db->table('aa_leaves L')
                    ->select('L.*, U.u_name')
                    ->join('aa_users U', 'U.u_id = L.l_u_id')
                    ->where('L.l_is_hourly', 'Yes')
                    ->where('L.l_from_date >=', $rpt_start)
                    ->where('L.l_to_date <=', $rpt_start)
                    ->orderBy('U.u_id', 'ASC')
                    ->get()->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = htmlspecialchars($rec['u_name']);
                    $row[] = date("d-m-Y", strtotime($rec['l_create_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_from_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_to_date']));
                    $row[] = number_format($rec['l_hourly_time_hour'] ?? 0, 2);
                    $row[] = $rec['l_status'];
                    $row[] = $rec['l_hourly_time'] ?? '';
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['l_u_id'] . '\', \'' . addslashes($rec['u_name']) . '\', \'' . $rpt_start . '\', \'' . $rpt_start . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'leave_hourly_detail':
                $l_u_id = $request->getPost('l_u_id');
                $rpt_start = $request->getPost('rpt_start');
                $rpt_end = $request->getPost('rpt_end');
                $user = $db->table('aa_users')->select('u_name')->where('u_id', $l_u_id)->get()->getRowArray();
                $records = $db->table('aa_leaves L')
                    ->where('L.l_u_id', $l_u_id)
                    ->where('L.l_is_hourly', 'Yes')
                    ->where('L.l_from_date >=', $rpt_start)
                    ->where('L.l_to_date <=', $rpt_end)
                    ->get()->getResultArray();
                $data = [];
                $total_hrs = 0;
                foreach ($records as $rec) {
                    $total_hrs += $rec['l_hourly_time_hour'] ?? 0;
                    $row = [];
                    $row[] = $user['u_name'] ?? '';
                    $row[] = date("d-m-Y", strtotime($rec['l_create_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_from_date']));
                    $row[] = date("d-m-Y", strtotime($rec['l_to_date']));
                    $row[] = number_format($rec['l_hourly_time_hour'] ?? 0, 2);
                    $row[] = $rec['l_status'];
                    $row[] = $rec['l_hourly_time'] ?? '';
                    $row[] = $rec['l_message'] . ((!empty($rec['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $rec['l_reply'] : "");
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data, 'total_hrs' => $total_hrs]);
                break;

            case 'total_user_leave__hour':
                $l_u_id = $request->getPost('l_u_id');
                $rpt_start = $request->getPost('rpt_start');
                $rpt_end = $request->getPost('rpt_end');
                $records = $db->table('aa_leaves')->where('l_u_id', $l_u_id)->where('l_is_hourly', 'Yes')->where('l_from_date >=', $rpt_start)->where('l_to_date <=', $rpt_end)->where('l_status', 'Approved')->get()->getResultArray();
                $totalMinutes = 0;
                foreach ($records as $rec) {
                    $parts = explode('.', number_format($rec['l_hourly_time_hour'] ?? 0, 2));
                    $hours = (int)$parts[0];
                    $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
                    $totalMinutes += ($hours * 60) + $minutes;
                }
                $totalHours = intdiv($totalMinutes, 60);
                $totalRemainingMinutes = $totalMinutes % 60;
                $result = sprintf("%d.%02d", $totalHours, $totalRemainingMinutes);
                echo json_encode(['status' => 'pass', 'total_hrs' => '<b>' . $result . ' hrs</b>']);
                break;

            case 'dependency':
                $p_id = $request->getPost('p_id');
                $records = $db->table('aa_tasks')
                    ->select('t_dependancy')
                    ->where('t_p_id', $p_id)
                    ->where('t_dependancy !=', '')
                    ->get()->getResultArray();
                echo json_encode(['status' => 'pass', 'data' => $records]);
                break;

            case 'estimated_actual':
                $txt_p_status = $request->getPost('txt_p_status');
                $records = $db->query("SELECT P.p_id, P.p_name, COALESCE(SUM(T.t_hours), 0) as t_hours, COALESCE(SUM(T.t_hours_planned), 0) as t_hours_planned, COALESCE(SUM(T.t_hours_total), 0) as t_hours_total FROM aa_projects P LEFT JOIN aa_tasks T ON T.t_p_id = P.p_id AND T.t_parent = 0" . ($txt_p_status ? " WHERE P.p_status = '{$txt_p_status}'" : "") . " GROUP BY P.p_id ORDER BY P.p_name ASC")->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $rec['p_name'];
                    $row[] = $rec['t_hours'] ?? 0;
                    $row[] = $rec['t_hours_planned'] ?? 0;
                    $row[] = number_format($this->convertHours($rec['t_hours_total']), 2);
                    $row[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['p_id'] . '\', \'' . $rec['p_name'] . '\')"><i class="fa fa-eye"></i></a>';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'estimated_actual_detail':
                $p_id = $request->getPost('p_id');
                $project = $db->table('aa_projects')->select('p_name')->where('p_id', $p_id)->get()->getRowArray();
                $records = $db->query("SELECT U.u_name, SUM(whours) as t_hours FROM (SELECT at_u_id, ((at_end - at_start) / 60) as whours FROM aa_attendance WHERE at_p_id = '{$p_id}') AS DB2 INNER JOIN aa_users U ON U.u_id = DB2.at_u_id GROUP BY at_u_id ORDER BY u_name ASC")->getResultArray();
                $data = [];
                foreach ($records as $rec) {
                    $row = [];
                    $row[] = $project['p_name'] ?? '';
                    $row[] = $rec['u_name'];
                    $row[] = number_format($this->convertHours($rec['t_hours']), 2);
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'getDaysHeader':
                $month = $request->getPost('month') ?: date('m');
                $year = $request->getPost('year') ?: date('Y');
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                echo json_encode(['days' => $daysInMonth]);
                break;

            case 'attendencedaily':
                $month = $request->getPost('month') ?: date('m');
                $year = $request->getPost('year') ?: date('Y');
                $users = $db->table('aa_users')
                    ->select('u_id, u_name, u_department')
                    ->whereIn('u_type', ['Employee', 'Project Leader'])
                    ->where('u_status', 'Active')
                    ->orderBy('u_name', 'ASC');
                if ($txt_search) $users->like('u_name', $txt_search);
                $users = $users->get()->getResultArray();

                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $data = [];
                foreach ($users as $idx => $user) {
                    $attendance = $db->table('aa_attendance')
                        ->select('at_date, at_start, at_end')
                        ->where('at_u_id', $user['u_id'])
                        ->where('MONTH(at_date)', $month)
                        ->where('YEAR(at_date)', $year)
                        ->get()->getResultArray();
                    $attendance_map = [];
                    foreach ($attendance as $a) {
                        $day = (int)date('j', strtotime($a['at_date']));
                        $hours = ($a['at_end'] - $a['at_start']) / 60;
                        $attendance_map[$day] = ($attendance_map[$day] ?? 0) + $hours;
                    }
                    $row = [];
                    $row[] = $idx + 1;
                    $row[] = $user['u_name'];
                    $totalRaw = 0;
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        if (isset($attendance_map[$d])) {
                            $totalRaw += $attendance_map[$d];
                            $row[] = number_format($this->convertHours($attendance_map[$d]), 2);
                        } else {
                            $row[] = 'A';
                        }
                    }
                    $row[] = number_format($this->convertHours($totalRaw), 2) . ' hrs';
                    $data[] = $row;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => count($data), 'recordsFiltered' => count($data), 'data' => $data]);
                break;

            case 'projects':
                $t_p_id = $request->getPost('t_p_id') ?? 0;
                $t_parent = $request->getPost('t_parent') ?? 0;
                $offset = $request->getPost('start') ?? 0;
                $limit = $request->getPost('length') ?? 25;
                $txt_p_status = $request->getPost('txt_p_status');
                $txt_projects = $request->getPost('txt_projects');
                $txt_p_cat = $request->getPost('txt_p_cat');

                $builder = $db->table('aa_tasks T');
                $builder->select('P.p_name, P.p_id, P.p_number, P.p_value, P.p_cat, P.p_status');
                $builder->join('aa_projects P', 'P.p_id = T.t_p_id', 'left');
                $builder->join('aa_users U', 'U.u_id = T.t_u_id', 'left');
                $builder->distinct();
                if ($txt_p_cat) $builder->where('P.p_cat', $txt_p_cat);
                if ($txt_p_status) $builder->like('P.p_status', $txt_p_status);
                if ($txt_projects) $builder->groupStart()->like('P.p_number', $txt_projects)->orLike('P.p_name', $txt_projects)->groupEnd();
                if ($t_parent > 0) $builder->where('T.t_parent', $t_parent); else $builder->where('T.t_parent', 0);
                if ($t_p_id > 0) $builder->where('T.t_p_id', $t_p_id);
                $builder->orderBy('P.p_name', 'ASC');
                $records = $builder->get()->getResultArray();
                $totalData = count($records);

                $result = [];
                $i = 1;
                foreach ($records as $rec) {
                    $nestedData = [];
                    $nestedData[] = $i++;
                    if ($t_p_id <= 0) {
                        $nestedData[] = $rec['p_name'];
                        $nestedData[] = $rec['p_number'];
                        if (in_array($admin_session['u_type'], ['Master Admin'])) $nestedData[] = $rec['p_value'];
                        $nestedData[] = $rec['p_cat'];
                        $nestedData[] = $rec['p_status'];
                    }
                    $tasks_list = $db->table('aa_tasks T')
                        ->select('T.*, U.u_name, P.p_name')
                        ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
                        ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
                        ->where('T.t_p_id', $rec['p_id'])
                        ->orderBy('T.t_title', 'ASC')
                        ->get()->getResultArray();
                    if (!empty($tasks_list)) {
                        $task_text = '';
                        foreach ($tasks_list as $task) {
                            $assigns = $db->query("SELECT TU.*, U.u_name, U.u_id FROM aa_task2user TU LEFT JOIN aa_users U ON TU.tu_u_id = U.u_id WHERE TU.tu_t_id = '{$task['t_id']}'")->getResultArray();
                            $stings = '';
                            foreach ($assigns as $assign) {
                                $assign_hrs = $db->query("SELECT SUM(((atte.at_end - atte.at_start) / 60)) as TOTALwhours FROM aa_attendance as atte, aa_users U WHERE atte.at_t_id = '{$assign['tu_t_id']}' AND atte.at_u_id = U.u_id AND U.u_id = '{$assign['tu_u_id']}'")->getRowArray();
                                $n = $assign_hrs['TOTALwhours'] ?? 0;
                                $stings .= $assign['u_name'] . ' - <b>' . number_format($this->convertHours($n), 2) . ' hr</b>  , ';
                            }
                            $task_text .= "\r\r<b>Task Title: " . $task['t_title'] . "</b>\r<b>Priority: </b>" . $task['t_priority'] . "\r<b>Posted Date: </b>" . convert_db2display($task['t_createdate']) . "\r<b>Posted By: </b>" . $task['u_name'] . "\r<b>Assigns: </b>" . $stings;
                        }
                        $nestedData[] = $task_text;
                    } else {
                        $nestedData[] = 'No Task';
                    }
                    $result[] = $nestedData;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($totalData), 'recordsFiltered' => intval($totalData), 'data' => $result]);
                break;

            case 'projects_employee':
                $t_id = $request->getPost('t_id') ?? 0;
                $t_p_id = $request->getPost('t_p_id') ?? 0;
                $t_parent = $request->getPost('t_parent') ?? 0;

                if ($t_id > 0) {
                    $record = $db->table('aa_tasks T')
                        ->select('T.*, P.p_name, U.u_name')
                        ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
                        ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
                        ->where('T.t_id', $t_id)
                        ->get()->getRowArray();
                    if ($record) {
                        $assigns = $db->table('aa_task2user TU')
                            ->select('TU.*, U.u_name, U.u_id')
                            ->join('aa_users U', 'TU.tu_u_id = U.u_id')
                            ->where('TU.tu_t_id', $t_id)
                            ->where('TU.tu_removed', 'No')
                            ->get()->getResultArray();
                        $files = $db->table('aa_task_files')->where('tf_t_id', $t_id)->get()->getResultArray();
                        echo json_encode(['status' => 'pass', 'data' => $record, 'assigns' => $assigns, 'files' => $files]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Selected record is not available.']);
                    }
                } else {
                    $txt_projects = $request->getPost('txt_projects');
                    $txt_status = $request->getPost('txt_status');
                    $txt_employee = $request->getPost('txt_employee');

                    $builder = $db->table('aa_tasks T');
                    $builder->select('T.*, P.p_name, U.u_name');
                    $builder->join('aa_projects P', 'P.p_id = T.t_p_id', 'left');
                    $builder->join('aa_users U', 'U.u_id = T.t_u_id', 'left');
                    if ($txt_projects) $builder->like('P.p_name', $txt_projects);
                    if ($txt_status) $builder->where('T.t_status', $txt_status);
                    $builder->where('P.p_status', 'Active');
                    if ($t_parent > 0) $builder->where('T.t_parent', $t_parent);
                    if ($t_p_id > 0) $builder->where('T.t_p_id', $t_p_id);
                    $builder->orderBy('T.t_priority', 'ASC');
                    $records = $builder->get()->getResultArray();
                    $totalData = count($records);

                    $result = [];
                    $i = 1;
                    foreach ($records as $rec) {
                        if ($txt_employee) {
                            $assignCheck = $db->table('aa_task2user')->where('tu_t_id', $rec['t_id'])->where('tu_removed', 'No')->where('tu_u_id', $txt_employee)->get()->getRowArray();
                            if (!$assignCheck) continue;
                        }
                        $nestedData = [];
                        $nestedData[] = $i++;
                        if ($t_p_id <= 0) $nestedData[] = $rec['p_name'];
                        if ($rec['t_parent'] != 0) {
                            $main = $db->table('aa_tasks')->select('t_title')->where('t_id', $rec['t_parent'])->get()->getRowArray();
                            $nestedData[] = ($main['t_title'] ?? '') . '<br/> <b>- ' . $rec['t_title'] . '</b>';
                        } else {
                            $nestedData[] = $rec['t_title'];
                        }
                        $nestedData[] = ($rec['t_parent'] != 0) ? 'Sub Task' : 'Main Task';
                        $nestedData[] = $rec['t_priority'];
                        $nestedData[] = convert_db2display($rec['t_createdate']);
                        $nestedData[] = $rec['u_name'];
                        $assigns = $db->table('aa_task2user TU')
                            ->select('U.u_name')
                            ->join('aa_users U', 'TU.tu_u_id = U.u_id')
                            ->where('TU.tu_t_id', $rec['t_id'])
                            ->where('TU.tu_removed', 'No')
                            ->get()->getResultArray();
                        $nestedData[] = implode(', ', array_column($assigns, 'u_name'));
                        if ($t_parent > 0) $nestedData[] = $rec['t_hours'];
                        $nestedData[] = $rec['t_status'];
                        $nestedData[] = number_format($this->convertHours($rec['t_hours_total'] ?? 0), 2);
                        $anchors = '<a href="javascript://" onClick="showData(\'' . $rec['t_id'] . '\', \'' . $rec['t_p_id'] . '\', \'' . addslashes($rec['p_name']) . '\', \'' . addslashes($rec['t_title']) . '\'' . ($txt_employee ? ', \'' . $txt_employee . '\'' : '') . ')" class="btn btn-primary btn-md"><i class="fa fa-eye"></i></a>&nbsp; ';
                        $nestedData[] = $anchors;
                        $result[] = $nestedData;
                    }
                    echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($totalData), 'recordsFiltered' => intval($totalData), 'data' => $result]);
                }
                break;

            case 'employee_salary_list':
                $offset = $request->getPost('start') ?? 0;
                $limit = $request->getPost('length') ?? 25;
                $txt_U_Type = $request->getPost('txt_U_Type');
                $txt_U_Status = $request->getPost('txt_U_Status');

                $builder = $db->table('aa_users');
                $builder->where('u_type !=', 'Master Admin');
                $builder->where('u_type !=', 'Associate User');
                $builder->where('u_type !=', 'Super Admin');
                if ($txt_search) $builder->groupStart()->like('u_name', $txt_search)->orLike('u_username', $txt_search)->groupEnd();
                if ($txt_U_Type) $builder->like('u_type', $txt_U_Type);
                if ($txt_U_Status) $builder->like('u_status', $txt_U_Status); else $builder->like('u_status', 'Active');
                $builder->orderBy('u_id', 'DESC');
                if ($limit > 0) $builder->limit($limit, $offset);
                $records = $builder->get()->getResultArray();

                $countBuilder = $db->table('aa_users');
                $countBuilder->where('u_type !=', 'Master Admin');
                $countBuilder->where('u_type !=', 'Associate User');
                $countBuilder->where('u_type !=', 'Super Admin');
                if ($txt_search) $countBuilder->groupStart()->like('u_name', $txt_search)->orLike('u_username', $txt_search)->groupEnd();
                if ($txt_U_Type) $countBuilder->like('u_type', $txt_U_Type);
                if ($txt_U_Status) $countBuilder->like('u_status', $txt_U_Status); else $countBuilder->like('u_status', 'Active');
                $totalData = $countBuilder->countAllResults();

                $result = [];
                foreach ($records as $rec) {
                    $nestedData = [];
                    $nestedData[] = $rec['u_username'];
                    $nestedData[] = $rec['u_name'];
                    $nestedData[] = ($admin_session['u_type'] ?? '') === 'Master Admin' ? ($rec['u_salary'] ?? '') : '0';
                    $nestedData[] = $rec['u_type'];
                    $nestedData[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['u_id'] . '\', \'' . addslashes($rec['u_name']) . '\')"><i class="fa fa-eye"></i></a>&nbsp; ';
                    $result[] = $nestedData;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($totalData), 'recordsFiltered' => intval($totalData), 'data' => $result]);
                break;

            case 'employee_salary_detail':
                $u_id = $request->getPost('u_id');
                if ($u_id > 0) {
                    $records = $db->table('aa_users U')
                        ->select('US.*, U.u_name')
                        ->join('aa_users_salary US', 'U.u_id = US.u_id')
                        ->where('U.u_id', $u_id)
                        ->orderBy('US.id', 'DESC')
                        ->get()->getResultArray();
                    $totalData = count($records);
                    $result = [];
                    foreach ($records as $rec) {
                        $nestedData = [];
                        $nestedData[] = $rec['u_name'];
                        $nestedData[] = $rec['u_start_date'] ?? '';
                        $nestedData[] = $rec['u_end_date'] ?? '';
                        $nestedData[] = ($admin_session['u_type'] ?? '') === 'Master Admin' ? ($rec['u_salary'] ?? '') : '0';
                        $result[] = $nestedData;
                    }
                    echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($totalData), 'recordsFiltered' => intval($totalData), 'data' => $result]);
                } else {
                    echo json_encode(['draw' => intval($draw), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                }
                break;

            case 'projectprofitloss':
                $p_id = $request->getPost('p_id') ?? 0;
                if ($p_id > 0) {
                    $record = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
                    if ($record) {
                        $file_name = FCPATH . 'assets/logos/plogo_' . $record['p_id'] . '.jpg';
                        $record['photo'] = file_exists($file_name) ? base_url('assets/logos/plogo_' . $record['p_id'] . '.jpg') : '';
                        $pe = '';
                        if (in_array($admin_session['u_type'], ['Master Admin'])) {
                            $pe = $db->table('aa_project_expense')
                                ->select('pe_val, pe_lbl')
                                ->where('pe_p_id', $p_id)
                                ->get()->getResultArray();
                        }
                        echo json_encode(['status' => 'pass', 'data' => $record, 'pe' => $pe]);
                    } else {
                        echo json_encode(['status' => 'fail', 'message' => 'Selected record is not available.']);
                    }
                } else {
                    $txt_p_cat = $request->getPost('txt_p_cat');
                    $txt_p_status = $request->getPost('txt_p_status');
                    $offset = $request->getPost('start') ?? 0;
                    $limit = $request->getPost('length') ?? 25;

                    $builder = $db->table('aa_projects');
                    if ($txt_p_cat) $builder->where('p_cat', $txt_p_cat);
                    if ($txt_p_status) $builder->where('p_status', $txt_p_status);
                    if ($txt_search) $builder->groupStart()->like('p_number', $txt_search)->orLike('p_name', $txt_search)->groupEnd();
                    $builder->orderBy('p_number', 'ASC');
                    if ($limit > 0) $builder->limit($limit, $offset);
                    $records = $builder->get()->getResultArray();
                    $totalData = count($records);

                    $result = [];
                    foreach ($records as $rec) {
                        $nestedData = [];
                        $nestedData[] = $rec['p_number'];
                        $nestedData[] = $rec['p_name'];
                        $nestedData[] = $rec['p_address'] ?? '';
                        if (in_array($admin_session['u_type'], ['Master Admin'])) {
                            $nestedData[] = $rec['p_value'] ?? 0;
                            // get total expense (salary + project expense)
                            $total_salary_row = $db->query("SELECT SUM(total_salary) as final_salary FROM (SELECT ((at_end - at_start) / 60 * u_salary) as total_salary FROM aa_attendance A INNER JOIN aa_users_salary U ON A.at_u_id = U.u_id WHERE at_p_id = '{$rec['p_id']}' AND at_date >= u_start_date AND at_date < u_end_date) as FinnalDB")->getRowArray();
                            $total_salary = $total_salary_row['final_salary'] ?? 0;
                            $total_expense_row = $db->query("SELECT SUM(pe_val) as total_expense FROM aa_project_expense WHERE pe_p_id = '{$rec['p_id']}'")->getRowArray();
                            $total_exp = $total_salary + ($total_expense_row['total_expense'] ?? 0);
                            $nestedData[] = $total_exp;
                            $nestedData[] = ($rec['p_value'] ?? 0) - $total_exp;
                        }
                        $nestedData[] = $rec['p_status'];
                        $nestedData[] = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $rec['p_id'] . '\', \'' . addslashes($rec['p_name']) . '\')"><i class="fa fa-eye"></i></a>&nbsp; ';
                        $result[] = $nestedData;
                    }
                    echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($totalData), 'recordsFiltered' => intval($totalData), 'data' => $result]);
                }
                break;

            case 'pemployeedetail':
                $p_id = $request->getPost('p_id');
                $isMasterAdmin = ($admin_session['u_type'] ?? '') === 'Master Admin';
                $recordsP = $db->table('aa_projects')->where('p_id', $p_id)->get()->getRowArray();
                $records = $db->query("SELECT total_salary as final_salary, total_hrs, Username FROM (SELECT (SUM((at_end - at_start) / 60 * US.u_salary)) as total_salary, SUM((at_end - at_start) / 60) as total_hrs, U.u_id as UserId, U.u_name as Username FROM aa_attendance A INNER JOIN aa_users_salary US ON A.at_u_id = US.u_id, aa_users as U WHERE at_p_id = '{$p_id}' AND at_date >= US.u_start_date AND at_date < US.u_end_date AND U.u_id = US.u_id GROUP BY U.u_id) as FinnalDB")->getResultArray();
                $total = 0;
                $result = [];
                if (empty($records) && $recordsP) {
                    $result[] = [$recordsP['p_number'], $recordsP['p_name'], 'Not Find', '0'];
                }
                foreach ($records as $rec) {
                    $nestedData = [];
                    $nestedData[] = $recordsP['p_number'] ?? '';
                    $nestedData[] = $recordsP['p_name'] ?? '';
                    $nestedData[] = $rec['Username'];
                    $nestedData[] = number_format($this->convertHours($rec['total_hrs'] ?? 0), 2);
                    $nestedData[] = $isMasterAdmin ? $rec['final_salary'] : 0;
                    if ($isMasterAdmin) $total += $rec['final_salary'];
                    $result[] = $nestedData;
                }
                echo json_encode(['draw' => intval($draw), 'data' => $result, 'total' => $total]);
                break;

            case 'projectData':
                $offset = $request->getPost('start') ?? 0;
                $limit = $request->getPost('length') ?? 25;
                $leader_id = $request->getPost('leader_id');
                $from_date = $request->getPost('from_date');
                $to_date = $request->getPost('to_date');
                $project_id = $request->getPost('project_id');
                $filter_status = $request->getPost('filter_status');

                $sql = "SELECT w.*, p.p_name AS project_name, u.u_name AS leader_name,
                    (SELECT COUNT(*) FROM aa_users WHERE u_leader = w.leader_id) AS team_assigned,
                    (SELECT GROUP_CONCAT(u2.u_name ORDER BY u2.u_name SEPARATOR ', ') FROM aa_weekly_work_users wu JOIN aa_users u2 ON u2.u_id = wu.u_id WHERE wu.weekly_work_id = w.w_id) AS assigned_users,
                    (SELECT COUNT(*) FROM aa_projects WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,
                    (SELECT COUNT(*) FROM aa_weekly_work_dependency d WHERE d.w_id = w.w_id AND d.status != 'Completed') AS incomplete_deps
                    FROM aa_weekly_work w
                    LEFT JOIN aa_projects p ON p.p_id = w.p_id
                    LEFT JOIN aa_users u ON u.u_id = w.leader_id
                    WHERE 1=1";
                if ($leader_id) $sql .= " AND w.leader_id = " . intval($leader_id);
                if ($project_id) $sql .= " AND w.p_id = " . intval($project_id);
                if ($from_date && $to_date) {
                    $fd = date('Y-m-d', strtotime($from_date));
                    $td = date('Y-m-d', strtotime($to_date));
                    $sql .= " AND w.week_from <= '{$td}' AND w.week_to >= '{$fd}'";
                }
                if (!empty($filter_status) && $filter_status != 'All') $sql .= " AND w.status = '" . $db->escapeString($filter_status) . "'";
                $sql .= " ORDER BY u.u_name ASC";
                if ($limit > 0) $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

                $records = $db->query($sql)->getResultArray();
                $totalData = count($records);

                $result = [];
                foreach ($records as $row) {
                    $nestedData = [];
                    $nestedData[] = $row['leader_name'] ?? '-';
                    $nestedData[] = (int)($row['team_assigned'] ?? 0);
                    $nestedData[] = htmlspecialchars($row['no_of_persons'] ?? '');
                    $nestedData[] = htmlspecialchars($row['assigned_users'] ?? '-');
                    $nestedData[] = (int)($row['no_of_projects'] ?? 0);
                    $nestedData[] = htmlspecialchars($row['project_name'] ?? '');
                    $wf = !empty($row['week_from']) ? date('d-m-Y', strtotime($row['week_from'])) : '';
                    $wt = !empty($row['week_to'])   ? date('d-m-Y', strtotime($row['week_to']))   : '';
                    $nestedData[] = htmlspecialchars($wf . ' to ' . $wt);
                    $nestedData[] = htmlspecialchars($row['task_name'] ?? '');
                    $nestedData[] = htmlspecialchars($row['submission_date'] ?? '');
                    $nestedData[] = htmlspecialchars($row['status'] ?? '');
                    $depHTML = '';
                    if (!empty($row['incomplete_deps']) && $row['incomplete_deps'] > 0) {
                        $depHTML .= '<br><a href="javascript:void(0);" class="btn btn-warning btn-xs view-dep-btn" data-wid="' . $row['w_id'] . '" data-type="incomplete">View Incomplete (' . $row['incomplete_deps'] . ')</a>';
                    }
                    $depHTML .= '<br><a href="javascript:void(0);" class="btn btn-info btn-xs view-dep-btn" data-wid="' . $row['w_id'] . '" data-type="all">View All</a>';
                    $nestedData[] = $depHTML;
                    $result[] = $nestedData;
                }
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($totalData), 'recordsFiltered' => intval($totalData), 'data' => $result]);
                break;

            case 'dependenciesReport':
                $status = $request->getPost('status');
                $sql = "SELECT d.wd_id, d.dependency_text, d.dependency_type, d.dep_leader_ids, d.priority, d.status, d.completed_date, d.created_date,
                    u.u_name as created_by, ww.week_from, ww.week_to, p.p_name as project_name
                    FROM aa_weekly_work_dependency d
                    LEFT JOIN aa_users u ON u.u_id = d.created_by
                    LEFT JOIN aa_weekly_work ww ON ww.w_id = d.w_id
                    LEFT JOIN aa_projects p ON p.p_id = ww.p_id";
                if (empty($status) || $status === 'Incomplete') {
                    $sql .= " WHERE d.status != 'Completed'";
                } elseif ($status !== 'All') {
                    $sql .= " WHERE d.status = '" . $db->escapeString($status) . "'";
                }
                $sql .= " ORDER BY d.created_date DESC";
                $deps = $db->query($sql)->getResultArray();

                foreach ($deps as &$d) {
                    if ($d['dependency_type'] === 'Internal' && !empty($d['dep_leader_ids'])) {
                        $leader_ids = explode(',', $d['dep_leader_ids']);
                        $leader_names = $db->table('aa_users')
                            ->select("GROUP_CONCAT(u_name SEPARATOR ', ') AS leader_names")
                            ->whereIn('u_id', $leader_ids)
                            ->get()->getRowArray();
                        $d['assigned_to'] = $leader_names['leader_names'] ?? '-';
                    } else {
                        $d['assigned_to'] = '-';
                    }
                }
                unset($d);
                echo json_encode(['status' => 'pass', 'data' => $deps]);
                break;

            default:
                echo json_encode(['draw' => intval($draw), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                break;
        }
        exit;
    }

    private function convertHours($n)
    {
        $whole = floor($n);
        $fraction = $n - $whole;
        if ($fraction == 0.75) return $whole + 0.45;
        elseif ($fraction == 0.25) return $whole + 0.15;
        elseif ($fraction == 0.50) return $whole + 0.30;
        else return $whole + $fraction;
    }

    public function messages()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $session = service('session');
        $admin_session = $session->get('admin_session');
        $db = \Config\Database::connect();

        $act = $request->getPost('act');

        if ($act === 'read') {
            // Mark message as read
            $m_id = $request->getPost('m_id');
            if ($m_id) {
                $db->table('aa_message_users')
                    ->where('mu_m_id', $m_id)
                    ->where('mu_u_id', $admin_session['u_id'])
                    ->update(['mu_read' => 1]);
            }
            echo json_encode(['status' => 'pass']);
            exit;
        }

        // Get unread message count
        $unread = $db->table('aa_message_users')
            ->where('mu_u_id', $admin_session['u_id'])
            ->where('mu_read', 0)
            ->countAllResults();

        echo json_encode([
            'status' => 'pass',
            'unread_count' => $unread
        ]);
        exit;
    }

    public function message_report()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        $db = \Config\Database::connect();

        $draw       = intval($request->getPost('draw') ?? 1);
        $start      = intval($request->getPost('start') ?? 0);
        $length     = intval($request->getPost('length') ?? 25);
        $project_id = $request->getPost('project_id');
        $search_date = $request->getPost('search_date');
        $discipline  = $request->getPost('discipline');

        $qb = $db->table('aa_project_messages pm')
            ->select('p.p_name, pm.pm_datetime, pm.pm_text, pm.pm_descipline,
                GROUP_CONCAT(pmr.pmr_text ORDER BY pmr.pmr_datetime SEPARATOR "\n") AS replies')
            ->join('aa_projects p', 'p.p_id = pm.pm_p_id')
            ->join('aa_project_message_replies pmr', 'pmr.pmr_pm_id = pm.pm_id', 'left')
            ->where('pm.pm_deleted', 0)
            ->groupBy('pm.pm_id')
            ->orderBy('pm.pm_datetime', 'DESC');

        if (!empty($project_id)) $qb->where('pm.pm_p_id', intval($project_id));
        if (!empty($search_date)) $qb->where('DATE(pm.pm_datetime)', $search_date);
        if (!empty($discipline)) $qb->where('pm.pm_descipline', $discipline);

        $totalQuery = clone $qb;
        $total = count($totalQuery->get()->getResultArray());

        if ($length > 0) $qb->limit($length, $start);
        $records = $qb->get()->getResultArray();

        $data = [];
        foreach ($records as $rec) {
            $row = [];
            $row[] = htmlspecialchars($rec['p_name']);
            $row[] = $rec['pm_datetime'] ? date('d-m-Y', strtotime($rec['pm_datetime'])) : '';
            $row[] = htmlspecialchars($rec['pm_text'] ?? '');
            $row[] = htmlspecialchars($rec['pm_descipline'] ?? '');
            $row[] = $rec['replies'] ?? '';
            $data[] = $row;
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
        exit;
    }

    public function registrations_get()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        $request = service('request');
        echo json_encode([
            'draw' => $request->getPost('draw') ?? 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ]);
        exit;
    }

    public function registration_add_edit()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        echo json_encode(['status' => 'pass', 'message' => 'Registration saved.']);
        exit;
    }

    public function registration_delete()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        echo json_encode(['status' => 'pass', 'message' => 'Registration deleted.']);
        exit;
    }
}
