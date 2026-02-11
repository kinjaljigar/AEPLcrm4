<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Home extends CI_Controller
{
	protected $open_methods = array('login', 'logout', 'forget');
	protected $master_methods = array('report_employee_salary');
	protected $project_methods = array('index', 'projects', 'employees', 'report_leave', 'report_dependency', 'report_timesheet', 'report_daily', 'report_employee', 'report_estimated_actual');
	//protected $project_methods = array();
	protected $team_methods = array();
	protected $admin_session;

	function __construct()
	{
		parent::__construct();

		date_default_timezone_set('Asia/Kolkata');
		$method = strtolower($this->router->fetch_method());

		//$this->prefix = $this->config->item('table_prefix');

		if (in_array($method, $this->open_methods)) {
		} else {

			if (isset($this->session))
				$this->admin_session = $this->session->userdata('admin_session');

			if (isset($this->admin_session['u_id']) && $this->admin_session['u_id'] > 0) {
				if (in_array($method, $this->project_methods)) {
					if (($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') || ($this->admin_session['u_type'] == 'Project Leader' && $method == 'index')) {
					} else {
						redirect(base_url('home/tasks'));
						return;
					}
				}
				if (in_array($method, $this->master_methods)) {
					if ($this->admin_session['u_type'] == 'Master Admin') {
					} else {
						redirect(base_url('home/tasks'));
						return;
					}
				}
				$this->load->model('message_model');
				$params = array();
				$params['u_id'] = $this->admin_session['u_id'];
				$params['conditions'] = array(array('mu_read' => 0));
				$messages = $this->message_model->get_records($params);
				$this->session->set_userdata(['messages' => $messages]);
			} else {
				redirect(base_url('home/login'));
				return;
			}
		}
	}

	public function index()
	{
		$this->view_data['page'] = 'index';
		$this->view_data['meta_title'] = 'Dashboard';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function task($act, $p_id, $t_id = 0, $parent = 0)
	{
		$today = date("Y-m-d");
		$this->load->model(array('project_model', 'user_model'));
		$this->load->library(array('general'));
		$project = $this->project_model->get_project_by_id($p_id);
		$task = null;
		$params = array();
		//$params['params'] = array(array('u_type' => 'Project Leader'), array('u_type' => 'Employee'));
		$params['newcondition'] = 'u_status = "Active" AND (u_type = "Project Leader" OR u_type = "Employee")';  // For Both OR and AND together
		$employees = $this->user_model->get_records($params);
		$active_projects = array();
		$active_tasks = array();
		$leaves = array();
		$data = $this->db->query("SELECT count(DISTINCT(p_id)) as total, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  GROUP BY u_id")->result_array();
		foreach ($data as $val) {
			$active_projects[$val['u_id']] = $val['total'];
		}
		$data = $this->db->query("SELECT count(DISTINCT(t_id)) as total, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id LEFT OUTER JOIN aa_tasks T ON TU.tu_t_id = T.t_id WHERE TU.tu_removed = 'No' AND (T.t_status = 'New' OR T.t_status = 'Inprogress') AND (P.p_status = 'Active' OR P.p_status = 'New')  GROUP BY u_id")->result_array();
		foreach ($data as $val) {
			$active_tasks[$val['u_id']] = $val['total'];
		}
		$data = $this->db->query("SELECT u_id, L.l_from_date, L.l_to_date FROM aa_users U LEFT OUTER JOIN aa_leaves L ON U.u_id = L.l_u_id WHERE L.l_status = 'Approved' AND (l_from_date >= '{$today}' || l_to_date >= '{$today}')")->result_array();
		foreach ($data as $val) {
			$leaves[$val['u_id']][] = convert_db2display($val['l_from_date']) . " to " . convert_db2display($val['l_to_date']);
		}
		$this->view_data['act'] = 'add';
		if (isset($project[0])) {
			if ($t_id > 0 && ($act == "edit" || $act == "view" || $act == "sub")) {
				$this->load->model('task_model');
				$criteria = array();
				$criteria['conditions'] = array(array('t_id' => $t_id));
				$task = $this->task_model->get_records($criteria);
				if (count($task) == 1) {
					$task = $task[0];
					$this->view_data['act'] = $act;
				} else {
					redirect(base_url('home/tasks') . "?NoTask");
					return;
				}
				if ($act == "view") {
					$files = $this->task_model->get_files(array('tf_t_id' => $t_id));
					$task['files'] = $files;
					$assigns = $this->task_model->get_assigns($t_id);
					$task['assigns'] = $assigns;
					$task['team'] = $this->project_model->get_project_team($p_id);
				}
				if ($act == "sub") {
					if ($task['t_parent'] != 0) {
						redirect(base_url('home/tasks') . "?NoTSub");
						return;
					}
				}
			}
			if ($t_id > 0 && $act == "add") {
				$this->load->model('task_model');
				$criteria = array();
				$criteria['conditions'] = array(array('t_id' => $t_id));
				$task = $this->task_model->get_records($criteria);
				if (count($task) == 1) {
					$task = $task[0];
					$this->view_data['act'] = $act;
				} else {
					redirect(base_url('home/tasks'));
					return;
				}
			}
			$return_url = base_url('home/tasks');
			if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "")
				$return_url = $_SERVER['HTTP_REFERER'];

			if ($act == "view") {
				if (!$this->general->ValidateTaskAssignment($t_id, $this->admin_session)) {
					redirect(base_url('home/tasks') . "?NoAccess");
					return;
				}
			}
			if ($act == "add" || $act == "edit") {
				if ($act == "add") {
					$my_t_id = 0;
					$my_p_id = $t_id;
				} else {
					$my_t_id = $t_id;
					$my_p_id = $task['t_parent'];
				}

				$error = $this->general->ValidateTaskAddEdit($my_t_id, $my_p_id, $this->admin_session);
				if ($error) {
					redirect(base_url('home/tasks') . "?NoAccess");
					return;
				}
			}
			$project = $project[0];
			$this->view_data['return_url'] = $return_url;
			$this->view_data['p_id'] = $p_id;
			$this->view_data['project'] = $project;
			$this->view_data['task'] = $task;
			$this->view_data['t_id'] = $t_id;
			$this->view_data['t_p_id'] = $parent;
			$this->view_data['employees'] = $employees;
			$this->view_data['active_projects'] = $active_projects;
			$this->view_data['active_tasks'] = $active_tasks;
			$this->view_data['leaves'] = $leaves;
			$this->view_data['priorities'] = $this->config->item('priorities');
			$this->view_data['page'] = (($act == "view") ? 'view_task' : (($act == "sub") ? 'sub' : 'task'));
			$this->view_data['meta_title'] = 'Manage Task';
			$this->view_data['admin_session'] = $this->admin_session;
			$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
			$this->load->view("template", array('view_data' => $this->view_data));
		} else {
			redirect(base_url('home/index'));
			return;
		}
	}
	public function download($type = "task", $id)
	{
		$this->load->model(array('task_model'));
		//[PENDING] Validation
		if ($type == "task") {
			$result = $this->task_model->get_files(array('tf_id' => $id));
			if (count($result) == 1) {
				$result = $result[0];
				download_file($type, $result);
			} else {
				redirect(base_url('home/index'));
				return;
			}
		} else if ($type == "tm") {
			$result = $this->task_model->get_task_message_single($id);
			if (count($result) == 1) {
				$result = $result[0];
				download_file($type, $result);
			} else {
				redirect(base_url('home/index'));
				return;
			}
		}
	}
	public function project_contacts($p_id = null)
	{
		if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head', 'Project Leader'])) {
		} else {
			redirect(base_url('home/tasks'));
			return;
		}
		$this->load->model('project_model');
		$project = $this->project_model->get_project_by_id($p_id);
		if (isset($project[0])) {
			$project = $project[0];
		}
		$this->view_data['p_id'] = $p_id;
		$this->view_data['project'] = $project;
		$this->view_data['page'] = 'project_contacts';
		$this->view_data['meta_title'] = 'Project Contacts';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function project_detail($p_id = null)
	{
		$this->load->model('project_model');
		$project = $this->project_model->get_project_by_id($p_id);
		if (isset($project[0])) {
			$project = $project[0];
		}
		$this->view_data['p_id'] = $p_id;
		$this->view_data['project'] = $project;
		$this->view_data['page'] = 'project_detail';
		$this->view_data['meta_title'] = 'Project Detail:: ' . $project['p_name'];
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function projects()
	{
		$this->view_data['p_cat'] = $this->config->item('p_cat');
		$this->view_data['page'] = 'projects';
		$this->view_data['meta_title'] = 'Projects';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function timesheet()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$this->view_data['rpt_start'] = "01-04-" . $year;
		$this->view_data['rpt_end'] = date("d-m-Y");
		$at_days_back = $this->config->item('at_days_back');
		$this->view_data['at_days_back'] = $at_days_back;
		$this->view_data['page'] = 'timesheet';
		$this->view_data['meta_title'] = 'Timesheet';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function tasks()
	{
		$this->load->model("project_model");
		$params = array();
		$params['sort_by'] = "p_name";
		if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head'])) {
			$params['newcondition'] = array('p_status' => 'Active');
			$projects = $this->project_model->get_records($params);

			$this->load->model("user_model");
			$params = array();
			$params['condition'] = array('u_type' => 'Project Leader', 'u_type' => 'Employee');
			$params['newcondition'] = array('u_status' => 'Active');
			$users = $this->user_model->get_records($params);
			$this->view_data['users'] = $users;
		} else {
			$u_id = $this->admin_session['u_id'];
			$projects = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
		}
		$this->view_data['projects'] = $projects;

		$this->view_data['u_type'] = $this->admin_session['u_type'];
		$this->view_data['page'] = 'tasks';
		$this->view_data['meta_title'] = 'Tasks';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function employees()
	{
		$this->view_data['page'] = 'employees';
		$this->view_data['meta_title'] = 'Employees';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function holidays()
	{
		$this->view_data['page'] = 'holidays';
		$this->view_data['meta_title'] = 'Holidays';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function messages()
	{
		$this->view_data['page'] = 'messages';
		$this->view_data['meta_title'] = 'Messages';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function leaves()
	{
		$this->view_data['page'] = 'leaves';
		$this->view_data['meta_title'] = 'Leaves';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function settings()
	{
		$this->view_data['page'] = 'settings';
		$this->view_data['meta_title'] = 'Settings';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
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
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_leave_total()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$this->view_data['rpt_start'] = $year;
		$this->view_data['rpt_end'] = $year + 1;
		$this->view_data['page'] = 'report_leave_total';
		$this->view_data['meta_title'] = 'Leave Total Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
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
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_project()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$this->view_data['rpt_start'] = "01-04-" . $year;
		$this->view_data['rpt_end'] = date("d-m-Y");
		$this->load->model("project_model");
		$params = array();
		$params['sort_by'] = "p_name";
		if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head'])) {
			$projects = $this->project_model->get_records($params);

			$this->load->model("user_model");
			$params = array();
			$params['condition'] = array('u_type' => 'Project Leader', 'u_type' => 'Employee');
			$users = $this->user_model->get_records($params);
			$this->view_data['users'] = $users;
		} else {
			$u_id = $this->admin_session['u_id'];
			$projects = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
		}
		$this->view_data['projects'] = $projects;

		$this->view_data['u_type'] = $this->admin_session['u_type'];
		$this->view_data['page'] = 'report_projects';
		$this->view_data['page_title'] = 'Project Report';
		$this->view_data['meta_title'] = 'Project Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_project_employee()
	{
		$this->load->model("project_model");
		$params = array();
		$params['sort_by'] = "p_name";
		if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head'])) {
			$params['newcondition'] = array('p_status' => 'Active');
			$projects = $this->project_model->get_records($params);

			$this->load->model("user_model");
			$params = array();
			$params['condition'] = array('u_type' => 'Project Leader', 'u_type' => 'Employee');
			$params['newcondition'] = array('u_status' => 'Active');
			$users = $this->user_model->get_records($params);
			$this->view_data['users'] = $users;
		} else {
			$u_id = $this->admin_session['u_id'];
			$projects = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
		}
		$this->view_data['projects'] = $projects;

		$this->view_data['u_type'] = $this->admin_session['u_type'];
		$this->view_data['page'] = 'report_projects_employee';
		$this->view_data['meta_title'] = 'Project Employee Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	function report_profitloss()
	{
		$this->view_data['p_cat'] = $this->config->item('p_cat');
		$this->view_data['page'] = 'report_profitloss';
		$this->view_data['page_title'] = 'Profit/Loss Report';
		$this->view_data['meta_title'] = 'Profit/Loss Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true, 'datepicker' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_dependency()
	{
		$this->load->model("project_model");
		$params = array();
		$projects = $this->project_model->get_records($params);
		$this->view_data['projects'] = $projects;
		$this->view_data['page'] = 'report_dependency';
		$this->view_data['meta_title'] = 'Dependency';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_timesheet()
	{
		$rpt_start = $this->input->post('rpt_start');
		$rpt_end = $this->input->post('rpt_end');
		$u_name = $this->input->post('u_name');
		$u_id = $this->input->post('u_id');
		$type = $this->input->post('type');
		$this->view_data['rpt_start'] = $rpt_start;
		$this->view_data['rpt_end'] = $rpt_end;
		$this->view_data['type'] = $type;
		$this->view_data['u_id'] = $u_id;
		$this->view_data['page'] = 'report_timesheet';
		$this->view_data['page_title'] = 'Daily Report';
		$this->view_data['page_title'] = (($type == "daily") ? "Daily Report: " : "Employee Work Report: ") . $u_name . " | " . (($type == "daily") ? $rpt_start : $rpt_start . " to " . $rpt_end);
		$this->view_data['meta_title'] = (($type == "daily") ? "Daily Report Detail" : "Employee Work Report Detail");
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_daily()
	{
		$this->view_data['rpt_start'] = $_REQUEST['rpt_start'] ?? date("d-m-Y");
		$this->view_data['rpt_end'] = date("d-m-Y");
		$this->view_data['type'] = 'daily';
		$this->view_data['page'] = 'report_attendance';
		$this->view_data['page_title'] = 'Daily Report';
		$this->view_data['meta_title'] = 'Daily Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_employee()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$this->view_data['rpt_start'] = $_REQUEST['rpt_start'] ?? "01-04-" . $year;
		$this->view_data['rpt_end'] = $_REQUEST['rpt_end'] ?? date("d-m-Y");
		$this->view_data['type'] = 'employee';
		$this->view_data['page'] = 'report_attendance';
		$this->view_data['page_title'] = 'Employee Work Report';
		$this->view_data['meta_title'] = 'Employee Work Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_employee_salary()
	{
		$this->view_data['type'] = 'employee';
		$this->view_data['page'] = 'report_employee_salary';
		$this->view_data['page_title'] = 'Employee Salary Report';
		$this->view_data['meta_title'] = 'Employee Salary Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}

	public function report_estimated_actual()
	{
		$this->view_data['page'] = 'report_estimated_actual';
		$this->view_data['meta_title'] = 'Estimated Hours v/s Actual Hours';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function login()
	{
		$this->view_data['page'] = 'login';
		$this->view_data['meta_title'] = 'Login';
		$this->view_data['plugins'] = array('form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function logout()
	{
		$this->session->unset_userdata('admin_session');
		$this->session->unset_userdata('messages');
		redirect(base_url('home/login'));
		return;
	}
}