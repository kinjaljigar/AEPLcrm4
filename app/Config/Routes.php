<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Home::index');

// Home/Auth routes
$routes->get('home', 'Home::index');
$routes->get('home/index', 'Home::index');
$routes->get('home/login', 'Home::login');
$routes->post('home/login', 'Api::login');  // Use API controller for POST login
$routes->get('home/logout', 'Home::logout');

// API routes
$routes->post('api/login', 'Api::login');
$routes->post('api/dashboard', 'Api::dashboard');
$routes->post('api/tasks', 'Api::tasks');
$routes->post('api/projects', 'Api::projects');
$routes->post('api/employees', 'Api::employees');
$routes->post('api/settings', 'Api::settings');
$routes->post('api/leaves', 'Api::leaves');
$routes->post('api/holidays', 'Api::holidays');
$routes->post('api/projectmessages', 'Api::projectmessages');
$routes->post('api/tickets', 'Api::tickets');
$routes->post('api/ticket_categories', 'Api::ticket_categories');
$routes->post('api/timesheet', 'Api::timesheet');
$routes->post('api/empattendance', 'Api::empattendance');
$routes->post('api/dependency', 'Api::dependency');
$routes->post('api/weeklywork', 'Api::weeklywork');
$routes->post('api/project_contacts', 'Api::project_contacts');
$routes->post('api/reports', 'Api::reports');
$routes->post('api/messages', 'Api::messages');
$routes->post('api/message_report', 'Api::message_report');
$routes->post('api/registrations_get', 'Api::registrations_get');
$routes->post('api/registration_add_edit', 'Api::registration_add_edit');
$routes->post('api/registration_delete', 'Api::registration_delete');
$routes->get('api/drop_get', 'Api::drop_get');
$routes->post('api/drop_get', 'Api::drop_get');
$routes->get('AssociateUserTaskController/fetchDesktopNotifications', 'Api::fetchDesktopNotifications');
$routes->get('meeting/getProjectUsers/(:num)', 'Api::getProjectUsers/$1');

// Task detail routes (view/edit/sub)
$routes->get('home/task/view/(:num)/(:num)', 'Home::taskView/$1/$2');
$routes->get('home/task/edit/(:num)/(:num)', 'Home::taskEdit/$1/$2');
$routes->post('home/task/edit/(:num)/(:num)', 'Home::taskEdit/$1/$2');
$routes->get('home/task/sub/(:num)/(:num)', 'Home::taskSub/$1/$2');

// Task add route
$routes->get('home/task/add/(:num)', 'Home::taskAdd/$1');
$routes->get('home/task/add/(:num)/(:num)', 'Home::taskAdd/$1/$2');
$routes->post('home/task/add/(:num)', 'Home::taskAdd/$1');
$routes->post('home/task/add/(:num)/(:num)', 'Home::taskAdd/$1/$2');

// Other core routes
$routes->get('home/tasks', 'Home::tasks');
$routes->get('home/projects', 'Home::projects');
$routes->get('home/employees', 'Home::employees');
$routes->get('home/messages', 'Home::messages');
$routes->get('home/settings', 'Home::settings');
$routes->get('home/leaves', 'Home::leaves');
$routes->get('home/holidays', 'Home::holidays');
$routes->get('home/timesheet', 'Home::timesheet');
$routes->get('home/empattendance', 'Home::empattendance');
$routes->get('home/dependency', 'Home::dependency');
$routes->get('home/dependencies', 'Home::dependencies');
$routes->get('home/presentlist', 'Home::presentlist');
$routes->get('home/project_contacts/(:num)', 'Home::project_contacts/$1');
$routes->get('home/project_detail/(:num)', 'Home::project_detail/$1');
$routes->get('home/download/(:segment)/(:num)', 'Home::download/$1/$2');
$routes->get('home/fetchMessages', 'Home::fetchMessages');

// Report routes
$routes->get('home/report_daily', 'Home::report_daily');
$routes->get('home/report_employee', 'Home::report_employee');
$routes->get('home/report_leader_employee', 'Home::report_leader_employee');
$routes->get('home/projectData', 'Home::projectData');
$routes->get('home/report_attendance_employee', 'Home::report_attendance_employee');
$routes->get('home/report_employee_salary', 'Home::report_employee_salary');
$routes->get('home/report_dependency', 'Home::report_dependency');
$routes->get('home/report_estimated_actual', 'Home::report_estimated_actual');
$routes->get('home/report_leave_date', 'Home::report_leave_date');
$routes->get('home/report_leave', 'Home::report_leave');
$routes->get('home/report_leave_total', 'Home::report_leave_total');
$routes->get('home/report_leave_hour_date', 'Home::report_leave_hour_date');
$routes->get('home/report_leave_hour', 'Home::report_leave_hour');
$routes->get('home/report_message', 'Home::report_message');
$routes->get('home/report_project', 'Home::report_project');
$routes->get('home/report_project_employee', 'Home::report_project_employee');
$routes->get('home/report_profitloss', 'Home::report_profitloss');
$routes->post('home/report_timesheet', 'Home::report_timesheet');

