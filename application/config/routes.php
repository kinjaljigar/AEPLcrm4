<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['conference'] = 'ConferenceController/listView';
$route['conference/edit/(:num)'] = 'ConferenceController/edit/$1';
$route['conference/update/(:num)'] = 'ConferenceController/update/$1';
$route['conference/delete/(:num)'] = 'ConferenceController/delete/$1';
$route['conference/add'] = 'ConferenceController/add';
$route['conference/addData'] = 'ConferenceController/addData';
$route['conference/view/(:num)'] = 'ConferenceController/view/$1';

$route['schedule'] = 'ScheduleController/listView';
$route['schedule/edit/(:num)'] = 'ScheduleController/edit/$1';
$route['schedule/update/(:num)'] = 'ScheduleController/update/$1';
$route['schedule/delete/(:num)'] = 'ScheduleController/delete/$1';
$route['schedule/add'] = 'ScheduleController/add';
$route['schedule/addData'] = 'ScheduleController/addData';
$route['schedule/view/(:num)'] = 'ScheduleController/view/$1';
$route['meeting/getProjectUsers/(:num)'] = 'ScheduleController/getProjectUsers/$1';

$route['company'] = 'CompanyController/list';
$route['company/edit/(:num)'] = 'CompanyController/edit/$1';
$route['company/update/(:num)'] = 'CompanyController/update/$1';
$route['company/delete/(:num)'] = 'CompanyController/delete/$1';
$route['company/add'] = 'CompanyController/add';
$route['company/addData'] = 'CompanyController/addData';


$route['companyuser'] = 'CompanyController/listUser';
$route['companyuser/edit/(:num)'] = 'CompanyController/editUser/$1';
$route['companyuser/update/(:num)'] = 'CompanyController/updateUser/$1';
$route['companyuser/delete/(:num)'] = 'CompanyController/deleteUser/$1';
$route['companyuser/add'] = 'CompanyController/addUser';
$route['companyuser/addData'] = 'CompanyController/addDataUser';


$route['usertask'] = 'AssociateUserTaskController/list';
$route['usertask/edit/(:num)'] = 'AssociateUserTaskController/edit/$1';
$route['usertask/view/(:num)'] = 'AssociateUserTaskController/view/$1';
$route['usertask/update/(:num)'] = 'AssociateUserTaskController/update/$1';
$route['usertask/status/(:num)'] = 'AssociateUserTaskController/status/$1';
$route['usertask/delete/(:num)'] = 'AssociateUserTaskController/delete/$1';
$route['usertask/add'] = 'AssociateUserTaskController/add';
$route['usertask/addData'] = 'AssociateUserTaskController/addData';
$route['usertask/fetchTasks'] = 'AssociateUserTaskController/fetchTasks';


$route['ticket-category'] = 'TicketCategoryController/index';
$route['ticket-category/add'] = 'TicketCategoryController/create';
$route['ticket-category/store'] = 'TicketCategoryController/store';
$route['ticket-category/edit/(:num)'] = 'TicketCategoryController/edit/$1';
$route['ticket-category/update/(:num)'] = 'TicketCategoryController/update/$1';
$route['ticket-category/delete/(:num)'] = 'TicketCategoryController/delete/$1';


//$route['ticket'] = 'TicketController/index';
$route['ticket/my'] = 'TicketController/my_tickets';
$route['ticket/add'] = 'TicketController/create';
$route['ticket/store'] = 'TicketController/store';
$route['ticket/delete/(:num)'] = 'TicketController/delete/$1';
$route['ticket/deleteassign/(:num)'] = 'TicketController/deleteassign/$1';
$route['ticket/view/(:num)'] = 'TicketController/view/$1';
$route['ticket/close/(:num)'] = 'TicketController/close/$1';
$route['ticket/add_message/(:num)'] = 'TicketController/add_message/$1';
$route['ticket/assigned'] = 'TicketController/assigned_tickets';
$route['ticket/get_child_categories_ajax'] = 'TicketController/get_child_categories_ajax';
