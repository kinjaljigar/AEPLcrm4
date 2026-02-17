<?php
defined('SYSTEMPATH') or exit('No direct script access allowed');
$view_data['show_menu'] = true;
$view_data['extra_css'] = array();
$view_data['extra_foot_js'] = array();
switch ($view_data['page']) {
    case 'login':
        $view_data['show_menu'] = false;
        break;
}
if (isset($view_data['plugins'])) {
    foreach ($view_data['plugins'] as $key => $val) {
        switch ($key) {
            case 'datatable':
                $view_data['extra_css'][] = 'assets/admin/addons/datatables/dataTables.bootstrap.css';
                $view_data['extra_css'][] = 'assets/admin/addons/datatables/extensions/Responsive/css/dataTables.responsive.css';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/jquery.dataTables.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/extensions/Responsive/js/dataTables.responsive.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/dataTables.bootstrap.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/buttons.print.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/dataTables.buttons.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/pdfmake.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/jszip.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/vfs_fonts.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/datatables/buttons.html5.min.js';
                break;
            case 'datepicker':
                $view_data['extra_css'][] = 'assets/admin/addons/bootstrap-datepicker/css/bootstrap-datepicker.standalone.min.css';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/bootstrap-datepicker/js/bootstrap-datepicker.min.js';
                //$view_data['extra_foot_js'][] = 'assets/common/js/moment.min.js';
                break;
            case 'form_validation':
                $view_data['extra_foot_js'][] = 'assets/admin/addons/form_validation/jquery.validate.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/form_validation/additional-methods.min.js';
                $view_data['extra_foot_js'][] = 'assets/admin/addons/form_validation/extra_method.js';
                break;
            case 'momentjs':
                $view_data['extra_foot_js'][] = 'assets/common/js/moment.min.js';
                break;
            case 'chartjs':
                $view_data['extra_foot_js'][] = 'assets/admin/addons/chart/Chart.min.js';
                break;
            case 'select2':
                //$view_data['extra_css'][] = 'assets/admin/addons/select2/select2.min.css';
                //$view_data['extra_foot_js'][] = 'assets/admin/addons/select2/select2.min.js';
                //$view_data['extra_foot_js'][] = 'assets/admin/addons/select2/common-select2.js';
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $view_data['meta_title']; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/favicon.ico'); ?>">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/addons/bootstrap/css/bootstrap.min.css'); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!--        Ionicons // may add later if required-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <?php foreach ($view_data['extra_css'] as $file) { ?>
        <link href="<?php echo base_url($file); ?>" rel="stylesheet" type="text/css" />
    <?php } ?>
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/AdminLTE.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/skins/_all-skins.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/adminlte-customized.css?f01'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/custom-checks.css?05'); ?>">
    <script>
        var sb_base_url = '<?php echo base_url() ?>';
        var holidays = new Object();
    </script>
</head>

