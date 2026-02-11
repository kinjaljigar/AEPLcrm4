<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Api extends RestController
{

        protected $open_methods = array('login', 'logout', 'forget');
        protected $master_methods = array();
        protected $project_methods = array('projects', 'employees');
        //protected $project_methods = array();
        protected $team_methods = array();
        protected $allowed_files = array(), $allowed_files_images = array();
        protected $admin_session;

        function __construct()
        {
                // Construct the parent class
                parent::__construct();

                date_default_timezone_set('Asia/Kolkata');
                $method = strtolower($this->router->fetch_method());
                $this->allowed_files = $this->config->item('allowed_files');
                $this->allowed_files_images = $this->config->item('allowed_files_images');
                if (in_array($method, $this->open_methods)) {
                } else {
                        if (isset($this->session)) {
                                $this->admin_session = $this->session->userdata('admin_session');
                        }
                        if (isset($this->admin_session['u_id']) && $this->admin_session['u_id'] > 0) {
                                if (in_array($method, $this->project_methods)) {
                                        if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') {
                                        } else {
                                                $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                                        }
                                }
                        } else {
                                $this->response(array('status' => 'session', 'message' => 'Your session expired. Please relogin.'));
                        }
                }
        }
        public function leaves_post()
        {
                $this->load->model('leave_model');
                $act = $this->post('act');
                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head' || $this->admin_session['u_type'] == 'Project Leader') {
                        /*
                if ($act == "add" || $act == "del") {
                    $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                }*/
                } else {
                        if ($act == "Approve" || $act == "Decline") {
                                $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                        }
                }
                $l_u_id = $this->admin_session['u_id'];
                switch ($act) {
                        case "Approve":
                        case "Decline":
                                $l_id = $this->post('l_id');
                                $l_status = $this->post('l_status');
                                $l_reply = $this->post('l_reply');
                                $l_approved_by = $this->admin_session['u_type'];
                                if ($l_id > 0) {
                                        try {
                                                if ($this->admin_session['u_type'] == 'Project Leader') {

                                                        $sql = "SELECT DISTINCT a.tu_p_id as projectid , c.u_name as Username , c.u_department as Department FROM aa_task2user as a , aa_leaves as b , aa_users as c WHERE  a.tu_u_id  = b.l_u_id  and a.tu_u_id = c.u_id and b.l_id = '{$l_id}'";
                                                        $query = $this->db->query($sql);
                                                        $results = $query->result_array();

                                                        $this->load->model('message_model');
                                                        $me_text = "<b> Department  - " .  $results[0]['Department'] . "</b> <br/>Leave " . $l_status . " By Project Lead - <b> " . $this->admin_session['u_name'] . " </b>of employee - <b>" . $results[0]['Username'] . " </b>with reason  - <br/>" . $l_reply . "";
                                                        $me_p_id = $this->post('me_p_id');
                                                        $data = array(
                                                                'me_datetime' => date("Y-m-d H:i:s"),
                                                                'me_text' => $me_text,
                                                                'me_p_id' => $results[0]['projectid'],
                                                        );
                                                        try {
                                                                $admin_id = $this->message_model->saveLeaveMessage($data);
                                                        } catch (Exception $ex) {
                                                                $this->response(array(
                                                                        'status' => 'fail',
                                                                        'type' => 'popup',
                                                                        'message' => $ex->getMessage()
                                                                ));
                                                        }
                                                }
                                                $this->leave_model->update(array('l_status' => $l_status . "d", 'l_reply' => $l_reply, 'l_approved_by' => $this->admin_session['u_type'],  'l_approved_by_id' => $l_u_id), array('l_id' => $l_id, 'l_status' => 'Pending'));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Leave has been updated successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "loadinfo":
                                $info = [];
                                $l_id = $this->post('l_id');
                                $criteria['select_list'] = 'L.*, U.u_name, U.u_mobile, U.u_email';
                                $criteria['conditions'] = array(array('l_id' => $l_id));
                                //[IMPROVE] may add validation for already approved
                                $leave = $this->leave_model->get_records($criteria, 'result');
                                if (empty($leave)) {
                                        $this->response(array('status' => 'fail', 'message' => "Leave Not Found"));
                                }
                                $leave = $leave[0];
                                $info['l_message'] = $leave['l_message'];
                                $info['l_id'] = $leave['l_id'];
                                $info['u_name'] = $leave['u_name'];
                                $info['u_mobile'] = $leave['u_mobile'];
                                $info['u_email'] = $leave['u_email'];

                                $projects = $this->db->query("SELECT count(DISTINCT(p_id)) as total, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New') AND u_id = '{$leave['l_u_id']}' GROUP BY u_id")->result_array();
                                $info['u_active'] = $projects[0]['total'] ?? 0;
                                $tasks = $this->db->query("SELECT count(DISTINCT(t_id)) as total, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id LEFT OUTER JOIN aa_tasks T ON TU.tu_t_id = T.t_id WHERE TU.tu_removed = 'No' AND (T.t_status = 'New' OR T.t_status = 'Inprogress') AND (P.p_status = 'Active' OR P.p_status = 'New') AND u_id = '{$leave['l_u_id']}' GROUP BY u_id")->result_array();
                                $info['u_tasks'] = $tasks[0]['total'] ?? 0;

                                $this->response(array(
                                        'status' => 'pass',
                                        'data' => $info,
                                        'img_url' => getLogoURL($leave['l_u_id'], 'ulogo')
                                ));
                                break;
                        case "add":

                                $l_id = $this->post('l_id');
                                $l_u_id = $l_u_id;
                                $l_create_date = date("Y-m-d H:i:s");
                                $l_from_date = $this->post('l_from_date');
                                $l_to_date = $this->post('l_to_date');
                                $l_status = "Pending";
                                $l_message = $this->post('l_message');
                                $l_is_halfday = $this->post('l_is_halfday');
                                $l_halfday_time = $this->post('l_halfday_time');
                                $l_is_hourly = $this->post('l_is_hourly');
                                $l_hourly_time = $this->post('l_hourly_time');
                                $l_hourly_time_hour = $this->post('l_hourly_time_hour') ? $this->post('l_hourly_time_hour') : 1;

                                if ($l_hourly_time_hour != '' && $l_is_hourly != '') {
                                        $decimalTimes = [0, 0.15, 0.30, 0.45];
                                        $whole = floor($l_hourly_time_hour);     // 1
                                        $decimal = fmod($l_hourly_time_hour, 1); //0.25
                                        if (in_array($decimal, $decimalTimes)) {
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'type' => 'popup',
                                                        'message' => 'Please Enter Valid Hours.'
                                                ));
                                        }
                                }

                                $data = array(
                                        'l_u_id' => $l_u_id,
                                        'l_create_date' => $l_create_date,
                                        'l_from_date' => convert_display2db($l_from_date),
                                        'l_to_date' => convert_display2db($l_to_date) ? convert_display2db($l_to_date) : convert_display2db($l_from_date),
                                        'l_status' => $l_status,
                                        'l_is_halfday' => $l_is_halfday ?? 'No',
                                        'l_halfday_time' => $l_is_halfday ? $l_halfday_time : '',
                                        'l_is_hourly' =>  $l_is_hourly ?? 'No',
                                        'l_hourly_time_hour' => $l_is_hourly ? $l_hourly_time_hour : '',
                                        'l_hourly_time' => $l_is_hourly ? $l_hourly_time : '',
                                        'l_message' => $l_message,
                                );

                                if ($l_id > 0) {
                                        $data['l_id'] = $l_id;
                                        unset($data['l_create_date']);
                                        unset($data['l_status']);
                                        //unset($data['l_u_id']);
                                }
                                try {
                                        $admin_id = $this->leave_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Leave is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "del":
                                $l_id = $this->post('l_id');
                                if ($l_id > 0) {
                                        try {
                                                $this->leave_model->delete_records(array('l_id' => $l_id, 'l_u_id' => $l_u_id, 'l_status' => 'Pending'));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Leave has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $l_id = $this->post('l_id');
                                if ($l_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('l_id' => $l_id, 'l_u_id' => $l_u_id));
                                        $record = $this->leave_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $record[0]['l_from_date'] = convert_db2display($record[0]['l_from_date']);
                                                $record[0]['l_to_date'] = convert_db2display($record[0]['l_to_date']);
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        if ($this->admin_session['u_type'] != 'Master Admin' && $this->admin_session['u_type'] != 'Bim Head' && $this->admin_session['u_type'] != 'Project Leader') {
                                                $criteria['conditions'] = array(array('l_u_id' => $l_u_id));
                                        }
                                        if ($this->admin_session['u_type'] == 'Project Leader') {
                                                $u_id_array = array();
                                                array_push($u_id_array, $l_u_id);
                                                $this->load->model('user_model');
                                                $criteriausr['conditions'] = array(array('u_leader' => $l_u_id));
                                                $recordsuser = $this->user_model->get_records($criteriausr, 'result');
                                                foreach ($recordsuser as $single_record) {
                                                        //$u_id_array = $u_id_array . ',' . "'" . $single_record['u_id'] . "'";
                                                        array_push($u_id_array, $single_record['u_id']);
                                                }
                                                $criteria['where_in'] = $u_id_array;
                                                //array(array('l_u_id in (' . $ids . ')'));
                                        }
                                        $records = $this->leave_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->leave_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $status = $single_record['l_status'];
                                                if ($single_record['l_approved_by'] != '') {
                                                        $this->load->model('user_model');
                                                        $criteriausr['conditions'] = array(array('u_id' => $single_record['l_approved_by_id']));
                                                        $recordsuser = $this->user_model->get_records($criteriausr, 'result');
                                                        $status =  $single_record['l_status'] . " By <br/>" . $single_record['l_approved_by'] . "<br/><b>" . $recordsuser[0]['u_name'] . "</b>";
                                                }

                                                $nestedData[] = $single_record['u_name'];
                                                $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                                $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                                $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                                $nestedData[] = $single_record['l_message'] . ((!empty($single_record['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $single_record['l_reply'] : "");
                                                $nestedData[] = $status;
                                                $var =  $single_record['l_halfday_time'] ? " - " . $single_record['l_halfday_time'] . " half" : '';
                                                $hourvar =  $single_record['l_hourly_time'] ? " - " . $single_record['l_hourly_time'] . "<br/><b>" . number_format($single_record['l_hourly_time_hour'], 2) . " Hrs</b>" : '';
                                                $nestedData[] = $single_record['l_is_halfday'] . $var;
                                                $nestedData[] = $single_record['l_is_hourly'] . $hourvar;


                                                $anchors = "";
                                                if ($single_record['l_u_id'] == $l_u_id && $single_record['l_status'] == 'Pending') {
                                                        $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['l_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['l_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                }
                                                if (($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') && $single_record['l_status'] == 'Pending') {
                                                        $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Approve\')"><i class="fa fa-thumbs-up"></i><a>&nbsp; ';
                                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Decline\')"><i class="fa fa-thumbs-down"></i><a>';
                                                }
                                                if ($this->admin_session['u_type'] == 'Project Leader' && $single_record['l_status'] == 'Pending') {
                                                        if ($single_record['l_u_id'] != $l_u_id) {
                                                                $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Approve\')"><i class="fa fa-thumbs-up"></i><a>&nbsp; ';
                                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Decline\')"><i class="fa fa-thumbs-down"></i><a>';
                                                        }
                                                }
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }
        public function holidays_post()
        {
                $this->load->model('holiday_model');
                $act = $this->post('act');
                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') {
                } else {
                        if ($act == "add" || $act == "del") {
                                $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                        }
                }
                switch ($act) {
                        case "add":
                                $h_id = $this->post('h_id');
                                $h_date = $this->post('h_date');
                                $h_title = $this->post('h_title');
                                $data = array(
                                        'h_date' => convert_display2db($h_date),
                                        'h_title' => $h_title,
                                );

                                if ($h_id > 0) {
                                        $data['h_id'] = $h_id;
                                }
                                try {
                                        $admin_id = $this->holiday_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Holiday is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "del":
                                $h_id = $this->post('h_id');
                                if ($h_id > 0) {
                                        try {
                                                $this->holiday_model->delete_records(array('h_id' => $h_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Holiday has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $h_id = $this->post('h_id');
                                if ($h_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('h_id' => $h_id));
                                        $record = $this->holiday_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $record[0]['h_date'] = convert_db2display($record[0]['h_date']);
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');


                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;

                                        $records = $this->holiday_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->holiday_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = date("l", strtotime($single_record['h_date']));
                                                $nestedData[] = date("M d, Y", strtotime($single_record['h_date']));
                                                $nestedData[] = $single_record['h_title'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['h_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['h_id'] . '\')"><i class="fa fa-trash"></i><a>';
                                                if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head']))
                                                        $nestedData[] = $anchors;
                                                else
                                                        $nestedData[] = "";
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }
        public function employees_post()
        {
                $this->load->model('user_model');
                $act = $this->post('act');
                switch ($act) {
                        case "add":
                                $u_id = $this->post('u_id');
                                $u_username = $this->post('u_username');
                                $u_password = $this->post('u_password');
                                $u_name = $this->post('u_name');
                                $u_join_date = $this->post('u_join_date');
                                $u_leave_date = $this->post('u_leave_date');
                                $u_salary = $this->post('u_salary');
                                $u_mobile = $this->post('u_mobile');
                                $u_email = $this->post('u_email');
                                $u_address = $this->post('u_address');
                                $u_qualification = $this->post('u_qualification');
                                $u_department = $this->post('u_department');
                                $u_status = $this->post('u_status');
                                $u_type = $this->post('u_type');
                                $u_leader = $this->post('u_leader');
                                $u_comments = $this->post('u_comments');
                                if ($this->admin_session['u_type'] == 'Master Admin') {
                                        $data = array(
                                                'u_username' => $u_username,
                                                'u_password' => $u_password,
                                                'u_name' => $u_name,
                                                'u_join_date' => convert_display2db($u_join_date),
                                                'u_leave_date' => convert_display2db($u_leave_date),
                                                'u_salary' => $u_salary,
                                                'u_mobile' => $u_mobile,
                                                'u_email' => $u_email,
                                                'u_address' => $u_address,
                                                'u_qualification' => $u_qualification,
                                                'u_department' => $u_department,
                                                'u_status' => $u_status,
                                                'u_type' => $u_type,
                                                'u_leader' => $u_leader,
                                                'u_comments' => $u_comments,
                                        );
                                } else {
                                        $data = array(
                                                'u_username' => $u_username,
                                                'u_password' => $u_password,
                                                'u_name' => $u_name,
                                                'u_join_date' => convert_display2db($u_join_date),
                                                'u_leave_date' => convert_display2db($u_leave_date),
                                                //'u_salary' => $u_salary,
                                                'u_mobile' => $u_mobile,
                                                'u_email' => $u_email,
                                                'u_address' => $u_address,
                                                'u_qualification' => $u_qualification,
                                                'u_department' => $u_department,
                                                'u_status' => $u_status,
                                                'u_type' => $u_type,
                                                'u_leader' => $u_leader,
                                                'u_comments' => $u_comments,
                                        );
                                }

                                if ($u_id > 0) {
                                        $data['u_id'] = $u_id;
                                        if ($u_password == "") {
                                                unset($data['u_password']);
                                        }
                                }
                                $errors = validate_image($_FILES, $this->allowed_files_images);


                                try {
                                        if ($errors != "") throw new Exception($errors);
                                        $admin_id = $this->user_model->save($data);
                                        if ($this->admin_session['u_type'] == 'Master Admin')
                                                $salarysave = $this->user_model->save_users_salary($data, $admin_id);
                                        save_image($_FILES, "ulogo_" . $admin_id);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Employee is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "del":
                                $u_id = $this->post('u_id');
                                if ($u_id > 0) {
                                        try {
                                                $this->user_model->delete_records(array('u_id' => $u_id));
                                                $this->user_model->delete_users_salary(array('u_id' => $u_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Employee has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $u_id = $this->post('u_id');
                                if ($u_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('u_id' => $u_id));
                                        $record = $this->user_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                unset($record[0]['u_password']);
                                                $record[0]['u_join_date'] = convert_db2display($record[0]['u_join_date']);
                                                $record[0]['u_leave_date'] = convert_db2display($record[0]['u_leave_date']);
                                                $file_name = "./assets/logos/ulogo_" . $record[0]['u_id'] . ".jpg";
                                                if (file_exists($file_name)) {
                                                        $record[0]['u_photo']  = base_url("assets/logos/ulogo_" . $record[0]['u_id']) . ".jpg";
                                                } else {
                                                        $record[0]['u_photo']  = "";
                                                }
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $txt_search = $this->post('txt_search');
                                        $txt_U_Type = $this->post('txt_U_Type');
                                        $txt_U_Status = $this->post('txt_U_Status');

                                        $criteria = array();
                                        $criteria['sort_by'] = "u_id"; // Sort order by new employee
                                        $criteria['sort_type'] = "desc";
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("u_id <>" => "1");

                                        if ($txt_search != null) {
                                                //$criteria['conditions'][] = array("u_name LIKE " => "%" . $txt_search . "%");
                                                //$criteria['or_conditions'][] = array("u_username LIKE " => "%" . $txt_search . "%");
                                                $criteria['newcondition'] = ' (u_name like "%' . $txt_search . '%" OR u_username  like  "%' . $txt_search . '%")';
                                        }
                                        if ($txt_U_Type != null) {
                                                $criteria['conditions'][] = array("u_type LIKE " => "%" . $txt_U_Type . "%");
                                        }
                                        if ($txt_U_Status != null) {
                                                $criteria['conditions'][] = array("u_status LIKE " => $txt_U_Status);
                                        } else {
                                                $criteria['conditions'][] = array("u_status LIKE " => 'Active');
                                        }



                                        $records = $this->user_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->user_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['u_username'];
                                                $nestedData[] = $single_record['u_name'];
                                                $nestedData[] = $single_record['u_email'];
                                                $nestedData[] = $single_record['u_mobile'];
                                                if ($this->admin_session['u_type'] == 'Master Admin')
                                                        $nestedData[] = $single_record['u_salary'];
                                                else
                                                        $nestedData[] = 0;
                                                $nestedData[] = $single_record['u_type'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['u_id'] . '\',\'' . $this->admin_session['u_type'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['u_id'] . '\')"><i class="fa fa-trash"></i><a>';
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "sql" => $this->db->last_query(),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }
                        case "list_task":
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $txt_search = $this->post('txt_search');


                                $criteria = array();
                                $criteria['sort_by'] = "u_name";
                                //$criteria['sort_type'] = "desc";
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("u_id <>" => "1");
                                $criteria['newcondition'] = 'u_status = "Active" AND (u_type = "Project Leader" OR u_type = "Employee")';  // For Both OR and AND together
                                if ($txt_search != null) {
                                        $criteria['conditions'][] = array("u_name LIKE " => "%" . $txt_search . "%");
                                        //$criteria['or_conditions'][] = array("u_username LIKE " => "%" . $txt_search . "%");
                                }



                                $records = $this->user_model->get_records($criteria, 'result');
                                $sql = $this->db->last_query();
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->user_model->get_records($criteria);
                                $result = array();
                                $active_projects = array();
                                $active_tasks = array();
                                $leaves = array();
                                $today = date("Y-m-d");
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

                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_id'];
                                        $nestedData[] = $single_record['u_username'];
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] = $single_record['u_email'];
                                        $nestedData[] = $single_record['u_mobile'];
                                        $nestedData[] = $single_record['u_salary'];
                                        $nestedData[] = $single_record['u_type'];
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['u_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['u_id'] . '\')"><i class="fa fa-trash"></i><a>';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $sql,
                                        "data" => $result,
                                        "active_projects" => $active_projects,
                                        "active_tasks" => $active_tasks,
                                        "leaves" => $leaves,
                                );
                                $this->response($json_data);


                                break;
                }
        }
        public function projects_post()
        {
                $this->load->model('project_model');
                $act = $this->post('act');
                switch ($act) {
                        case "email":
                                $email_list = $this->post('email_list');
                                $email_subject = $this->post('email_subject');
                                $email_message = $this->post('email_message');
                                $this->load->library('email', $this->config->item('email_config'));

                                $this->email->from('noreply@dummyproject.com', 'Aashir');
                                $this->email->to($email_list);

                                $this->email->subject($email_subject);
                                $this->email->message($email_message);

                                try {
                                        $this->email->send();
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Email is sent.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }
                                break;
                        case "teams":
                                $p_id = $this->post('p_id');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                /*
                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("pe_p_id" => $p_id);
                                */

                                $records = $this->project_model->get_project_team($p_id);
                                //$criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = count($records);
                                $result = array();
                                $total = 0;
                                foreach ($records as $single_record) {
                                        $single_record['work_hour_total'] = $single_record['work_hour_total'] ?? 0;
                                        $nestedData = array();
                                        $nestedData[] = '<label class="check_container"><input type="checkbox" id="u_ids_' . $single_record['u_id'] . '" name="u_id[]" value="' . $single_record['u_email'] . '" class="teammet"><span class="checkmark"></span></label>';
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] = $single_record['u_email'];
                                        $nestedData[] = number_format($single_record['work_hour_total'], 2);
                                        if ($this->admin_session['u_type'] == 'Master Admin') {
                                                $nestedData[] = $single_record['u_salary'];
                                                $nestedData[] = number_format($single_record['work_hour_total'] * $single_record['u_salary'], 2);
                                        } else {
                                                $nestedData[] = 0;
                                                $nestedData[] = 0;
                                        }
                                        $result[] = $nestedData;
                                        if ($this->admin_session['u_type'] == 'Master Admin')
                                                $total = $total + $single_record['work_hour_total'] * $single_record['u_salary'];
                                        else
                                                $total = 0;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData + 1),
                                        "recordsFiltered" => intval($totalFiltered + 1),
                                        "data" => $result,
                                        "total_val" =>  number_format($total, 2)
                                );
                                $this->response($json_data);
                                break;
                        case "accounts":
                                $p_id = $this->post('p_id');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("pe_p_id" => $p_id);

                                $records = $this->project_model->get_project_expense($criteria, 'result');
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->project_model->get_project_expense($criteria);
                                $result = array();
                                $total = 0;
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $single_record['pe_lbl'];
                                        $nestedData[] = number_format($single_record['pe_val'], 2);
                                        $result[] = $nestedData;
                                        $total = $total + $single_record['pe_val'];
                                }
                                $total_salary = $this->project_model->get_total_salary($p_id);
                                $total_salary[0]['final_salary'] = $total_salary[0]['final_salary'] ?? 0;
                                $result[] = array('Salary',  number_format($total_salary[0]['final_salary'] ?? 0, 2));

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData + 1),
                                        "recordsFiltered" => intval($totalFiltered + 1),
                                        "data" => $result,
                                        "total_val" => number_format($total + $total_salary[0]['final_salary'], 2)
                                );
                                $this->response($json_data);

                                break;
                        case "vcom_add":
                                $pv_id = $this->post('pv_id');
                                $pv_p_id = $this->post('pv_p_id');
                                $pv_u_id = $this->admin_session['u_id'];
                                $pv_text = $this->post('pv_text');
                                $pv_datetime = date("Y-m-d H:i:s");
                                $data = array(
                                        'pv_p_id' => $pv_p_id,
                                        'pv_u_id' => $pv_u_id,
                                        'pv_text' => $pv_text,
                                        'pv_datetime' => $pv_datetime,
                                );
                                if ($pv_id > 0) {
                                        $data['pv_id'] = $pv_id;
                                }
                                try {
                                        $p_id = $this->project_model->save_project_vcom($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Record is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "vcom_list":
                                $pv_p_id = $this->post('pv_p_id');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("V.pv_p_id" => $pv_p_id);

                                $records = $this->project_model->get_project_vcom($criteria, 'result');
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->project_model->get_project_vcom($criteria);
                                $result = array();
                                $total = 0;
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = convert_db2display($single_record['pv_datetime']);
                                        $nestedData[] = $single_record['pv_text'];
                                        $nestedData[] = $single_record['u_name'];
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData + 1),
                                        "recordsFiltered" => intval($totalFiltered + 1),
                                        "data" => $result,
                                );
                                $this->response($json_data);
                                break;
                        case "add":
                                $p_id = $this->post('p_id');
                                $p_number = $this->post('p_number');
                                $p_name = $this->post('p_name');
                                $p_value = $this->post('p_value');
                                $p_contact = $this->post('p_contact');
                                $p_cat = $this->post('p_cat');
                                $p_status = $this->post('p_status');
                                $p_address = $this->post('p_address');
                                $p_scope = $this->post('p_scope');
                                $p_show_dashboard = $this->post('p_show_dashboard');
                                $pe_lbl = $this->post('pe_lbl');
                                $pe_val = $this->post('pe_val');
                                //print_r($pe_lbl);
                                if ($p_show_dashboard == "") $p_show_dashboard = "No";
                                $data = array(
                                        'p_number' => $p_number,
                                        'p_name' => $p_name,
                                        'p_value' => $p_value,
                                        'p_contact' => $p_contact,
                                        'p_cat' => $p_cat,
                                        'p_status' => $p_status,
                                        'p_address' => $p_address,
                                        'p_scope' => $p_scope,
                                        'p_show_dashboard' => $p_show_dashboard,
                                );
                                if ($p_id > 0) {
                                        $data['p_id'] = $p_id;
                                } else {
                                }
                                $errors = validate_image($_FILES, $this->allowed_files_images);
                                try {
                                        if ($errors != "") throw new Exception($errors);
                                        $p_id = $this->project_model->save($data);
                                        if ($this->admin_session['u_type'] == 'Master Admin') {
                                                $this->project_model->save_project_expense($p_id, $pe_lbl, $pe_val);
                                        }
                                        save_image($_FILES, "plogo_" . $p_id);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Project is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "del":
                                $p_id = $this->post('p_id');
                                if ($p_id > 0) {
                                        try {
                                                $this->project_model->delete_records(array('p_id' => $p_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Project has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $p_id = $this->post('p_id');
                                if ($p_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('p_id' => $p_id));
                                        $record = $this->project_model->get_records($criteria, 'result');
                                        //get_project_expense
                                        if (isset($record[0])) {
                                                $file_name = "./assets/logos/plogo_" . $record[0]['p_id'] . ".jpg";
                                                if (file_exists($file_name)) {
                                                        $record[0]['photo']  = base_url("assets/logos/plogo_" . $record[0]['p_id']) . ".jpg";
                                                } else {
                                                        $record[0]['photo']  = "";
                                                }
                                                $pe = '';
                                                if ($this->admin_session['u_type'] == 'Master Admin') {
                                                        $criteria = array();
                                                        $criteria['select_list'] = 'pe_val, pe_lbl';
                                                        $criteria['conditions'] = array(array('pe_p_id' => $p_id));
                                                        $pe = $this->project_model->get_project_expense($criteria, 'result');
                                                }
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0],
                                                        'pe' => $pe
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $txt_search = $this->post('txt_search');
                                        $txt_p_cat = $this->post('txt_p_cat');
                                        $txt_p_status = $this->post('txt_p_status');


                                        $criteria = array();
                                        $criteria['sort_by'] = "p_number";
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("p_cat" => $txt_p_cat);

                                        if (!empty($txt_p_status))
                                                $criteria['conditions'][] = array("p_status" => $txt_p_status);

                                        if ($txt_search != null) {
                                                //$criteria['conditions'][] = array("p_number LIKE " => "%" . $txt_search . "%");
                                                //$criteria['or_conditions'][] = array("p_name LIKE " => "%" . $txt_search . "%");
                                                $criteria['newcondition'] = ' (p_number like "%' . $txt_search . '%" OR p_name  like  "%' . $txt_search . '%")';
                                        }

                                        $records = $this->project_model->get_records($criteria, 'result');
                                        $sql = $this->db->last_query();
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->project_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['p_number'];
                                                $nestedData[] = $single_record['p_name'];
                                                $nestedData[] = $single_record['p_address'];
                                                if ($this->admin_session['u_type'] == 'Master Admin') {
                                                        $nestedData[] = $single_record['p_value'];
                                                        $total_exp = $this->project_model->get_total_expense($single_record['p_id']);
                                                        $nestedData[] = $total_exp;
                                                        $nestedData[] = $single_record['p_value'] - $total_exp;
                                                }
                                                $nestedData[] = $single_record['p_status'];
                                                $anchors = '<a href="' . base_url("home/project_detail/" . $single_record['p_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['p_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['p_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                $anchors .= '<a href="' . base_url("home/project_contacts/" . $single_record['p_id']) . '" class="btn btn-warning btn-md"><i class="fa fa-phone"></i><a>';
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                //"sql" => $sql,
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }
        public function tasks_post()
        {
                $this->load->model('task_model');
                $this->load->library('General');
                $act = $this->post('act');
                // Validation aread for tasks
                if ($this->admin_session['u_type'] == 'Employee' && in_array($act, ["add", "del", "assigns", "file_del"])) {
                        $this->response(array('status' => 'session', 'message' => 'You do not have access for this section.'));
                }
                switch ($act) {
                        case "tm_add":
                                $t_id = $this->post('t_id');
                                $tm_text = $this->post('tm_text');

                                //$error = $this->general->ValidateTaskAddEdit($t_id, -1, $this->admin_session, false);
                                //if($error) $this->response(array('status' => 'fail','type' => 'popup', 'message' => $error));

                                $errors = validate_task_message_files($_FILES, $this->allowed_files);
                                if ($errors) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $errors
                                        ));
                                }
                                $data = array(
                                        'tm_t_id' => $t_id,
                                        'tm_text' => $tm_text,
                                        'tm_date' => date("Y-m-d H:i:s"),
                                        'tm_u_id' => $this->admin_session['u_id'],
                                );
                                $tm_id = $this->task_model->save_taskmessage($data);
                                if (isset($_FILES['tm_file'])) {
                                        $_FILES['tm_file']['ext'] = explode(".", $_FILES['tm_file']['name']);
                                        $_FILES['tm_file']['ext'] = strtolower($_FILES['tm_file']['ext'][count($_FILES['tm_file']['ext']) - 1]);
                                        $data = array(
                                                'tm_file_name' => $_FILES['tm_file']['name'],
                                                'tm_file_type' => $_FILES['tm_file']['ext'],
                                        );
                                        $this->task_model->update_taskmessage($tm_id, $data);
                                        $task_message_files = $this->config->item('task_message_files');
                                        $directory = ceil($tm_id / 500);
                                        $task_files_final = $task_message_files . $directory . "/";
                                        if (!is_dir($task_files_final)) {
                                                mkdir($task_files_final);
                                        }
                                        move_uploaded_file($_FILES['tm_file']['tmp_name'], $task_files_final . $tm_id);
                                }
                                $this->response(array(
                                        'status' => 'pass',
                                        'message' => 'Message is saved.'
                                ));
                                break;
                        case "tm_list":
                                $t_id = $this->post('t_id');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("V.tm_t_id" => $t_id);

                                $records = $this->task_model->get_task_message($t_id);
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->task_model->get_task_message($t_id, $offset, $limit);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = convert_db2display($single_record['tm_date']);
                                        $nestedData[] = $single_record['tm_text'];
                                        $nestedData[] = $single_record['tm_file_name'] ? '<a href="' . base_url('home/download/tm/' . $single_record['tm_id']) . '" target="_blank">' . $single_record['tm_file_name'] . '</a>' : '';
                                        $nestedData[] = $single_record['u_name'];
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData + 1),
                                        "recordsFiltered" => intval($totalFiltered + 1),
                                        "data" => $result,
                                );
                                $this->response($json_data);
                                break;
                        case "t_loghours":
                                $t_id = $this->post('t_id');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $emp_id = $this->post('emp_id') ?? null;
                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['task_id'] = $t_id;
                                $criteria['user_id'] = $emp_id;
                                $criteria['result_type'] = 'all_records';

                                $records = $this->task_model->get_records_for_task($criteria);
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->task_model->get_records_for_task($criteria);
                                $result = array();
                                $whours = 0;
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] = convert_db2display($single_record['at_date']);
                                        $nestedData[] = RevTime($single_record['at_start']) . " - " . RevTime($single_record['at_end']);
                                        $whours = $whours + (($single_record['at_end'] - $single_record['at_start']) / 60);
                                        $nestedData[] = $single_record['at_comment'];
                                        $result[] = $nestedData;
                                }
                                $whole = floor($whours);      // 1
                                $fraction = $whours - $whole; // .25
                                if ($fraction == '.75')
                                        $total_salary = str_replace($fraction, '.75', '.45');
                                if ($fraction == '.25')
                                        $total_salary = str_replace($fraction, '.25', '.15');
                                if ($fraction == '.50')
                                        $total_salary = str_replace($fraction, '.50', '.30');
                                if ($whours > 0)
                                        $total_hrs = "<b>Total Hours Worked :  " . (number_format($whole + $total_salary, 2)) . " hr</b>";
                                else
                                        $total_hrs = '';
                                $new = array("", "", $total_hrs, "");
                                array_push($result, $new);

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData + 1),
                                        "recordsFiltered" => intval($totalFiltered + 1),
                                        "data" => $result,
                                );
                                $this->response($json_data);
                                break;
                        case "file_del":
                                $tf_id = $this->post('tf_id');
                                //[EXTRA] May Validate
                                delete_file("task", $tf_id);
                                $this->response(array(
                                        'status' => 'pass',
                                        'message' => 'Task File is deleted.'
                                ));
                                break;
                        case "assigns":
                                $t_id = $this->post('t_id');
                                $t_p_id = $this->post('t_p_id');
                                $t_assign = $this->post('u_id');
                                $act_sub = $this->post('act_sub');
                                //[EXTRA] May Validate
                                $this->task_model->assign_update($t_id, $t_assign, $act_sub, $t_p_id);
                                $this->response(array(
                                        'status' => 'pass',
                                        'message' => 'Task is updated.'
                                ));
                                break;
                        case "add":
                                $is_edit = false;
                                $errors = validate_task_files($_FILES, $this->allowed_files);
                                if ($errors) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $errors
                                        ));
                                }
                                $t_id = $this->post('t_id');
                                $t_p_id = $this->post('t_p_id');
                                $t_title = $this->post('t_title');
                                $t_priority = $this->post('t_priority');
                                $t_status = $this->post('t_status');
                                $t_hours = $this->post('t_hours');
                                $t_hours_planned = $this->post('t_hours_planned');
                                $t_description = $this->post('t_description');
                                $t_dependancy = $this->post('t_dependancy');
                                $t_parent = $this->post('t_parent');
                                $t_u_id = $this->admin_session['u_id'];
                                $t_assign = $this->post('u_id');
                                $tf_lbl = $this->post('tf_lbl');
                                if ($t_parent == "") $t_parent = 0;
                                $error = $this->general->ValidateTaskAddEdit($t_id, $t_parent, $this->admin_session);
                                if ($error) {
                                        $this->response(array('status' => 'fail', 'type' => 'popup', 'message' => $error));
                                }
                                $data = array(
                                        't_p_id' => $t_p_id,
                                        't_title' => $t_title,
                                        't_priority' => $t_priority,
                                        't_status' => $t_status,
                                        't_hours' => $t_hours,
                                        't_hours_planned' => $t_hours_planned ?? 0,
                                        't_description' => $t_description,
                                        't_dependancy' => $t_dependancy,
                                        't_parent' => $t_parent,
                                        't_u_id' => $t_u_id,
                                        't_createdate' => date("Y-m-d H:i:s"),
                                );

                                if ($t_id > 0) {
                                        $data['t_id'] = $t_id;
                                        unset($data['t_u_id']);
                                        unset($data['t_parent']);
                                        unset($data['t_createdate']);
                                        $is_edit = true;
                                }
                                try {
                                        $t_id = $this->task_model->save($data);
                                        if (!$is_edit) {
                                                $this->task_model->assign($t_id, $t_p_id, $t_assign);
                                        }
                                        task_files("add", $t_id, $_FILES, $tf_lbl);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Task is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "del":
                                $t_id = $this->post('t_id');
                                $error = $this->general->ValidateTaskAddEdit($t_id, -1, $this->admin_session);
                                if ($error) $this->response(array('status' => 'fail', 'type' => 'popup', 'message' => $error));
                                if ($t_id > 0) {
                                        try {
                                                $this->task_model->delete_records(array('t_id' => $t_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Task has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $t_id = $this->post('t_id');
                                $t_p_id = $this->post('t_p_id');
                                $t_parent = $this->post('t_parent');
                                if ($t_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('t_id' => $t_id));
                                        $record = $this->task_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                //unset($record[0]['t_title']);
                                                $assigns = $this->task_model->get_assigns($t_id);
                                                $files = $this->task_model->get_files(array('tf_t_id' => $t_id));
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0],
                                                        'assigns' => $assigns,
                                                        'files' => $files,
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $txt_projects = $this->post('txt_projects');
                                        $txt_status = $this->post('txt_status');
                                        $txt_employee =  $this->post('txt_employee');

                                        $criteria = array();
                                        $criteria['sort_by'] = 't_priority';
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        if ($txt_projects != '') {
                                                $criteria['conditions'][] = array("p_name LIKE " => "%" . $txt_projects . "%");
                                        }
                                        if ($txt_status != '') {
                                                $criteria['conditions'][] = array("t_status" => $txt_status);
                                        }
                                        $criteria['conditions'][] = array("p_status" => "Active");

                                        if ($t_parent > 0) {
                                                $criteria['conditions'][] = array("t_parent" => $t_parent);
                                        } else {
                                                $criteria['conditions'][] = array("t_parent" => 0);
                                        }
                                        if ($t_p_id > 0) {
                                                $criteria['conditions'][] = array("t_p_id" => $t_p_id);
                                        }
                                        $records = $this->task_model->get_records($criteria, 'result');
                                        $sql = $this->db->last_query();
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->task_model->get_records($criteria);
                                        $result = array();
                                        $i = 1;
                                        foreach ($records as $single_record) {
                                                if (!$this->general->ValidateTaskAssignment($single_record['t_id'], $this->admin_session)) continue;

                                                if ($txt_employee != null) {
                                                        $nestedData = array();
                                                        $assigns = $this->task_model->get_assigns($single_record['t_id'], 0, false, $txt_employee);
                                                        if ($assigns != null) {
                                                                $nestedData[] = $i++;
                                                                if ($t_p_id <= 0) {
                                                                        $nestedData[] = $single_record['p_name'];
                                                                }
                                                                $nestedData[] = $single_record['t_title'];
                                                                $nestedData[] = $single_record['t_priority'];
                                                                $nestedData[] = convert_db2display($single_record['t_createdate']);
                                                                $nestedData[] = $single_record['u_name'];
                                                                $assigns = $this->task_model->get_assigns($single_record['t_id']);
                                                                $assigns = array_column($assigns, 'u_name');
                                                                $assigns = implode(", ", $assigns);
                                                                $nestedData[] = $assigns;
                                                                if ($t_parent > 0) {
                                                                        $nestedData[] = $single_record['t_hours'];
                                                                }
                                                                $nestedData[] = $single_record['t_status'];
                                                                $anchors = "";
                                                                $anchors .= '<a href="' . site_url("home/task/view/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                                if (in_array($this->admin_session['u_type'], ['Bim Head', 'Master Admin']) ||  $this->admin_session['u_id'] == $single_record['t_u_id']) {
                                                                        $anchors .= '<a href="' . site_url("home/task/edit/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['t_id'] . '\', \'' . $single_record['t_p_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                                }
                                                                if ($single_record['t_parent'] == 0)
                                                                        $anchors .= '<a href="' . site_url("home/task/sub/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-warning btn-md"><i class="fa fa-tasks"></i><a>&nbsp; ';
                                                                $nestedData[] = $anchors;
                                                                $result[] = $nestedData;
                                                        }
                                                } else {
                                                        $nestedData = array();
                                                        $nestedData[] = $i++;
                                                        if ($t_p_id <= 0) {
                                                                $nestedData[] = $single_record['p_name'];
                                                        }
                                                        $nestedData[] = $single_record['t_title'];
                                                        $nestedData[] = $single_record['t_priority'];
                                                        $nestedData[] = convert_db2display($single_record['t_createdate']);
                                                        $nestedData[] = $single_record['u_name'];
                                                        $assigns = $this->task_model->get_assigns($single_record['t_id']);
                                                        $assigns = array_column($assigns, 'u_name');
                                                        $assigns = implode(", ", $assigns);
                                                        $nestedData[] = $assigns;
                                                        if ($t_parent > 0) {
                                                                $nestedData[] = $single_record['t_hours'];
                                                        }
                                                        $nestedData[] = $single_record['t_status'];
                                                        $anchors = "";
                                                        $anchors .= '<a href="' . site_url("home/task/view/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                        if (in_array($this->admin_session['u_type'], ['Bim Head', 'Master Admin']) ||  $this->admin_session['u_id'] == $single_record['t_u_id']) {
                                                                $anchors .= '<a href="' . site_url("home/task/edit/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['t_id'] . '\', \'' . $single_record['t_p_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                        }
                                                        if ($single_record['t_parent'] == 0)
                                                                $anchors .= '<a href="' . site_url("home/task/sub/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-warning btn-md"><i class="fa fa-tasks"></i><a>&nbsp; ';
                                                        $nestedData[] = $anchors;
                                                        $result[] = $nestedData;
                                                }
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                //"sql" => $sql,
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }
        public function project_contacts_post()
        {
                $this->load->model('project_model');
                $act = $this->post('act');
                if (!in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head']) && $act != "list") {
                        $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                }
                switch ($act) {
                        case "add":
                                $pc_id = $this->post('pc_id');
                                $pc_p_id = $this->post('pc_p_id');
                                $pc_name = $this->post('pc_name');
                                $pc_mobile = $this->post('pc_mobile');
                                $pc_email = $this->post('pc_email');
                                $pc_designation = $this->post('pc_designation');
                                $data = array(
                                        'pc_p_id' => $pc_p_id,
                                        'pc_name' => $pc_name,
                                        'pc_mobile' => $pc_mobile,
                                        'pc_email' => $pc_email,
                                        'pc_designation' => $pc_designation,
                                );

                                if ($pc_id > 0) {
                                        $data['pc_id'] = $pc_id;
                                }
                                try {
                                        $admin_id = $this->project_model->save_project_contacts($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Project Contact is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }
                                break;
                        case "del":
                                $pc_id = $this->post('pc_id');
                                $pc_p_id = $this->post('pc_p_id');
                                if ($pc_id > 0) {
                                        try {
                                                $this->project_model->delete_project_contacts(array('pc_id' => $pc_id, 'pc_p_id' => $pc_p_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Project Contact has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $pc_id = $this->post('pc_id');
                                $pc_p_id = $this->post('pc_p_id');
                                if ($pc_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('pc_id' => $pc_id, 'pc_p_id' => $pc_p_id));
                                        $record = $this->project_model->get_project_contacts($criteria, 'result');
                                        if (isset($record[0])) {
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');


                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("pc_p_id" => $pc_p_id);

                                        $records = $this->project_model->get_project_contacts($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->project_model->get_project_contacts($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['pc_name'];
                                                $nestedData[] = $single_record['pc_designation'];
                                                $nestedData[] = $single_record['pc_email'];
                                                $nestedData[] = $single_record['pc_mobile'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['pc_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['pc_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                if (!in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head'])) {
                                                        $anchors = '';
                                                }
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }
                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result,
                                        );
                                        $this->response($json_data);
                                }
                                break;
                }
        }
        public function timesheet_post()
        {
                $at_u_id = $this->admin_session['u_id'];
                $this->load->model('timesheet_model');
                $act = $this->post('act');
                switch ($act) {
                        case "add":
                                $at_id = $this->post('at_id');
                                $at_p_id = $this->post('at_p_id');
                                $at_t_id = $this->post('at_t_id');
                                $at_date = $this->post('at_date');
                                $at_start = $this->post('at_start');
                                $at_end = $this->post('at_end');
                                $at_comment = $this->post('at_comment');
                                $data = array(
                                        'at_p_id' => $at_p_id,
                                        'at_t_id' => $at_t_id,
                                        'at_u_id' => $at_u_id,
                                        'at_date' => convert_display2db($at_date),
                                        'at_start' => $at_start,
                                        'at_end' => $at_end,
                                        'at_comment' => $at_comment,
                                );

                                if ($at_id > 0) {
                                        $data['at_id'] = $at_id;
                                }
                                try {
                                        $at_id = $this->timesheet_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Timesheet data is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }
                                break;
                        case "del":
                                $at_id = $this->post('at_id');
                                if ($at_id > 0) {
                                        try {
                                                $this->timesheet_model->delete(array('at_id' => $at_id, 'at_u_id' => $at_u_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Timesheet data has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "total_time":
                                $at_id = $this->post('at_id');
                                if ($at_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('at_id' => $at_id, 'at_u_id' => $at_u_id));
                                        $record = $this->timesheet_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $record[0]['at_date'] = convert_db2display($record[0]['at_date']);
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        //$at_date = $this->post('at_date');
                                        $at_u_id = $this->post('at_u_id') ?? $this->admin_session['u_id'];
                                        $at_start_sdate = convert_display2db($this->post('at_start_sdate'));
                                        $at_date = convert_display2db($this->post('at_date'));
                                        $get_all_empl_hrs =  $this->timesheet_model->get_all_empl_hrs($at_start_sdate, $at_date, $at_u_id);
                                        if (!empty($get_all_empl_hrs)) {
                                                $n = $get_all_empl_hrs[0]['work_hours'];
                                                $whole = floor($n);      // 1
                                                $fraction = $n - $whole; // .25
                                                if ($fraction == '.75')
                                                        $total_salary = str_replace($fraction, '.75', '.45');
                                                if ($fraction == '.25')
                                                        $total_salary = str_replace($fraction, '.25', '.15');
                                                if ($fraction == '.50')
                                                        $total_salary = str_replace($fraction, '.50', '.30');
                                                $total_hrs = "<b>Total Hours Worked :  " . (number_format($whole + $total_salary, 2)) . " hr</b>";
                                        } else
                                                $total_hrs = "<b>Total Hours Worked : 0 hr</b>";
                                        $this->response(array(
                                                'status' => "pass",
                                                'message' => 'Timesheet data has been deleted successfully.',
                                                'total_hrs' => $total_hrs,
                                        ));
                                }
                                break;
                        case "list":
                                $at_id = $this->post('at_id');
                                if ($at_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('at_id' => $at_id, 'at_u_id' => $at_u_id));
                                        $record = $this->timesheet_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $record[0]['at_date'] = convert_db2display($record[0]['at_date']);
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        //$at_date = $this->post('at_date');
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $at_start_sdate = convert_display2db($this->post('at_start_sdate'));
                                        $at_date = convert_display2db($this->post('at_date'));

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['sort_by'] = 'at_date,at_start';
                                        $criteria['conditions'] = array();
                                        //$criteria['conditions'][] = array("at_u_id" => $at_u_id, 'at_date' => convert_display2db($at_date));
                                        $criteria['conditions'][] = array("at_u_id" => $at_u_id, "at_date >= " => $at_start_sdate, "at_date <= " => $at_date);
                                        $sql = $this->db->last_query();
                                        $records = $this->timesheet_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->timesheet_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['p_name'] ?? "Leave";
                                                $nestedData[] = $single_record['t_title'] ?? "Leave";
                                                $nestedData[] = convert_db2display($single_record['at_date']);
                                                $nestedData[] = RevTime($single_record['at_start']) . " - " . RevTime($single_record['at_end']);
                                                $nestedData[] = $single_record['at_comment'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['at_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['at_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                if ($this->timesheet_model->validateDate($at_date))
                                                        $nestedData[] = $anchors;
                                                else
                                                        $nestedData[] = '';
                                                $result[] = $nestedData;
                                        }
                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                //"sql" => $sql,
                                                "data" => $result,

                                        );
                                        $this->response($json_data);
                                }
                                break;
                }
        }
        public function login_post()
        {
                $this->load->model('user_model');
                $u_username = $this->post('u_username');
                $u_password = $this->post('u_password');

                $params = array();
                $params['conditions'] = array(array('u_username' => $u_username));
                $records = $this->user_model->get_records($params);
                if (count($records)) {
                        $records = $records[0];
                        if ($records['u_status'] == 'Active') {
                                if ($records['u_password'] == md5($u_password)) {
                                        unset($records['u_password']);
                                        try {
                                                $this->db->insert('aa_present', ['pr_u_id' => $records['u_id'], 'pr_date' => date("Y-m-d")]);
                                        } catch (Exception $ex) {
                                        }
                                        /*
                                        $this->load->model('message_model');
                                        $params = array();
                                        $params['u_id'] = $records['u_id'];
                                        $params['conditions'] = array(array('mu_read' => 0));
                                        $messages = $this->message_model->get_records($params);
                                        */
                                        if ($records['u_type'] == "Bim Head" || $records['u_type'] == "Master Admin" || $records['u_type'] == "Project Leader") {
                                                $url = base_url("home/index");
                                        } else {
                                                $url = base_url("home/tasks");
                                        }
                                        $this->session->set_userdata(['admin_session' => $records]);
                                        //$this->session->set_userdata(['messages' => $messages]);
                                        $this->response(array('status' => "pass", 'message' => 'Login successfully.', 'url' => $url));
                                } else {
                                        $this->response(array('status' => 'fail', 'message' => 'Invalid user name or password'));
                                }
                        } else {
                                $this->response(array('status' => 'fail', 'message' => 'Your account is not active'));
                        }
                } else {
                        $this->response(array('status' => 'fail', 'message' => 'Invalid user name or password'));
                }
        }
        public function drop_get_post()
        {
                $ReturnVal = array();
                $dropobjs = $this->post('dropobjs');
                $u_id = $this->admin_session['u_id'];
                foreach ($dropobjs as $val) {
                        $criteria = array();
                        $ReturnVal[$val['type']] = '';
                        switch ($val['type']) {
                                case 'team_leader':
                                        $this->load->model('user_model');
                                        $params = array();
                                        $params['conditions'] = array();
                                        $params['conditions'][] = array('u_type' => 'Project Leader');
                                        $records = $this->user_model->get_records($params);
                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="">Select Project Leader</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['u_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['u_id']) ? ' selected="selected"' : '') . '>' . $row['u_name'] . '</option>';
                                                }
                                        }
                                        break;
                                case 'projects':  //[PENDING] Check for Assigned Projects
                                        if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head'])) {
                                                $this->load->model('project_model');
                                                $params = array();
                                                $params['sort_by'] = "p_name";
                                                if (isset($val['active_only'])) {
                                                        $params['conditions'] = array();
                                                        $params['conditions'][] = array('p_status' => 'Active');
                                                }
                                                $records = $this->project_model->get_records($params);
                                        } else {
                                                $records = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
                                        }



                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="-1">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="-1">Select Project</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['p_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['p_id']) ? ' selected="selected"' : '') . '>' . $row['p_name'] . '</option>';
                                                }
                                        }
                                        if (isset($val['leave'])) {
                                                $ReturnVal[$val['type']] .= '<option value="0" ' . ((isset($val['id']) && $val['id'] == 0) ? ' selected="selected"' : '') . '>On Leave</option>';
                                        }
                                        break;
                                case 'tasks':
                                        $this->load->model('task_model');
                                        $params = array();
                                        $params['conditions'] = array();
                                        $params['conditions'][] = array('t_p_id' => $val['p_id'], 't_parent' => 0, 'tu_u_id' => $u_id, 'tu_removed' => 'No', 't_status != ' => 'Completed');
                                        $records = $this->task_model->get_records_by_assignee($params);
                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="">Select Task</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['t_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['t_id']) ? ' selected="selected"' : '') . '>' . $row['t_title'] . '</option>';
                                                        $subparams = array();
                                                        $subparams['conditions'] = array();
                                                        $subparams['conditions'][] = array('t_parent' => $row['t_id']);
                                                        $subrecords = $this->task_model->get_records($subparams);
                                                        if ($subrecords != null) {
                                                                foreach ($subrecords as $row2) {
                                                                        $ReturnVal[$val['type']] .= '<option value="' . $row2['t_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row2['t_id']) ? ' selected="selected"' : '') . '> &nbsp; &nbsp; &nbsp; ' . $row2['t_title'] . '</option>';
                                                                }
                                                        }
                                                }
                                        }
                                        break;
                        }
                }
                $this->response(array(
                        'status' => "pass",
                        'data' => $ReturnVal
                ));
        }
        public function dashboard_post()
        {
                if (($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') || $this->admin_session['u_type'] == 'Project Leader') {
                        $type = $this->post('type');
                        switch ($type) {
                                case 'present_list':
                                        // $query = $this->db->query("SELECT COUNT(u_id) as total, u_department FROM aa_users, aa_present WHERE u_id = pr_u_id AND pr_date = '" . date("Y-m-d") . "' GROUP BY u_department")->result_array();

                                        //if ($this->admin_session['u_type'] != 'Master Admin') return false;
                                        if ($this->admin_session['u_type'] != ('Master Admin' || 'Bim Head')) return false;
                                        $this->load->model('user_model');

                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("pr_date" => date("Y-m-d"));

                                        $records = $this->user_model->get_records_present($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->user_model->get_records_present($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['u_name'];
                                                $nestedData[] = $single_record['u_email'];
                                                $nestedData[] = $single_record['u_mobile'];
                                                $nestedData[] = $single_record['u_type'];
                                                $nestedData[] = $single_record['u_department'];
                                                /*
                                                $anchors = '<a href="' . base_url("home/project_detail/" . $single_record['p_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['p_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['p_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                $anchors .= '<a href="' . base_url("home/project_contacts/" . $single_record['p_id']) . '" class="btn btn-warning btn-md"><i class="fa fa-phone"></i><a>';
                                                $nestedData[] = $anchors;*/
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                        break;
                                case 'under_watch':
                                        //if ($this->admin_session['u_type'] != 'Master Admin') return false;
                                        if ($this->admin_session['u_type'] != ('Master Admin' || 'Bim Head')) return false;
                                        $this->load->model('project_model');

                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("p_show_dashboard" => "Yes");

                                        $records = $this->project_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->project_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['p_name'];
                                                $nestedData[] = convert_db2display($single_record['p_created'], false);
                                                $nestedData[] = $single_record['p_value'];
                                                $total_exp = $this->project_model->get_total_expense($single_record['p_id']);
                                                $nestedData[] = $total_exp;
                                                $nestedData[] = $single_record['p_value'] - $total_exp;
                                                $nestedData[] = $single_record['p_status'];
                                                $anchors = '<a href="' . base_url("home/project_detail/" . $single_record['p_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['p_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['p_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                $anchors .= '<a href="' . base_url("home/project_contacts/" . $single_record['p_id']) . '" class="btn btn-warning btn-md"><i class="fa fa-phone"></i><a>';
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                        break;
                                case 'leaves':
                                        $this->load->model('leave_model');
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;

                                        $leaderid = $this->post('leaderid');

                                        //
                                        $criteria['select_list'] = "L.*, U.u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days";

                                        if ($leaderid != '') {
                                                $criteria['conditions'] = array();
                                                $criteria['conditions'][] = array('U.u_leader' => $leaderid, 'l_status' => "Pending");
                                        } else {
                                                $criteria['conditions'] = array(array('l_status' => "Pending"));
                                        }
                                        $records = $this->leave_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->leave_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['u_name'];
                                                $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                                $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                                $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                                $nestedData[] = $single_record['l_is_hourly'];
                                                $nestedData[] = $single_record['total_days'];
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                        break;
                                case 'leavestoday':
                                        $this->load->model('leave_model');
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $currentdate = date("Y-m-d");
                                        $leaderid = $this->post('leaderid');
                                        if ($leaderid != '') {
                                                $records = $this->db->query("SELECT L.*, U.u_name, U.u_department FROM aa_users U LEFT JOIN aa_leaves L ON U.u_id = L.l_u_id WHERE L.l_from_date <= '$currentdate' AND L.l_to_date >= '$currentdate' AND L.l_status = 'Approved' and U.u_leader = '$leaderid'")->result_array();
                                        } else
                                                $records = $this->db->query("SELECT L.*, U.u_name, U.u_department FROM aa_users U LEFT JOIN aa_leaves L ON U.u_id = L.l_u_id WHERE L.l_from_date <= '$currentdate' AND L.l_to_date >= '$currentdate' AND L.l_status = 'Approved' ")->result_array();

                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['u_name'];
                                                $nestedData[] = $single_record['u_department'];
                                                if ($single_record['l_is_halfday'] == 'Yes')
                                                        $nestedData[] = $single_record['l_is_halfday'] . " - " . $single_record['l_halfday_time'];
                                                else
                                                        $nestedData[] = $single_record['l_is_halfday'];

                                                if ($single_record['l_is_hourly'] == 'Yes')
                                                        $nestedData[] = $single_record['l_is_hourly'] . " - " . $single_record['l_hourly_time'] . " - " . $single_record['l_hourly_time_hour'] . " Hr";
                                                else
                                                        $nestedData[] = $single_record['l_is_hourly'];
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                        break;
                                case 'basic':
                                        $data = array();
                                        $query = $this->db->select("COUNT(*) as total")->from("aa_projects")->get()->result_array();
                                        $data['box']['total_projects'] = $query[0]['total'];
                                        $query = $this->db->select("COUNT(*) as total")->from("aa_projects")->where("p_status", "Active")->get()->result_array();
                                        $data['box']['active_projects'] = $query[0]['total'];
                                        $query = $this->db->select("COUNT(*) as total")->from("aa_projects")->where("p_status", "Completed")->get()->result_array();
                                        $data['box']['completed_projects'] = $query[0]['total'];
                                        $query = $this->db->select("COUNT(*) as total")->from("aa_users")->get()->result_array();
                                        $data['box']['total_employee'] = ($query[0]['total'] - 2);
                                        $query = $this->db->query("SELECT COUNT(u_id) as total, u_department FROM aa_users, aa_present WHERE u_id = pr_u_id AND pr_date = '" . date("Y-m-d") . "' GROUP BY u_department")->result_array();
                                        $presents = ['Architecture' => 0, 'MEPF' => 0, 'Admin' => 0];
                                        foreach ($query as $key => $val) {
                                                $presents[$val['u_department']] = $val['total'];
                                        }
                                        $r1 = '<thead><tr>';
                                        $r2 = '<tbody><tr>';
                                        foreach ($presents as $key => $val) {
                                                //if ($val['u_department'] == "") continue;
                                                $r1 .= '<th>' . $key . '</th>';
                                                $r2 .= '<td>' . $val . '</td>';
                                        }
                                        $r1 .= '</tr></thead>';
                                        $r2 .= '</tr><tbody>';
                                        $data['rows'] = $r1 . $r2;
                                        $this->response(array(
                                                'status' => "pass",
                                                'data' => $data
                                        ));
                                default:
                                        break;
                        }
                } else {
                        $this->response(array(
                                'status' => "fail",
                                'message' => 'You do not have access for this page.'
                        ));
                }
        }
        public function reports_post()
        {
                //
                $type = $this->post('type');

                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') {
                } else {
                        $this->response(array(
                                'status' => "fail",
                                'message' => 'You do not have access for this page.'
                        ));
                }
                $u_id = $this->admin_session['u_id'];
                $rpt_start = convert_display2db($this->post('rpt_start'));
                $rpt_end = convert_display2db($this->post('rpt_end'));
                switch ($type) {
                        case "leave":
                                $this->load->model('leave_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $txt_search = $this->post('txt_search');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                //$criteria['where'] = (['l_is_hourly' => 'No']);
                                $records = $this->leave_model->get_reports($rpt_start, $rpt_end, $txt_search);
                                $totalFiltered = $totalData = count($records);
                                $result = array();

                                // $criteria_setting['conditions'] = array(array('s_key' => 'total_emp_leaves'));
                                // $records_settings = $this->settings_model->get_records($criteria_setting, 'result');
                                // $total_leaves =  $records_settings[0]['s_value'];
                                foreach ($records as $single_record) {
                                        $l_u_id = $single_record['l_u_id'];
                                        $leaves_approved = $this->leave_model->get_reports_approved($rpt_start, $rpt_end, $l_u_id);
                                        $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;
                                        $leaves_declined = $this->leave_model->get_reports_declined($rpt_start, $rpt_end, $l_u_id);
                                        $declined_leaves = $leaves_declined[0]['declined_leave'] ?? 0;

                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] = (fmod($single_record['final_leave'], 1) !== 0.0) ? $single_record['final_leave'] : (int)$single_record['final_leave'];
                                        $nestedData[] =  (fmod($approved_leaves, 1) !== 0.0) ? $approved_leaves : (int)$approved_leaves;
                                        $nestedData[] = (fmod($declined_leaves, 1) !== 0.0) ? $declined_leaves : (int)$declined_leaves;
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['l_u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start . '\', \'' . $rpt_end . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;
                        case "leave_detail":
                                $this->load->model('leave_model');
                                $l_u_id = $this->post('l_u_id');
                                $rpt_start = $this->post('rpt_start');
                                $rpt_end = $this->post('rpt_end');

                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                // if ($this->admin_session['u_type'] != 'Master Admin' && $this->admin_session['u_type'] != 'Bim Head') {
                                //         $criteria['conditions'] = array(array('l_u_id' => $l_u_id));
                                // }
                                $criteria['conditions'][] = array("l_u_id" => $l_u_id, 'l_is_hourly' => 'No');
                                $criteria['dateseletion'] = array($rpt_start, $rpt_end);

                                $this->load->model('user_model');
                                $criteriausr['conditions'] = array(array('u_id' => $l_u_id));
                                $recordsuser = $this->user_model->get_records($criteriausr, 'result');

                                $records = $this->leave_model->get_records($criteria, 'result');
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->leave_model->get_records($criteria);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $recordsuser[0]['u_name'];
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $datetime1 = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $datetime2 = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $diff = strtotime($datetime2) - strtotime($datetime1);
                                        if ($single_record['l_is_halfday'] == 'Yes')
                                                $interval = abs(round($diff / 86400)) + 0.5;
                                        else
                                                $interval = abs(round($diff / 86400)) + 1;
                                        $nestedData[] = $interval;
                                        $nestedData[] = $single_record['l_message'] . ((!empty($single_record['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $single_record['l_reply'] : "");
                                        $nestedData[] = $single_record['l_status'];
                                        $nestedData[] = $single_record['l_is_halfday'];
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                        $result[] = $nestedData;
                                }
                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result
                                );
                                $this->response($json_data);


                                break;
                        case "leave_hourly_detail":
                                $this->load->model('leave_model');
                                $l_u_id = $this->post('l_u_id');
                                $rpt_start = $this->post('rpt_start');
                                $rpt_end = $this->post('rpt_end');

                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                // if ($this->admin_session['u_type'] != 'Master Admin' && $this->admin_session['u_type'] != 'Bim Head') {
                                //         $criteria['conditions'] = array(array('l_u_id' => $l_u_id));
                                // }
                                $criteria['conditions'][] = array("l_u_id" => $l_u_id, 'l_is_hourly' => 'Yes');
                                $criteria['dateseletion'] = array($rpt_start, $rpt_end);

                                $this->load->model('user_model');
                                $criteriausr['conditions'] = array(array('u_id' => $l_u_id));
                                $recordsuser = $this->user_model->get_records($criteriausr, 'result');

                                $records = $this->leave_model->get_records($criteria, 'result');
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->leave_model->get_records($criteria);
                                $result = array();
                                $total_hrs = 0;
                                foreach ($records as $single_record) {
                                        $total_hrs = $total_hrs + $single_record['l_hourly_time_hour'];
                                        $nestedData = array();
                                        $nestedData[] = $recordsuser[0]['u_name'];
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $datetime1 = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $datetime2 = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        // $diff = strtotime($datetime2) - strtotime($datetime1);
                                        // if ($single_record['l_is_halfday'] == 'Yes')
                                        //         $interval = abs(round($diff / 86400)) + 0.5;
                                        // else
                                        //         $interval = abs(round($diff / 86400)) + 1;
                                        // $nestedData[] = $interval;
                                        $nestedData[] = number_format($single_record['l_hourly_time_hour'], 2);
                                        $nestedData[] = $single_record['l_message'] . ((!empty($single_record['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $single_record['l_reply'] : "");
                                        $nestedData[] = $single_record['l_status'];
                                        //$nestedData[] = $single_record['l_is_halfday'];
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                        $result[] = $nestedData;
                                }
                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result,
                                        "total_hrs" => $total_hrs,
                                );
                                $this->response($json_data);
                                break;
                        case "total_user_leave__hour":
                                $this->load->model('leave_model');
                                $l_u_id = $this->post('l_u_id');
                                $rpt_start = $this->post('rpt_start');
                                $rpt_end = $this->post('rpt_end');

                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                // if ($this->admin_session['u_type'] != 'Master Admin' && $this->admin_session['u_type'] != 'Bim Head') {
                                //         $criteria['conditions'] = array(array('l_u_id' => $l_u_id));
                                // }
                                $criteria['conditions'][] = array("l_u_id" => $l_u_id, 'l_is_hourly' => 'Yes');
                                $criteria['dateseletion'] = array($rpt_start, $rpt_end);

                                $this->load->model('user_model');
                                $criteriausr['conditions'] = array(array('u_id' => $l_u_id));
                                $recordsuser = $this->user_model->get_records($criteriausr, 'result');

                                $records = $this->leave_model->get_records($criteria, 'result');
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->leave_model->get_records($criteria);
                                $result = array();
                                $total_hrs = 0;
                                foreach ($records as $single_record) {
                                        $total_hrs = $total_hrs + $single_record['l_hourly_time_hour'];
                                }
                                $json_data = array(
                                        "status" => 'pass',
                                        "total_hrs" => "<b>" . number_format($total_hrs, 2) . "</b>",
                                );
                                $this->response($json_data);
                                break;
                        case "leaves_total":
                                $this->load->model('leave_model');
                                $this->load->model('settings_model');
                                $this->load->model('user_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $txt_search = $this->post('txt_search');
                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $fromdate = "01-04-" . $this->post('rpt_start');
                                $todate = "31-03-" . $this->post('rpt_end');
                                $rpt_start1 = convert_display2db($fromdate);
                                $rpt_end1 = convert_display2db($todate);

                                //// $criteria['newcondition'] = ' ((u_status = "Deactive" and u_leave_date  >=  "' . $rpt_start1 . '") OR (u_status = "Active") ) and u_name like ' % $txtsearch % '';
                                if ($txt_search == '')
                                        $criteria['newcondition'] = ' ((u_status = "Deactive" and u_leave_date  >=  "' . $rpt_start1 . '") OR (u_status = "Active")) ';
                                else
                                        $criteria['newcondition'] = ' ((u_status = "Deactive" and u_leave_date  >=  "' . $rpt_start1 . '") OR (u_status = "Active")) and u_name like "%' . $txt_search . '%" ';
                                $records = $this->user_model->get_records($criteria, 'result');
                                //$records = $this->leave_model->get_reports($rpt_start1, $rpt_end1);
                                $totalFiltered = $totalData = count($records);
                                $result = array();

                                $criteria_setting['conditions'] = array(array('s_key' => 'total_emp_leaves'));
                                $records_settings = $this->settings_model->get_records($criteria_setting, 'result');
                                $total_leaves =  $records_settings[0]['s_value'];
                                foreach ($records as $single_record) {
                                        $l_u_id = $single_record['u_id'];
                                        $leaves_approved = $this->leave_model->get_reports_approved($rpt_start1, $rpt_end1, $l_u_id);
                                        $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;

                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] =  (fmod($approved_leaves, 1) !== 0.0) ? $approved_leaves : (int)$approved_leaves;
                                        $nestedData[]  =  ($total_leaves - $approved_leaves);
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start1 . '\', \'' . $rpt_end1 . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;

                        case "hourly_leave":
                                $this->load->model('leave_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $txt_search = $this->post('txt_search');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                //$criteria['where'] = (['l_is_hourly' => 'No']);
                                $records = $this->leave_model->get_reports_hourly($rpt_start, $rpt_end, $txt_search);
                                $totalFiltered = $totalData = count($records);
                                $result = array();

                                // $criteria_setting['conditions'] = array(array('s_key' => 'total_emp_leaves'));
                                // $records_settings = $this->settings_model->get_records($criteria_setting, 'result');
                                // $total_leaves =  $records_settings[0]['s_value'];
                                foreach ($records as $single_record) {
                                        $l_u_id = $single_record['l_u_id'];
                                        $leaves_approved = $this->leave_model->get_reports_approved_hourly($rpt_start, $rpt_end, $l_u_id);
                                        $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;
                                        $leaves_declined = $this->leave_model->get_reports_declined_hourly($rpt_start, $rpt_end, $l_u_id);
                                        $declined_leaves = $leaves_declined[0]['declined_leave'] ?? 0;

                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'] ?? null;
                                        $nestedData[] = (fmod($single_record['final_leave'], 1) !== 0.0) ? $single_record['final_leave'] : (int)$single_record['final_leave'];
                                        $nestedData[] =  (fmod($approved_leaves, 1) !== 0.0) ? $approved_leaves : (int)$approved_leaves;
                                        $nestedData[] = (fmod($declined_leaves, 1) !== 0.0) ? $declined_leaves : (int)$declined_leaves;
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['l_u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start . '\', \'' . $rpt_end . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;

                        case "dependency":
                                $p_id = $this->post('p_id');
                                $this->load->model('task_model');
                                $params = array();
                                $params['select_list'] = 't_dependancy';
                                $params['conditions'] = array();
                                $params['conditions'][] = array('t_p_id' => $p_id, 't_dependancy <>' => '');
                                $records = $this->task_model->get_records($params);
                                $this->response(array(
                                        'status' => "pass",
                                        'data' => $records
                                ));
                                break;
                        case "estimated_actual":
                                $this->load->model('task_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $txt_p_status = $this->post('txt_p_status');
                                $txt_search = $this->post('txt_search');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;

                                $records = $this->task_model->get_report_by_project($txt_p_status, $txt_search);
                                $totalFiltered = $totalData = count($records);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $single_record['p_name'];
                                        $nestedData[] = $single_record['t_hours'] ?? 0;
                                        $nestedData[] = $single_record['t_hours_planned'] ?? 0;
                                        $n = $single_record['t_hours_total'];
                                        $whole = floor($n);      // 1
                                        $fraction = $n - $whole; // .25
                                        if ($fraction == '.75')
                                                $total_salary = str_replace($fraction, '.75', '.45');
                                        if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        $nestedData[] = number_format(($whole + $total_salary) ?? 0, 2);
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['p_id'] . '\', \'' . $single_record['p_name'] . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }
                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;
                        case "estimated_actual_detail":
                                $this->load->model('task_model');
                                $p_id = $this->post('p_id');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;

                                $this->load->model('project_model');
                                $criteria['conditions'] = array(array('p_id' => $p_id));
                                $recordsP = $this->project_model->get_records($criteria, 'result');

                                $records = $this->task_model->get_report_by_project_detail($p_id);
                                $totalFiltered = $totalData = count($records);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] =  $recordsP[0]['p_name'];
                                        $nestedData[] = $single_record['u_name'];
                                        // $nestedData[] = $single_record['t_hours'] ?? 0;
                                        $n = $single_record['t_hours'];
                                        $whole = floor($n);      // 1
                                        $fraction = $n - $whole; // .25
                                        if ($fraction == '.75')
                                                $total_salary = str_replace($fraction, '.75', '.45');
                                        if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        $nestedData[] = number_format(($whole + $total_salary) ?? 0, 2);
                                        $result[] = $nestedData;
                                }
                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;
                        case "attendence":
                                $sub_type = $this->post('sub_type');
                                if ($sub_type == "daily") $rpt_end = $rpt_start;

                                $this->load->model('timesheet_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;

                                $txt_search = $this->post('txt_search');
                                $records = $this->timesheet_model->get_report_all($rpt_start, $rpt_end, $txt_search);
                                $totalFiltered = $totalData = count($records);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        $n = $single_record['work_hours'];
                                        $whole = floor($n);      // 1
                                        $fraction = $n - $whole; // .25
                                        if ($fraction == '.75')
                                                $total_salary = str_replace($fraction, '.75', '.45');
                                        if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        $nestedData[] = number_format($whole + $total_salary, 2);
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="postFormData(\'' . $single_record['u_id'] . '\', \'' . $single_record['u_name'] . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }
                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;
                        case "timesheet":
                                $sub_type = $this->post('sub_type');
                                $u_id = $this->post('u_id');
                                if ($sub_type == "daily") $rpt_end = $rpt_start;

                                $this->load->model('timesheet_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['sort_by'] = 'at_date,at_start';
                                $criteria['conditions'][] = array("at_u_id" => $u_id, "at_date >= " => $rpt_start, "at_date <= " => $rpt_end);

                                $this->load->model('user_model');
                                $criteriausr['conditions'] = array(array('u_id' => $u_id));
                                $recordsuser = $this->user_model->get_records($criteriausr, 'result');

                                $records = $this->timesheet_model->get_records($criteria, 'result');
                                //echo $this->db->last_query();
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->timesheet_model->get_records($criteria);
                                $result = array();
                                $whours = 0;
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $recordsuser[0]['u_name'];
                                        $nestedData[] = $single_record['p_name'] ?? "Leave";
                                        $nestedData[] = $single_record['t_title'] ?? "Leave";
                                        $nestedData[] = convert_db2display($single_record['at_date']);
                                        $nestedData[] = RevTime($single_record['at_start']) . " - " . RevTime($single_record['at_end']);
                                        $whours = $whours + (($single_record['at_end'] - $single_record['at_start']) / 60);
                                        $nestedData[] = $single_record['at_comment'];
                                        $result[] = $nestedData;
                                }
                                $whole = floor($whours);      // 1
                                $fraction = $whours - $whole; // .25
                                if ($fraction == '.75')
                                        $total_salary = str_replace($fraction, '.75', '.45');
                                if ($fraction == '.25')
                                        $total_salary = str_replace($fraction, '.25', '.15');
                                if ($fraction == '.50')
                                        $total_salary = str_replace($fraction, '.50', '.30');
                                if ($whours > 0)
                                        $total_hrs = "<b>Total Hours Worked :  " . (number_format($whole + $total_salary, 2)) . " hr</b>";
                                else
                                        $total_hrs = '';
                                $new = array("", "", "", "", $total_hrs, "");
                                array_push($result, $new);
                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;

                        case "projects":
                                $t_id = $this->post('t_id');
                                $t_p_id = $this->post('t_p_id');
                                $t_parent = $this->post('t_parent');
                                $this->load->model('task_model');
                                $this->load->library('General');

                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $txt_p_status = $this->post('txt_p_status');

                                $txt_projects = $this->post('txt_projects');

                                $criteria = array();
                                $criteria['sort_by'] = 'p_name';
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                if ($txt_p_status != '') {
                                        $criteria['conditions'][] = array("p_status LIKE " => "%" . $txt_p_status . "%");
                                }
                                if ($txt_projects != '') {
                                        //$criteria['conditions'][] = array("p_id  " => $txt_projects);
                                        $criteria['newcondition'] = ' (p_number like "%' . $txt_projects . '%" OR p_name  like  "%' . $txt_projects . '%")';
                                        //$criteria['conditions'][] = array("p_name LIKE " => "%" . $txt_projects . "%");

                                }

                                if ($t_parent > 0) {
                                        $criteria['conditions'][] = array("t_parent" => $t_parent);
                                } else {
                                        $criteria['conditions'][] = array("t_parent" => 0);
                                }
                                if ($t_p_id > 0) {
                                        $criteria['conditions'][] = array("t_p_id" => $t_p_id);
                                }
                                $records = $this->task_model->get_records_projects($criteria, 'result');
                                $sql = $this->db->last_query();
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->task_model->get_records_projects($criteria);
                                $result = array();
                                $i = 1;
                                foreach ($records as $single_record) {
                                        //if (!$this->general->ValidateTaskAssignment($single_record['t_id'], $this->admin_session)) continue;

                                        $nestedData = array();
                                        $nestedData[] = $i++;
                                        if ($t_p_id <= 0) {
                                                $nestedData[] = $single_record['p_name'];
                                                $nestedData[] = $single_record['p_number'];
                                                $nestedData[] = $single_record['p_value'];
                                                $nestedData[] = $single_record['p_cat'];
                                                $nestedData[] = $single_record['p_status'];
                                        }
                                        $tasks_list = $this->task_model->get_project_tasks($single_record['p_id']);
                                        //print_r($tasks_list);
                                        if (!empty($tasks_list)) {
                                                $task_text = '';
                                                foreach ($tasks_list as $tasks) {
                                                        $task_title =  $tasks['t_title'];
                                                        $task_priority =   $tasks['t_priority'];
                                                        $task_posted_date = convert_db2display($tasks['t_createdate']);
                                                        $task_posted_by = $tasks['u_name'];
                                                        //$assigns = $this->task_model->get_assigns($tasks['t_id']);
                                                        $assigns = $this->db->query("SELECT TU.*, U.u_name , U.u_id FROM aa_task2user TU LEFT JOIN aa_users U ON TU.tu_u_id = U.u_id  where TU.tu_t_id = '{$tasks['t_id']}'")->result_array();
                                                        $stings = '';
                                                        foreach ($assigns as $assign) {
                                                                $assign_hrs = $this->db->query("SELECT SUM( ((atte.at_end - atte.at_start) / 60)) as TOTALwhours FROM aa_attendance as atte , aa_users U WHERE atte.at_t_id = '{$assign['tu_t_id']}' AND atte.at_u_id  = U.u_id and U.u_id = '{$assign['tu_u_id']}'")->result_array();
                                                                $assign_hrs['TOTALwhours'] = $assign_hrs[0]['TOTALwhours'] ?? 0;
                                                                $n = $assign_hrs[0]['TOTALwhours'];
                                                                $whole = floor($n);      // 1
                                                                $fraction = $n - $whole; // .25
                                                                if ($fraction == '.75')
                                                                        $total_salary = str_replace($fraction, '.75', '.45');
                                                                if ($fraction == '.25')
                                                                        $total_salary = str_replace($fraction, '.25', '.15');
                                                                if ($fraction == '.50')
                                                                        $total_salary = str_replace($fraction, '.50', '.30');

                                                                $stings = $stings . $assign['u_name'] . " - <b>" . number_format($whole + $total_salary, 2) . " hr</b>  , ";
                                                        }
                                                        $task_text = $task_text . "\r\r<b>Task Title: " . $task_title . "</b>\r<b>Priority: </b>" . $task_priority . "\r<b>Posted Date: </b>" . $task_posted_date . "\r<b>Posted By: </b>" . $task_posted_by . "\r<b>Assigns: </b>" . $stings;
                                                }
                                                $nestedData[] = $task_text;
                                        } else {
                                                $nestedData[] = "No Task";
                                        }

                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $sql,
                                        "data" => $result
                                );
                                $this->response($json_data);

                                break;
                        case "projects_employee":
                                $this->load->model('task_model');
                                $this->load->library('General');
                                $t_id = $this->post('t_id');
                                $t_p_id = $this->post('t_p_id');
                                $t_parent = $this->post('t_parent');
                                if ($t_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('t_id' => $t_id));
                                        $record = $this->task_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                //unset($record[0]['t_title']);
                                                $assigns = $this->task_model->get_assigns($t_id);
                                                $files = $this->task_model->get_files(array('tf_t_id' => $t_id));
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0],
                                                        'assigns' => $assigns,
                                                        'files' => $files,
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $txt_projects = $this->post('txt_projects');
                                        $txt_status = $this->post('txt_status');
                                        $txt_employee =  $this->post('txt_employee');

                                        $criteria = array();
                                        $criteria['sort_by'] = 't_priority';
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        if ($txt_projects != '') {
                                                $criteria['conditions'][] = array("p_name LIKE " => "%" . $txt_projects . "%");
                                        }
                                        if ($txt_status != '') {
                                                $criteria['conditions'][] = array("t_status" => $txt_status);
                                        }
                                        $criteria['conditions'][] = array("p_status" => "Active");

                                        if ($t_parent > 0) {
                                                $criteria['conditions'][] = array("t_parent" => $t_parent);
                                        } else {
                                                $criteria['conditions'][] = array("t_parent" => 0);
                                        }
                                        if ($t_p_id > 0) {
                                                $criteria['conditions'][] = array("t_p_id" => $t_p_id);
                                        }
                                        $records = $this->task_model->get_records($criteria, 'result');
                                        $sql = $this->db->last_query();
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->task_model->get_records($criteria);
                                        $result = array();
                                        $i = 1;
                                        foreach ($records as $single_record) {
                                                if (!$this->general->ValidateTaskAssignment($single_record['t_id'], $this->admin_session)) continue;

                                                if ($txt_employee != null) {
                                                        $nestedData = array();
                                                        $assigns = $this->task_model->get_assigns($single_record['t_id'], 0, false, $txt_employee);
                                                        if ($assigns != null) {
                                                                $nestedData[] = $i++;
                                                                if ($t_p_id <= 0) {
                                                                        $nestedData[] = $single_record['p_name'];
                                                                }
                                                                $nestedData[] = $single_record['t_title'];
                                                                $nestedData[] = $single_record['t_priority'];
                                                                $nestedData[] = convert_db2display($single_record['t_createdate']);
                                                                $nestedData[] = $single_record['u_name'];
                                                                $assigns = $this->task_model->get_assigns($single_record['t_id']);
                                                                $assigns = array_column($assigns, 'u_name');
                                                                $assigns = implode(", ", $assigns);
                                                                $nestedData[] = $assigns;
                                                                if ($t_parent > 0) {
                                                                        $nestedData[] = $single_record['t_hours'];
                                                                }
                                                                $nestedData[] = $single_record['t_status'];
                                                                $anchors = "";
                                                                //$anchors .= '<a href="' . site_url("home/task/view/{$single_record['t_p_id']}/" . $single_record['t_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                                $anchors .= '<a href="javascript://" onClick="showData(\'' . $single_record['t_id'] . '\', \'' . $single_record['t_p_id'] . '\', \'' . $single_record['p_name'] . '\', \'' . $single_record['t_title'] . '\',  \'' . $txt_employee  . '\')" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';

                                                                $nestedData[] = $anchors;
                                                                $result[] = $nestedData;
                                                        }
                                                } else {
                                                        $nestedData = array();
                                                        $nestedData[] = $i++;
                                                        if ($t_p_id <= 0) {
                                                                $nestedData[] = $single_record['p_name'];
                                                        }
                                                        $nestedData[] = $single_record['t_title'];
                                                        $nestedData[] = $single_record['t_priority'];
                                                        $nestedData[] = convert_db2display($single_record['t_createdate']);
                                                        $nestedData[] = $single_record['u_name'];
                                                        $assigns = $this->task_model->get_assigns($single_record['t_id']);
                                                        $assigns = array_column($assigns, 'u_name');
                                                        $assigns = implode(", ", $assigns);
                                                        $nestedData[] = $assigns;
                                                        if ($t_parent > 0) {
                                                                $nestedData[] = $single_record['t_hours'];
                                                        }
                                                        $nestedData[] = $single_record['t_status'];
                                                        $anchors = "";
                                                        $anchors .= '<a href="javascript://" onClick="showData(\'' . $single_record['t_id'] . '\', \'' . $single_record['t_p_id'] . '\', \'' . $single_record['p_name'] . '\', \'' . $single_record['t_title'] . '\')" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';

                                                        $nestedData[] = $anchors;
                                                        $result[] = $nestedData;
                                                }
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                //"sql" => $sql,
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                        case "project_task_detail":
                                break;
                        case "projectprofitloss":
                                $this->load->model('project_model');
                                $p_id = $this->post('p_id');
                                if ($p_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('p_id' => $p_id));
                                        $record = $this->project_model->get_records($criteria, 'result');
                                        //get_project_expense
                                        if (isset($record[0])) {
                                                $file_name = "./assets/logos/plogo_" . $record[0]['p_id'] . ".jpg";
                                                if (file_exists($file_name)) {
                                                        $record[0]['photo']  = base_url("assets/logos/plogo_" . $record[0]['p_id']) . ".jpg";
                                                } else {
                                                        $record[0]['photo']  = "";
                                                }
                                                $pe = '';
                                                if ($this->admin_session['u_type'] == 'Master Admin') {
                                                        $criteria = array();
                                                        $criteria['select_list'] = 'pe_val, pe_lbl';
                                                        $criteria['conditions'] = array(array('pe_p_id' => $p_id));
                                                        $pe = $this->project_model->get_project_expense($criteria, 'result');
                                                }
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0],
                                                        'pe' => $pe
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $txt_search = $this->post('txt_search');
                                        $txt_p_cat = $this->post('txt_p_cat');
                                        $txt_p_status = $this->post('txt_p_status');


                                        $criteria = array();
                                        $criteria['sort_by'] = "p_number";
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("p_cat" => $txt_p_cat);

                                        if (!empty($txt_p_status))
                                                $criteria['conditions'][] = array("p_status" => $txt_p_status);

                                        if ($txt_search != null) {
                                                //$criteria['conditions'][] = array("p_number LIKE " => "%" . $txt_search . "%");
                                                //$criteria['or_conditions'][] = array("p_name LIKE " => "%" . $txt_search . "%");
                                                $criteria['newcondition'] = ' (p_number like "%' . $txt_search . '%" OR p_name  like  "%' . $txt_search . '%")';
                                        }

                                        $records = $this->project_model->get_records($criteria, 'result');
                                        $sql = $this->db->last_query();
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->project_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['p_number'];
                                                $nestedData[] = $single_record['p_name'];
                                                $nestedData[] = $single_record['p_address'];
                                                if ($this->admin_session['u_type'] == 'Master Admin') {
                                                        $nestedData[] = $single_record['p_value'];
                                                        $total_exp = $this->project_model->get_total_expense($single_record['p_id']);
                                                        $nestedData[] = $total_exp;
                                                        $nestedData[] = $single_record['p_value'] - $total_exp;
                                                }
                                                $nestedData[] = $single_record['p_status'];
                                                //$anchors = '<a href="' . base_url("home/project_detail/" . $single_record['p_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['p_id'] . '\', \'' . $single_record['p_name'] . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                //"sql" => $sql,
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }
                                break;

                        case "pemployeedetail":
                                $this->load->model('project_model');
                                $p_id = $this->post('p_id');

                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $total = 0;
                                $records = $this->project_model->get_emply_salary($p_id);
                                $criteria['conditions'] = array(array('p_id' => $p_id));
                                $recordsP = $this->project_model->get_records($criteria, 'result');

                                if (empty($records)) {
                                        $nestedData = array();
                                        $nestedData[] = $recordsP[0]['p_number'];
                                        $nestedData[] = $recordsP[0]['p_name'];
                                        $nestedData[] = "Not Find";
                                        $nestedData[] = "0";
                                        $result[] = $nestedData;
                                        //$this->response(array('status' => 'fail', 'message' => "Employees are not found"));
                                }
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $recordsP[0]['p_number'];
                                        $nestedData[] = $recordsP[0]['p_name'];
                                        $nestedData[] = $single_record['Username'];
                                        $nestedData[] = $single_record['final_salary'];
                                        $total = $total + $single_record['final_salary'];
                                        $result[] = $nestedData;
                                }
                                //$result[] = array_push($result[],$total);
                                $json_data = array(
                                        "draw" => intval($draw),
                                        //"recordsTotal" => intval($totalData),
                                        //"recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result,
                                        "total" => $total
                                );
                                $this->response($json_data);
                                break;

                        case "employee_salary_list":
                                $this->load->model('user_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');

                                $txt_search = $this->post('txt_search');
                                $txt_U_Type = $this->post('txt_U_Type');
                                $txt_U_Status = $this->post('txt_U_Status');

                                $criteria = array();
                                $criteria['sort_by'] = "u_id"; // Sort order by new employee
                                $criteria['sort_type'] = "desc";
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("u_id <>" => "1");

                                if ($txt_search != null) {
                                        //$criteria['conditions'][] = array("u_name LIKE " => "%" . $txt_search . "%");
                                        //$criteria['or_conditions'][] = array("u_username LIKE " => "%" . $txt_search . "%");
                                        $criteria['newcondition'] = ' (u_name like "%' . $txt_search . '%" OR u_username  like  "%' . $txt_search . '%")';
                                }
                                if ($txt_U_Type != null) {
                                        $criteria['conditions'][] = array("u_type LIKE " => "%" . $txt_U_Type . "%");
                                }
                                if ($txt_U_Status != null) {
                                        $criteria['conditions'][] = array("u_status LIKE " => $txt_U_Status);
                                } else {
                                        $criteria['conditions'][] = array("u_status LIKE " => 'Active');
                                }



                                $records = $this->user_model->get_records($criteria, 'result');
                                $criteria['result_type'] = 'count_records';
                                $totalFiltered = $totalData = $this->user_model->get_records($criteria);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_username'];
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] = $single_record['u_salary'];
                                        $nestedData[] = $single_record['u_type'];
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['u_id'] . '\', \'' . $single_record['u_name'] . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        //"sql" => $this->db->last_query(),
                                        "data" => $result
                                );
                                $this->response($json_data);

                        case "employee_salary_detail":
                                $this->load->model('user_model');
                                $u_id = $this->post('u_id');
                                if ($u_id > 0) {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');

                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;

                                        $criteriausr['conditions'] = array(array('U.u_id' => $u_id));
                                        $criteriausr['select_list'] = "US.*, U.u_name";
                                        $criteriausr['sort_by'] = "US.id"; // Sort order by new employee
                                        $criteriausr['sort_type'] = "desc";
                                        $records = $this->user_model->get_users_salary($criteriausr, 'result');
                                        $totalFiltered = $totalData = count($records);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['u_name'];
                                                $nestedData[] = $single_record['u_start_date'];
                                                $nestedData[] = $single_record['u_end_date'];
                                                $nestedData[] = $single_record['u_salary'];
                                                $result[] = $nestedData;
                                        }
                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                        break;
                                }
                }
        }
        public function messages_post()
        {
                $this->load->model('message_model');
                $act = $this->post('act');
                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head' || $this->admin_session['u_type'] == 'Project Leader') {
                        $is_admin = true;
                } else {
                        if ($act == "add" || $act == "del") {
                                $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                        }
                }
                switch ($act) {
                        case "read":
                                $mu_me_id = $this->post('me_id');
                                $mu_u_id = $this->admin_session['u_id'];
                                $this->db->where(['mu_me_id' => $mu_me_id, 'mu_u_id' => $mu_u_id])->update("aa_message_users", ["mu_read" => 1]);
                                $this->response(array(
                                        'status' => 'pass',
                                        'message' => 'Message is saved.'
                                ));
                                break;
                        case "add":
                                $me_id = $this->post('me_id');
                                $me_datetime = $this->post('me_datetime');
                                $me_text = $this->post('me_text');
                                $me_p_id = $this->post('me_p_id');
                                $data = array(
                                        'me_datetime' => date("Y-m-d H:i:s"),
                                        'me_text' => $me_text,
                                        'me_p_id' => $me_p_id,
                                );

                                if ($me_id > 0) {
                                        $data['me_id'] = $me_id;
                                }
                                try {
                                        $admin_id = $this->message_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Message is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "del":
                                $me_id = $this->post('me_id');
                                if ($me_id > 0) {
                                        try {
                                                $this->message_model->delete_records(array('me_id' => $me_id));
                                                //[PENDING] add Delete
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Message has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $me_id = $this->post('me_id');
                                if ($me_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('me_id' => $me_id));
                                        $record = $this->message_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $record[0]['me_datetime'] = convert_db2display($record[0]['me_datetime']);
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');


                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;

                                        if (empty($is_admin)) {
                                                $criteria['u_id']  = $this->admin_session['u_id'];
                                        }

                                        $records = $this->message_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->message_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = date("M d, Y", strtotime($single_record['me_datetime']));
                                                if ($single_record['leave_message'] == 'No')
                                                        $nestedData[] = $single_record['p_name'];
                                                else
                                                        $nestedData[] = 'Leave Approval Message';
                                                $nestedData[] = $single_record['me_text'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['me_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['me_id'] . '\')"><i class="fa fa-trash"></i><a>';
                                                if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head']))
                                                        $nestedData[] = $anchors;
                                                else
                                                        $nestedData[] = "";
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }
        public function settings_post()
        {

                $this->load->model('settings_model');
                $act = $this->post('act');
                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') {
                        $is_admin = true;
                } else {
                        if ($act == "add" || $act == "del") {
                                $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                        }
                }
                switch ($act) {
                        case "add":
                                $id = $this->post('id');
                                $s_value = $this->post('s_value');
                                $data = array(
                                        's_value' => $s_value,

                                );

                                if ($id > 0) {
                                        $data['id'] = $id;
                                }
                                try {
                                        $admin_id = $this->settings_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Setting is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }

                                break;
                        case "list":
                                $id = $this->post('id');
                                if ($id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('id' => $id));
                                        $record = $this->settings_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');


                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;

                                        // if (empty($is_admin)) {
                                        //         $criteria['u_id']  = $this->admin_session['u_id'];
                                        // }

                                        $records = $this->settings_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->settings_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['s_title'];
                                                $nestedData[] = $single_record['s_key'];
                                                $nestedData[] = $single_record['s_value'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head']))
                                                        $nestedData[] = $anchors;
                                                else
                                                        $nestedData[] = "";
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }
}