// Conference and Schedule routes
$routes->get('conference', 'Conference::index');
$routes->get('conference/add', 'Conference::add');
$routes->post('conference/add', 'Conference::addData');
$routes->post('conference/addData', 'Conference::addData');
$routes->get('conference/edit/(:num)', 'Conference::edit/$1');
$routes->post('conference/update/(:num)', 'Conference::update/$1');
$routes->get('conference/delete/(:num)', 'Conference::delete/$1');
$routes->get('conference/view/(:num)', 'Conference::view/$1');

$routes->get('schedule', 'Schedule::index');
$routes->get('schedule/add', 'Schedule::add');
$routes->post('schedule/add', 'Schedule::addData');
$routes->post('schedule/addData', 'Schedule::addData');
$routes->get('schedule/edit/(:num)', 'Schedule::edit/$1');
$routes->post('schedule/update/(:num)', 'Schedule::update/$1');
$routes->get('schedule/delete/(:num)', 'Schedule::delete/$1');

$routes->get('company', 'Company::index');
$routes->get('company/add', 'Company::add');
$routes->post('company/add', 'Company::addData');
$routes->post('company/addData', 'Company::addData');
$routes->get('company/edit/(:num)', 'Company::edit/$1');
$routes->post('company/update/(:num)', 'Company::update/$1');
$routes->get('company/delete/(:num)', 'Company::delete/$1');

$routes->get('companyuser', 'CompanyUser::index');
$routes->get('companyuser/add', 'CompanyUser::add');
$routes->post('companyuser/add', 'CompanyUser::addData');
$routes->post('companyuser/addData', 'CompanyUser::addData');
$routes->get('companyuser/edit/(:num)', 'CompanyUser::edit/$1');
$routes->post('companyuser/update/(:num)', 'CompanyUser::update/$1');
$routes->get('companyuser/delete/(:num)', 'CompanyUser::delete/$1');

$routes->get('usertask', 'UserTask::index');
$routes->get('usertask/add', 'UserTask::add');
$routes->post('usertask/addData', 'UserTask::addData');
$routes->get('usertask/view/(:num)', 'UserTask::view/$1');
$routes->get('usertask/edit/(:num)', 'UserTask::edit/$1');
$routes->post('usertask/update/(:num)', 'UserTask::update/$1');
$routes->get('usertask/delete/(:num)', 'UserTask::delete/$1');
$routes->post('usertask/fetchTasks', 'UserTask::fetchTasks');
$routes->match(['get', 'post'], 'usertask/status/(:num)', 'UserTask::status/$1');

// Ticket routes
$routes->get('ticket-category', 'Ticket::category');
$routes->post('ticket-category', 'Ticket::category');
$routes->get('ticket-category/add', 'Ticket::categoryAdd');
$routes->post('ticket-category/add', 'Ticket::categoryStore');
$routes->get('ticket-category/edit/(:num)', 'Ticket::categoryEdit/$1');
$routes->post('ticket-category/update/(:num)', 'Ticket::categoryUpdate/$1');
$routes->get('ticket-category/delete/(:num)', 'Ticket::categoryDelete/$1');
$routes->post('ticket-category/store', 'Ticket::categoryStore');
$routes->post('ticket/get_child_categories_ajax', 'Ticket::get_child_categories_ajax');
$routes->get('ticket/add', 'Ticket::add');
$routes->post('ticket/add', 'Ticket::add');
$routes->post('ticket/store', 'Ticket::store');
$routes->get('ticket/view/(:num)', 'Ticket::view/$1');
$routes->post('ticket/view/(:num)', 'Ticket::view/$1');
$routes->get('ticket/my', 'Ticket::my');
$routes->post('ticket/my', 'Ticket::my');
$routes->get('ticket/assigned', 'Ticket::assigned');
$routes->post('ticket/assigned', 'Ticket::assigned');
$routes->post('ticket/add_message/(:num)', 'Ticket::add_message/$1');
$routes->get('ticket/close/(:num)', 'Ticket::close/$1');
$routes->get('ticket/delete/(:num)', 'Ticket::delete/$1');
$routes->get('ticket/deleteassign/(:num)', 'Ticket::deleteassign/$1');

// POST routes for other modules
$routes->post('conference', 'Conference::index');
$routes->post('schedule', 'Schedule::index');
$routes->post('company', 'Company::index');
$routes->post('companyuser', 'CompanyUser::index');
$routes->post('usertask', 'UserTask::index');
