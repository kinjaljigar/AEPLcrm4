<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Home extends CI_Controller
{
	protected $open_methods = array('login', 'logout', 'forget');
	protected $master_methods = array('report_employee_salary');
	protected $project_methods = array('index', 'projects', 'employees', 'report_leave', 'report_dependency', 'report_timesheet', 'report_daily', 'report_leader_employee', 'report_employee', 'report_estimated_actual', 'conference', 'company', 'companyuser', ' usertask');
	//protected $project_methods = array();
	protected $team_methods = array();
	protected $admin_session;
	protected $token;
	protected $cliBaseUrl;
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
			$this->token = $this->session->userdata('token');
			$this->cliBaseUrl = config_item('cli_base_url');

			if (isset($this->admin_session['u_id']) && $this->admin_session['u_id'] > 0) {
				if (in_array($method, $this->project_methods)) {
					// Check project method access using centralized authorization
					$has_access = $this->authorization->is_project_leader_or_higher($this->admin_session) ||
					              ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Project Leader', 'TaskCoordinator']) && $method == 'index');
					if (!$has_access) {
						redirect(base_url('home/tasks'));
						return;
					}
				}
				if (in_array($method, $this->master_methods)) {
					// Check master method access - Master Admin or Super Admin only
					if (!$this->authorization->is_admin($this->admin_session)) {
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
		$url = 'conference/list?page=1&limit=5&data=upcoming';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->token,
		]);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			// Handle error
			$error_message = curl_error($ch);
			log_message('error', 'cURL error: ' . $error_message);
			show_error('An error occurred while fetching data.');
		}
		curl_close($ch);
		$data['conferences'] = json_decode($response, true);
		$this->view_data['conferences'] = $data['conferences'];


		$url = 'schedule/list?page=1&limit=5&data=upcoming&type=mydata';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->token,
		]);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			// Handle error
			$error_message = curl_error($ch);
			log_message('error', 'cURL error: ' . $error_message);
			show_error('An error occurred while fetching data.');
		}
		curl_close($ch);
		$data['schedules'] = json_decode($response, true);
		$this->view_data['schedules'] = $data['schedules'];


		$url = 'task/list?page=1&limit=5&data=upcoming';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->token,
		]);
		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			// Handle error
			$error_message = curl_error($ch);
			log_message('error', 'cURL error: ' . $error_message);
			show_error('An error occurred while fetching data.');
		}
		curl_close($ch);
		$data = json_decode($response, true);
		$this->view_data['tasks'] = $data;

		$leader_id = $this->input->get('leader_id');
		$this->load->model('Weeklywork_model');
		//$weekly_works = $this->Weeklywork_model->get_all();
		$weekly_works = $this->Weeklywork_model->getWeeklyWorkHome($leader_id, null, null, 5, 0, 'DESC');
		$this->view_data['weekly_works'] = $weekly_works;

		$this->load->model('User_model');
		$leaders = $this->User_model->get_all_leaders('Active');
		$this->view_data['leaders'] = $leaders;

		$this->view_data['dataURL'] = 'upcoming';
		$this->view_data['page'] = 'index';
		$this->view_data['meta_title'] = 'Dashboard';
		$this->view_data['admin_session'] = $this->admin_session;


		$this->load->model('Dependency_model');
		$dependencies = $this->Dependency_model->get_latest_dependencies($this->admin_session['u_id'], $this->admin_session['u_type'], 10);
		$this->view_data['dependencies'] = $dependencies;

		$this->load->model('project_message_model');
		$criteria = array();
		$criteria['u_id'] = $this->admin_session['u_id'];
		$criteria['filter_date'] = date("Y-m-d");
		$criteria['offset'] = 0;
		$criteria['limit'] = 50;
		$todaysmessages = $this->project_message_model->get_list($criteria);
		$this->view_data['todaysmessages'] = $todaysmessages;
		

		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function presentlist()
	{
		// Using centralized authorization - cleaner and reusable
		$this->authorization->require_roles($this->admin_session, ['Master Admin', 'Bim Head', 'Super Admin']);

		$this->view_data['page'] = 'presentlist';
		$this->view_data['meta_title'] = 'Employee Present Departmentwise';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
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
	public function download($type, $id)
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
		// Using centralized authorization - Project Leaders and above can access
		$this->authorization->require_roles($this->admin_session, ['Master Admin', 'Bim Head', 'Project Leader', 'Super Admin']);

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
		$this->load->model("user_model");
		$params = array();
		$params['newcondition'] = array(
			'u_type'   => 'Project Leader',
			'u_status' => 'Active'
		);
		$params['select_list'] = 'u_id,u_name';
		$users = $this->user_model->get_records($params);
		$this->view_data['p_leader'] = $users;


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
	public function dependency()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$this->view_data['rpt_start'] = "01-04-" . $year;
		$this->view_data['rpt_end'] = date("d-m-Y");
		$at_days_back = $this->config->item('at_days_back');
		$this->view_data['at_days_back'] = $at_days_back;
		$this->load->model('Weeklywork_model');
		$this->view_data['projects'] = $this->Weeklywork_model->get_assigned_projects($this->admin_session['u_id']);
		$employees = [];
		$employees =  $this->db->where('u_leader', $this->admin_session['u_id'])
                    ->get('aa_users')
                    ->result_array();
		$this->view_data['employees'] = $employees;
		$this->view_data['page'] = 'dependency';
		$this->view_data['meta_title'] = 'Project Dependency';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true, 'select2' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function projectData()
	{
		// Using centralized authorization - Bim Head or Master Admin only
		$this->authorization->require_roles($this->admin_session, ['Master Admin', 'Bim Head'], 'home');

		$session = $this->admin_session;
		if ($this->authorization->is_bim_head_or_higher($session)) {
			$this->view_data['projects'] = $this->db->select('p_id, p_name')->from('aa_projects')->get()->result_array();
		}
		if ($this->authorization->is_role_allowed($session['u_type'], ['Project Leader'])) {
			$this->load->model('Weeklywork_model');
			$this->view_data['projects'] = $this->Weeklywork_model->get_assigned_projects($this->admin_session['u_id']);
		}

		$this->load->model('Weeklywork_model');
		$this->load->model('User_model');

		$leaders = $this->User_model->get_all_leaders();
		$this->view_data['leaders'] = $leaders;
		$leader_id = $this->input->get('leader_id');
		$from_date = $this->input->get('from_date');
		$to_date = $this->input->get('to_date');

		$this->view_data['weekly_works'] = $this->Weeklywork_model->getWeeklyWork($leader_id, $from_date, $to_date, null, 0, 'ASC');
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['page'] = 'dependency/project_data';
		$this->view_data['meta_title'] = 'Project Data';
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}

	public function dependencies()
	{
		$session = $this->admin_session;
		if ($this->authorization->is_bim_head_or_high_taskcoordinator($session)) {
			$this->view_data['projects'] = $this->db->select('p_id, p_name, p_number')->from('aa_projects')->get()->result_array();
		}
		if ($this->authorization->is_role_allowed($session['u_type'], ['Project Leader'])) {
			$this->load->model('Weeklywork_model');
			$this->view_data['projects'] = $this->Weeklywork_model->get_assigned_projects($this->admin_session['u_id']);
		}

		$this->view_data['leaders'] = $this->db
			->select('u_id, u_name,u_type')
			->from('aa_users')
			->where_in('u_type', ['Project Leader', 'Master Admin', 'Bim Head'])
			->where('u_status', 'Active')
			->get()
			->result_array();
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['page'] = 'dependency/dependencies';
		$this->view_data['meta_title'] = 'Dependencies';

		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function tasks()
	{
		$this->load->model("project_model");
		$params = array();
		$params['sort_by'] = "p_name";
		if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
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
			$projects = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name,P.p_number,u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
		}
		$this->view_data['projects'] = $projects;

		$this->view_data['u_type'] = $this->admin_session['u_type'];
		$this->view_data['page'] = 'tasks';
		$this->view_data['meta_title'] = 'Tasks';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true, 'select2' => true);
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
		$users = $this->db->select('u_id, u_name , u_type')
			->from('aa_users')
			->where('u_status', 1)
			->get()
			->result_array();
		$this->view_data['users'] = $users;
		$this->view_data['page'] = 'messages';
		$this->view_data['meta_title'] = 'Messages';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function empattendance()
	{
		$this->view_data['page'] = 'empattendance';
		$this->view_data['meta_title'] = 'Employee Attendance';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$at_days_back = $this->config->item('at_days_back');
		$this->view_data['at_days_back'] = $at_days_back;
		$this->load->model("user_model");
		$params = array();
		$params['condition'] = array('u_type' => 'Project Leader', 'u_type' => 'Employee');
		$users = $this->user_model->get_records($params);
		$this->view_data['users'] = $users;

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
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_leave_date()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		//$this->view_data['rpt_start'] = "01-04-" . $year;
		$this->view_data['rpt_start'] =  date('d-m-Y', strtotime("-1 days"));
		$this->view_data['rpt_end'] = date("d-m-Y");
		$this->view_data['page'] = 'report_leaves_date';
		$this->view_data['meta_title'] = 'Leave Report - Datewise';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}

	public function report_leave_total()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		// $this->view_data['rpt_start'] = $year;
		// $this->view_data['rpt_end'] = $year + 1;
		$this->view_data['rpt_start'] = "01-04-" . $year;
		$this->view_data['rpt_end'] = "31-03-" . ($year + 1);
		$this->view_data['page'] = 'report_leave_total';
		$this->view_data['meta_title'] = 'Leave Total Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_leave_hour_date()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		//$this->view_data['rpt_start'] = "01-04-" . $year;
		$this->view_data['rpt_start'] =  date('d-m-Y', strtotime("-1 days"));
		$this->view_data['rpt_end'] = date("d-m-Y");
		$this->view_data['page'] = 'report_leave_hour_date';
		$this->view_data['meta_title'] = 'Hourly Leave Report - Datewise';
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
		$this->view_data['p_cat'] = $this->config->item('p_cat');
		if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
			$projects = $this->project_model->get_records($params);

			$this->load->model("user_model");
			$params = array();
			$params['condition'] = array('u_type' => 'Project Leader', 'u_type' => 'Employee');
			$users = $this->user_model->get_records($params);
			$this->view_data['users'] = $users;
		} else {
			$u_id = $this->admin_session['u_id'];
			$projects = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name,P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
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
		if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
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
			$projects = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name,P.p_number,u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
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
		$txt_search = $this->input->post('txt_search');
		$leader_id = $this->input->post('leader_id');
		$u_name = $this->input->post('u_name');
		$u_id = $this->input->post('u_id');
		$type = $this->input->post('type');
		$this->view_data['txt_search'] = $txt_search;
		$this->view_data['rpt_start'] = $rpt_start;
		$this->view_data['rpt_end'] = $rpt_end;
		$this->view_data['type'] = $type;
		$this->view_data['u_id'] = $u_id;
		$this->view_data['leader_id'] = $leader_id;
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
		$rpt_start = $this->input->get('rpt_start');
		$txt_search = $this->input->get('txt_search');
		$this->view_data['rpt_start'] = $rpt_start ?? date("d-m-Y");
		$this->view_data['txt_search'] = $txt_search;
		$this->view_data['rpt_end'] = date("d-m-Y");
		$this->view_data['type'] = 'daily';
		$this->view_data['page'] = 'report_attendance';
		$this->view_data['page_title'] = 'Daily Report';
		$this->view_data['meta_title'] = 'Daily Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function report_leader_employee()
	{
		$today = date('d-m-Y');
		$dayOfWeek = date('w', strtotime($today)); 

		$monday = ($dayOfWeek == 0)
			? date('d-m-Y', strtotime('last monday'))
			: date('d-m-Y', strtotime('monday this week'));

		$saturday = date('d-m-Y', strtotime($monday . ' +4 days'));
		$this->view_data['rpt_start'] = $this->input->get('rpt_start') ?? $monday;
		$this->view_data['rpt_end']   = $this->input->get('rpt_end')   ?? $saturday;
		$this->view_data['txt_search'] = $this->input->get('txt_search') ?? '';
		$this->view_data['leader_id'] = $this->input->get('leader_id') ?? '';
		$this->view_data['leaders'] = $this->db
			->select('u_id, u_name')
			->from('aa_users')
			->where('u_type', 'Project Leader')
			->where('u_status', 'Active')
			->order_by('u_name', 'ASC')
			->get()
			->result();
		$this->view_data['type'] = 'leader_employee';
		$this->view_data['page'] = 'report_leader_employee';
		$this->view_data['page_title'] = 'Leader Employee Report';
		$this->view_data['meta_title'] = 'Leader Employee Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}


	public function report_employee()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$txt_search = $this->input->get('txt_search');
		$this->view_data['rpt_start'] = $this->input->get('rpt_start') ?? "01-04-" . $year;
		$this->view_data['rpt_end'] = $this->input->get('rpt_end') ?? date("d-m-Y");
		$this->view_data['txt_search'] = $txt_search;
		$this->view_data['type'] = 'employee';
		$this->view_data['page'] = 'report_attendance';
		$this->view_data['page_title'] = 'Employee Work Report';
		$this->view_data['meta_title'] = 'Employee Work Report';
		$this->view_data['admin_session'] = $this->admin_session;
		$this->view_data['plugins'] = array('datatable' => true, 'datepicker' => true, 'form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}

	public function report_attendance_employee()
	{
		$year = date("Y");
		$month = (int)date("m");
		if ($month < 4) $year--;
		$txt_search = $this->input->get('txt_search');
		$this->view_data['rpt_start'] = $this->input->get('rpt_start') ?? "01-04-" . $year;
		$this->view_data['rpt_end'] = $this->input->get('rpt_end') ?? date("d-m-Y");
		$this->view_data['txt_search'] = $txt_search;
		$this->view_data['type'] = 'employee';
		$this->view_data['page'] = 'report_attendance_employee';
		$this->view_data['page_title'] = 'Employee Attendance Report';
		$this->view_data['meta_title'] = 'Employee Attendance Report';
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
		$this->session->sess_destroy();
		$this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
		$this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
		$this->output->set_header('Pragma: no-cache');

		if (isset($_SERVER['HTTP_COOKIE'])) {
			$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			foreach ($cookies as $cookie) {
				$parts = explode('=', $cookie);
				$name = trim($parts[0]);
				setcookie($name, '', time() - 3600, '/');
				setcookie($name, '', time() - 3600, '/', $_SERVER['SERVER_NAME']);
			}
		}

		$this->view_data['page'] = 'login';
		$this->view_data['meta_title'] = 'Login';
		$this->view_data['plugins'] = array('form_validation' => true);
		$this->load->view("template", array('view_data' => $this->view_data));
	}
	public function logout()
	{
		$this->db->where('u_id', $this->session->userdata('admin_session')['u_id']);
		$this->db->update('aa_users', ['is_web_logged_in' => 0]);
		$this->session->unset_userdata('admin_session');
		$this->session->unset_userdata('messages');
		redirect(base_url('home/login'));
		return;
	}
	public function fetchMessages()
	{
		$params = [
			'u_id' => $this->session->userdata('admin_session')['u_id'],
			'conditions' => [['mu_read' => 0]]
		];
		$messages = $this->message_model->get_records($params);

		$this->session->set_userdata(['messages' => $messages]);

		echo json_encode($messages);
	}
}