<body
    class="hold-transition skin-blue sidebar-collapse <?php echo $view_data['page'] == 'login' ? 'login-body' : ''; ?>">
    <!-- Site wrapper -->
    <div class="wrapper">
        <header class="main-header">
            <div class="logo-wrap">
                <a href="#"><img src="<?php echo base_url('assets/images/logo.jpg'); ?>" alt="" /></a>
            </div>
            <nav class="navbar navbar-static-top" role="navigation">
                <?php if ($view_data['show_menu'] == true) { ?>
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span> <span class="menu_text">Menu</span>
                    </a>

                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a aria-expanded="false" href="#" class="dropdown-toggle" data-toggle="dropdown">

                                    <span class="hidden-xsl"
                                        style="font-weight:bold;font-size:16px;"><?php echo isset($view_data['admin_session']['u_name']) ? "Hi, " . $view_data['admin_session']['u_name'] : ''; ?></span>
                                </a>
                                <?php /* <ul class="dropdown-menu">
                                      <!-- Menu Footer-->
                                      <li class="user-footer">
                                      <div class="pull-right">
                                      <a class="btn btn-default btn-flat" href="<?php echo base_url(); ?>availadmin/logout">Sign
                            out</a>
                </div>
                </li>
                </ul> */ ?>
                            </li>
                        </ul>
                    </div>
                <?php } ?>
            </nav>
        </header>

        <?php if ($view_data['show_menu'] == true) { ?>
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <?php /* <div class="user-panel">
                            <div class="pull-left image" style="display: none;">
                                <img src="<?php //echo base_url('admin/dist/img/user2-160x160.jpg'); ?>"
            class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p><?php // echo $username;         ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
            </div> */ ?>
                    <!-- sidebar menu: : style can be found in sidebar.less -->

                    <ul class="sidebar-menu">
                        <li class="header">MAIN NAVIGATION</li>
                        <?php
                        $menu = array();
                        $u_type = $view_data['admin_session']['u_type'] ?? '';
                        if ($u_type == 'Master Admin') {
                            $menu['dashboard'] = array('Dashboard', 'home/index', 'fa-dashboard');
                            if (($view_data['admin_session']['u_app_auth'] ?? '0') == '1') {
                                if ($u_type != 'Associate User') {
                                    $menu['conferences'] = array('Conferences', 'conference', 'fa-tasks');
                                    $menu['schedules'] = array('Schedules', 'schedule', 'fa-bell');
                                    if ($u_type == 'Master Admin' || $u_type == 'Bim Head' || $u_type == 'Super Admin') {
                                        $menu['company'] = array('Companies', 'company', 'fa-tasks');
                                    }
                                }
                                $menu['companyuser'] = array('Associate Users', 'companyuser', 'fa-users');
                                $menu['usertask'] = array('Aashir Connect', 'usertask', 'fa-tasks');
                                //}
                            }

                            $multi_level = array();
                            $multi_levelticket = array();

                            $multi_level['projects'] = array('Projects', 'home/projects', 'fa-sitemap');
                            $multi_level['messages'] = array('Mail Links', 'home/messages', 'fa-envelope');
                            //$multi_level['dependency'] = array('Project Dependency', 'home/dependency', 'fa-hand-o-right');
                            $multi_level['employees'] = array('Employees', 'home/employees', 'fa-users');
                            $multi_level['settings'] = array('Settings', 'home/settings', 'fa-cogs');
                            $multi_level['tasks'] = array('Tasks', 'home/tasks', 'fa-tasks');
                            $multi_level['leave_request'] = array('Leave Request', 'home/leaves', 'fa-briefcase');
                            $multi_level['holidays'] = array('Holiday', 'home/holidays', 'fa-plane');


                            $multi_levelticket['ticket-category'] = array('Ticket Category', 'ticket-category', 'fa-list-alt');
                            $multi_levelticket['raiseticket'] = array('Raise Ticket', 'ticket/add', 'fa-ticket');
                            $multi_levelticket['ticket'] = array('Ticket History', 'ticket/my', 'fa-ticket');
                            $multi_levelticket['assignticket'] = array('Assign Tickets', 'ticket/assigned', 'fa-comment');


                            $multi_levelReport = array();
                            $multi_levelReport['daily_report'] = array('Daily Report', 'home/report_daily', 'fa-hand-o-right');
                            $multi_levelReport['employee_report'] = array('Employee Work Report', 'home/report_employee', 'fa-hand-o-right');
                            $multi_levelReport['leaderemp_report'] = array('Leader Employee Report', 'home/report_leader_employee', 'fa-hand-o-right');

                            $multi_levelReport['project_data'] = array('Weekly Work Report', 'home/projectData', 'fa-hand-o-right');
                            $multi_levelReport['dependencies'] = array('Dependencies Report', 'home/dependencies', 'fa-hand-o-right');


                            $multi_levelReport['employee_attendance_report'] = array('Employee Atttendance Report', 'home/report_attendance_employee', 'fa-hand-o-right');
                            if ($u_type == 'Master Admin' || $u_type == 'Super Admin')
                                $multi_levelReport['employee_salary_report'] = array('Employee Salary Report', 'home/report_employee_salary', 'fa-hand-o-right');
                            //$multi_levelReport['report_dependency'] = array('Dependency', 'home/report_dependency', 'fa-hand-o-right');
                            $multi_levelReport['evsa_report'] = array('Estimated v/s Actual', 'home/report_estimated_actual', 'fa-hand-o-right');


                            $multi_levelleaves['leave_report_date'] = array('Leave Report - Datewise', 'home/report_leave_date', 'fa-hand-o-right');
                            $multi_levelleaves['leave_report'] = array('Leave Report', 'home/report_leave', 'fa-hand-o-right');
                            $multi_levelleaves['leave_total_report'] = array('Yearly Leave Report', 'home/report_leave_total', 'fa-hand-o-right');
                            $multi_levelleaves['report_leave_hour_date'] = array('Hourly Leave Report - Datewise', 'home/report_leave_hour_date', 'fa-hand-o-right');
                            $multi_levelleaves['report_leave_hour'] = array('Hourly Leave Report', 'home/report_leave_hour', 'fa-hand-o-right');
                            $multi_levelReport['leaves'] = array('Leaves', '#', 'fa-briefcase', $multi_levelleaves);


                            $multi_levelReport['message_report'] = array('Mail Link Report', 'home/report_message', 'fa-hand-o-right');


                            $multi_levelReport['project_report'] = array('Project Report', 'home/report_project', 'fa-hand-o-right');
                            $multi_levelReport['employee_project_report'] = array('Employee Project Report', 'home/report_project_employee', 'fa-hand-o-right');
                            $multi_levelReport['project_profitreport'] = array('Profit/Loss Report', 'home/report_profitloss', 'fa-hand-o-right');
                            $menu['tickets'] = array('Tickets', '#', 'fa-file', $multi_levelticket);
                            $menu['CRM'] = array('CRM', '#', 'fa-file', $multi_level);
                            $menu['Reports'] = array('Reports', '#', 'fa-file', $multi_levelReport);
                        } else {
                            $authorization = new \App\Libraries\Authorization();
                            if ($authorization->is_role_allowed($u_type, ['TaskCoordinator'])) {
                                $menu['dashboard'] = array('Dashboard', 'home/index', 'fa-dashboard');
                                $menu['dependency'] = array('Weekly Work + Dependency', 'home/dependency', 'fa-hand-o-right');
                                $menu['dependencies'] = array('All Dependencies', 'home/dependencies', 'fa-hand-o-right');
                            } else if ($authorization->is_role_allowed($u_type, ['MailCoordinator'])) {
                                //$menu['dashboard'] = array('Dashboard', 'home/index', 'fa-dashboard');
                                $menu['messages'] = array('Mail Links', 'home/messages', 'fa-envelope');
                            } else {
                                if ($u_type != 'Associate User') {
                                    if ($u_type == 'Master Admin' || $u_type == 'Super Admin' || $u_type == 'Bim Head') {
                                        $menu['dashboard'] = array('Dashboard', 'home/index', 'fa-dashboard');
                                        $menu['projects'] = array('Projects', 'home/projects', 'fa-sitemap');
                                        $menu['messages'] = array('Mail Links', 'home/messages', 'fa-envelope');
                                        $menu['employees'] = array('Employees', 'home/employees', 'fa-users');
                                        $menu['settings'] = array('Settings', 'home/settings', 'fa-cogs');
                                    }
                                    if ($u_type == 'Project Leader') {
                                        $menu['dashboard'] = array('Dashboard', 'home/index', 'fa-dashboard');
                                        //$menu['projects'] = array('Projects', 'home/projects', 'fa-sitemap');
                                        $menu['messages'] = array('Mail Links', 'home/messages', 'fa-envelope');
                                    }

                                    $menu['tasks'] = array('Tasks', 'home/tasks', 'fa-tasks');

                                    $menu['leave_request'] = array('Leave Request', 'home/leaves', 'fa-briefcase');
                                    $menu['timesheet'] = array('Timesheet', 'home/timesheet', 'fa-briefcase');
                                    $menu['holidays'] = array('Holiday', 'home/holidays', 'fa-plane');
                                }
                                $multi_level = array();
                                $multi_levelticket = array();
                                //$multi_level['leave_report'] = array('Leave Report', 'home/report_leave', 'fa-hand-o-right');
                                if ($u_type == 'Master Admin' || $u_type == 'Bim Head' || $u_type == 'Super Admin') {


                                    $menu['empattendance'] = array('Employee Attendance', 'home/empattendance', 'fa-hand-o-right');

                                    $multi_level['daily_report'] = array('Daily Report', 'home/report_daily', 'fa-hand-o-right');
                                    $multi_level['employee_report'] = array('Employee Work Report', 'home/report_employee', 'fa-hand-o-right');
                                    $multi_level['leaderemp_report'] = array('Leader Employee Report', 'home/report_leader_employee', 'fa-hand-o-right');
                                    $multi_level['employee_attendance_report'] = array('Employee Atttendance Report', 'home/report_attendance_employee', 'fa-hand-o-right');
                                    $multi_level['project_data'] = array('Weekly Work Report', 'home/projectData', 'fa-hand-o-right');
                                    $multi_level['dependencies'] = array('Dependencies Report', 'home/dependencies', 'fa-hand-o-right');
                                    if ($u_type == 'Master Admin')
                                        $multi_level['employee_salary_report'] = array('Employee Salary Report', 'home/report_employee_salary', 'fa-hand-o-right');
                                    $multi_level['report_dependency'] = array('Dependency', 'home/report_dependency', 'fa-hand-o-right');
                                    $multi_level['evsa_report'] = array('Estimated v/s Actual', 'home/report_estimated_actual', 'fa-hand-o-right');


                                    $multi_levelleaves['leave_report_date'] = array('Leave Report - Datewise', 'home/report_leave_date', 'fa-hand-o-right');
                                    $multi_levelleaves['leave_report'] = array('Leave Report', 'home/report_leave', 'fa-hand-o-right');
                                    $multi_levelleaves['leave_total_report'] = array('Yearly Leave Report', 'home/report_leave_total', 'fa-hand-o-right');
                                    $multi_levelleaves['report_leave_hour_date'] = array('Hourly Leave Report - Datewise', 'home/report_leave_hour_date', 'fa-hand-o-right');
                                    $multi_levelleaves['report_leave_hour'] = array('Hourly Leave Report', 'home/report_leave_hour', 'fa-hand-o-right');
                                    $multi_level['leaves'] = array('Leaves', '#', 'fa-briefcase', $multi_levelleaves);


                                    $multi_level['message_report'] = array('Mail Link Report', 'home/report_message', 'fa-hand-o-right');

                                    $multi_level['project_report'] = array('Project Report', 'home/report_project', 'fa-hand-o-right');
                                    $multi_level['employee_project_report'] = array('Employee Project Report', 'home/report_project_employee', 'fa-hand-o-right');
                                    $multi_level['project_profitreport'] = array('Profit/Loss Report', 'home/report_profitloss', 'fa-hand-o-right');
                                    $menu['reports'] = array('Reports', '#', 'fa-file', $multi_level);

                                    $multi_levelticket['ticket-category'] = array('Ticket Category', 'ticket-category', 'fa-list-alt');
                                }
                                $multi_levelticket['raiseticket'] = array('Raise Ticket', 'ticket/add', 'fa-ticket');
                                $multi_levelticket['ticket'] = array('Ticket History', 'ticket/my', 'fa-ticket');
                                $multi_levelticket['assignticket'] = array('Assign Tickets', 'ticket/assigned', 'fa-comment');
                                $menu['tickets'] = array('Tickets', '#', 'fa-file', $multi_levelticket);
                                if ($u_type == 'Project Leader') {
                                    $menu['dependency'] = array('Weekly Work + Dependency', 'home/dependency', 'fa-hand-o-right');
                                    $menu['dependencies'] = array('All Dependencies', 'home/dependencies', 'fa-hand-o-right');
                                    if ($u_type == 'Project Leader' && ($view_data['admin_session']['u_app_auth'] ?? '0') == '1') {
                                        $menu['usertask'] = array('Aashir Connect', 'usertask', 'fa-tasks');
                                    }
                                } elseif (($view_data['admin_session']['u_app_auth'] ?? '0') == '1') {
                                    if ($u_type != 'Associate User') {
                                        $menu['conferences'] = array('Conferences', 'conference', 'fa-tasks');
                                        $menu['schedules'] = array('Schedules', 'schedule', 'fa-bell');
                                        $menu['company'] = array('Companies', 'company', 'fa-tasks');
                                    }
                                    $menu['companyuser'] = array('Associate Users', 'companyuser', 'fa-users');
                                    $menu['usertask'] = array('Aashir Connect', 'usertask', 'fa-tasks');
                                }
                            }
                        }
                        //$menu[] = array('Dashboard', 'wcadmin/', 'fa-dashboard');
                        /*
                                $multi_level = array();
                                $multi_level['change_password'] = array('Change Password', 'spco/change_password', 'fa-key');
                                $menu['setting'] = array('Settings', '#', 'fa-cogs', $multi_level);
                                */

                        $menu['signout'] = array('Sign out', 'home/logout', 'fa-sign-out');


                        ?>


                        <?php
                        // Recursive function to render nested menu levels
                        function render_menu_recursive($items, $current_page)
                        {
                            echo '<ul class="treeview-menu">';
                            foreach ($items as $item) {
                                $label = $item[0];
                                $url = $item[1];
                                $icon = $item[2];
                                $has_sub = isset($item[3]) && is_array($item[3]);

                                // Active check
                                $active = ($url == $current_page) ? ' active' : '';
                                if ($has_sub) {
                                    foreach ($item[3] as $subitem) {
                                        if ($subitem[1] == $current_page) {
                                            $active = ' active';
                                            break;
                                        }
                                    }
                                }

                                if ($has_sub) {
                                    echo '<li class="treeview' . $active . '">';
                                    echo '<a href="#"><i class="fa ' . $icon . '"></i> <span>' . $label . '</span><i class="fa fa-angle-left pull-right"></i></a>';
                                    render_menu_recursive($item[3], $current_page);
                                    echo '</li>';
                                } else {
                                    echo '<li' . $active . '>';
                                    echo '<a href="' . base_url($url) . '"><i class="fa ' . $icon . '"></i> ' . $label . '</a>';
                                    echo '</li>';
                                }
                            }
                            echo '</ul>';
                        }

                        // Detect current page
                        $request = \Config\Services::request();
                        $uri = $request->getUri();
                        $segments = $uri->getSegments();
                        $current_page = ($segments[0] ?? '') . "/" . ($segments[1] ?? '');
                        if (isset($segments[2])) {
                            $current_page .= "/" . $segments[2];
                        }

                        // Render full menu
                        foreach ($menu as $item) {
                            $label = $item[0];
                            $url = $item[1];
                            $icon = $item[2];
                            $has_sub = isset($item[3]) && is_array($item[3]);

                            // Active check for main menu
                            $active = ($url == $current_page) ? ' active' : '';
                            if ($has_sub) {
                                foreach ($item[3] as $subitem) {
                                    if ($subitem[1] == $current_page) {
                                        $active = ' active';
                                        break;
                                    }
                                    // Check deep nested children
                                    if (isset($subitem[3])) {
                                        foreach ($subitem[3] as $deepitem) {
                                            if ($deepitem[1] == $current_page) {
                                                $active = ' active';
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($has_sub) {
                                echo '<li class="treeview' . $active . '">';
                                echo '<a href="#"><i class="fa ' . $icon . '"></i> <span>' . $label . '</span><i class="fa fa-angle-left pull-right"></i></a>';
                                render_menu_recursive($item[3], $current_page);
                                echo '</li>';
                            } else {
                                echo '<li' . $active . '>';
                                echo '<a href="' . base_url($url) . '"><i class="fa ' . $icon . '"></i> <span>' . $label . '</span></a>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
        <?php } ?>
        <div class="content-wrapper">
            <div class="clearfix"></div>
            <?php // echo isset($this->admin_session['Name'])?$this->admin_session['Name']."<br/>".$this->admin_session['Zonename']:'';
            ?>
            <?php
            if (isset($view_data['error'])) {
            ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                    <?php echo $view_data['error'] ?>
                </div>
            <?php
            }
            ?>
            <div id="messageContainer">
                <?php
                $session = session();
                if (!empty($session->get('messages'))) {
                    echo "<div style='margin:10px 20px;'>";
                    foreach ($session->get('messages') as $message) {
                        if ($message['conference_message'] == 'Yes' || $message['task_message'] == 'Yes')
                            echo '<div class="alert alert-dismissible" style="background-color:#ebc634;">';
                        else
                            echo '<div class="alert alert-success alert-dismissible">';
                        echo '<button type="button" class="close reset_me" data-dismiss="alert" aria-hidden="true" data-id="' . $message['me_id'] . '">×</button>';
                        if ($message['leave_message'] == 'No')
                            echo '<h4><i class="icon fa fa-envelope"></i> ' . $message['p_name'] . '</h4>';
                        else
                            echo '<h4><i class="icon fa fa-envelope"></i> Leave Approval Message </h4>';

                        echo $message['me_text'] . '</div>';
                    }
                    echo "</div>";
                    //$this->session->unset_userdata('messages');
                }
                ?>
            </div>
            <?php echo view($view_data['page'], array('view_data' => $view_data)); ?>


        </div>
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
            </div>
            <strong>Copyright &copy; <?php echo date("Y") ?> <a href="#"><?php echo "Aashir Engineering" ?></a>.</strong>
            All rights reserved.
        </footer>
        <div class="control-sidebar-bg"></div>
    </div>
    <div class="loading"><i class="fa fa-gear fa-spin fa-2x"></i></div>
    <div class="modal fade" id="myModalView" role="dialog" style="display: none;">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo base_url('assets/admin/addons/jQuery/jQuery-2.2.0.min.js'); ?>"></script>
    <link href="<?php echo base_url('assets/admin/addons/select2/select2.min.css'); ?>" rel="stylesheet">
    <script src="<?php echo base_url('assets/admin/addons/select2/select2.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/addons/select2/common-select2.js'); ?>"></script>
       <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
    <script src="<?php echo base_url('assets/admin/addons/bootstrap/js/bootstrap.min.js'); ?>"></script>
    <?php foreach ($view_data['extra_foot_js'] as $file) { ?>
        <script src="<?php echo base_url($file); ?>"></script>
    <?php } ?>
    <script src="<?php echo base_url('assets/admin/js/app.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/common/js/utility.js?5'); ?>"></script>
    <script>
        $(document).ready(function() {
            initProjectSelect2('.project-select');
        });
        <?php
        $data = $session->getFlashdata('data');
        if (is_array($data) && count($data) > 0) {
            switch ($data['type']) {
                case 'danger':
                    $title = 'Error!';
                    break;
                case 'success':
                    $title = 'Success!';
                    break;
                case 'warning':
                    $title = 'Alert!';
                    break;
            }
            echo "showModal('ok', '{$data['value']}', '{$title}', 'modal-{$data['type']}','modal-sm')";
        }
        ?>
        jQuery(".reset_me").click(function() {
            doAjax('api/messages', 'post', {
                "act": "read",
                "me_id": $(this).attr("data-id")
            }, function(res) {
                if (res.status == "pass") {} else {}
            });
        });

        // setInterval(function() {
        //     $.ajax({
        //         url: '<?php echo site_url('Home/fetchMessages'); ?>',
        //         method: 'GET',
        //         dataType: 'json',
        //         success: function(messages) {
        //             if (messages && messages.length > 0) {
        //                 let html = '<div style="margin:10px 20px;">';
        //                 messages.forEach(function(message) {
        //                     if (message.conference_message === 'Yes' || message.task_message === 'Yes') {
        //                         html += '<div class="alert alert-dismissible" style="background-color:#ebc634;">';
        //                     } else {
        //                         html += '<div class="alert alert-success alert-dismissible">';
        //                     }
        //                     html += `<button type="button" class="close reset_me" data-dismiss="alert" aria-hidden="true" data-id="${message.me_id}">×</button>`;

        //                     if (message.leave_message === 'No') {
        //                         html += `<h4><i class="icon fa fa-envelope"></i> ${message.p_name}</h4>`;
        //                     } else {
        //                         html += `<h4><i class="icon fa fa-envelope"></i> Leave Approval Message </h4>`;
        //                     }
        //                     html += `${message.me_text}</div>`;
        //                 });
        //                 html += '</div>';

        //                 $('#messageContainer').html(html);
        //             } else {

        //                 $('#messageContainer').html('');
        //             }
        //         },
        //         error: function() {
        //             console.error('Failed to fetch messages.');
        //         }
        //     });
        // }, 6000); // 10 minutes = 600,000 milliseconds


        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }

        function showDesktopNotification(title, message, payload) {
            if (Notification.permission === "granted") {
                const notification = new Notification(title, {
                    body: message,
                    icon: 'https://cdn-icons-png.flaticon.com/512/1827/1827272.png' // Optional, set the notification icon
                });

                notification.onclick = function() {

                    if (!payload || !payload.screen_name) return;

                    switch (payload.screen_name) {
                        case 'Task':
                            window.open('<?= base_url("usertask/view/") ?>' + payload.id, '_blank');
                            break;
                        case 'Schedule':
                            window.open('<?= base_url("schedule/view/") ?>' + payload.id, '_blank');
                            break;
                        case 'Conference':
                            window.open('<?= base_url("conference/view/") ?>' + payload.id, '_blank');
                            break;
                        case 'Ticket':
                            window.open('<?= base_url("ticket/view/") ?>' + payload.id, '_blank');
                            break;
                        case 'Dependency':
                            window.open('<?= base_url("home/dependencies") ?>', '_blank');
                            break;
                        default:
                            console.warn('Unknown screen_name:', payload.screen_name);
                    }
                    // Example: Redirect to task page if payload.screen_name is 'Task'
                    // if (payload && payload.screen_name === 'Task') {
                    //     window.open('/usertask/view/' + payload.id, '_blank');
                    // }
                };
            }
        }
        if (!window.location.pathname.endsWith('login')) {
            setInterval(function() {
                //console.log("Checking for new notifications...");
                $.ajax({
                    url: '<?php echo site_url("AssociateUserTaskController/fetchDesktopNotifications"); ?>',
                    method: 'GET',
                    dataType: 'json',
                    success: function(notifications) {
                        //console.log("Notifications received: ", notifications);
                        if (notifications && notifications.length > 0) {
                            notifications.forEach(function(note) {
                                // Show each notification
                                showDesktopNotification(note.title, note.message, JSON.parse(note.payload));
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error fetching desktop notifications:', textStatus, errorThrown);
                    }
                });
            }, 7000); // Poll every 7 seconds
        }
    </script>
</body>

</html>