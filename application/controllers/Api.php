<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

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
                                        // if (!$this->authorization->is_bim_head_or_higher($this->admin_session)) {
                                        //         $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                                        // }
                                        if (!$this->authorization->is_bim_head_or_higher($this->admin_session)) {
                                                if ($this->admin_session['u_type'] == 'Project Leader') {
                                                        if ($method != 'employees') {
                                                                $user_id = $this->admin_session['u_id'];
                                                                $assigned_project_ids = $this->get_assigned_project_ids($user_id);
                                                                if (!empty($assigned_project_ids)) {
                                                                        //$this->db->where_in('p_id', $assigned_project_ids);
                                                                        $this->load->model('project_model');
                                                                        $this->project_model->getProjectsForUser($assigned_project_ids);
                                                                } else {
                                                                        $this->db->where('1=0'); // no access to any project
                                                                }
                                                        } else {
                                                        }
                                                }
                                                else
                                                        {
                                                        $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                                                        }
                                                
                                        }
                                }
                        } else {
                                $this->response(array('status' => 'session', 'message' => 'Your session expired. Please relogin.'));
                        }
                }
        }
        private function get_assigned_project_ids($user_id)
        {
                $this->db->select('p_id');
                $this->db->from('aa_projects');
                //$this->db->where('p_leader', $user_id);
                $this->db->where("FIND_IN_SET(" . (int)$user_id . ", p_leader) >", 0, false); // Use raw SQL
                $result = $this->db->get()->result_array();
                return array_column($result, 'p_id');
        }
        public function leaves_post()
        {
                $this->load->model('leave_model');
                $act = $this->post('act');
                if (!$this->authorization->is_project_leader_or_higher($this->admin_session)) {
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
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Project Leader'])) {

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
                                                                'leave_message' => 'Yes',

                                                        );
                                                        try {
                                                                $admin_id = $this->message_model->saveLeaveMessage($data, $results[0]['Department']);
                                                        } catch (Exception $ex) {
                                                                $this->response(array(
                                                                        'status' => 'fail',
                                                                        'type' => 'popup',
                                                                        'message' => $ex->getMessage()
                                                                ));
                                                        }
                                                }
                                                $this->leave_model->update(array('l_status' => $l_status . "d", 'l_reply' => $l_reply, 'l_approved_by' => $this->admin_session['u_type'],  'l_approved_by_id' => $l_u_id, 'l_action' => 'Yes'), array('l_id' => $l_id, 'l_status' => 'Pending'));
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
                                        $decimalTimes = array(0, 0.15, 0.30, 0.45);
                                        $whole = floor($l_hourly_time_hour);     // 1
                                        $decimal = (string)fmod($l_hourly_time_hour, 1); //0.25

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
                                        $data['l_approved_by'] = '';
                                        $data['l_approved_by_id'] = '';
                                        $data['l_reply'] = '';
                                        $this->load->model('message_model');
                                        $sql = "SELECT DISTINCT a.tu_p_id as projectid , c.u_name as Username , c.u_department as Department FROM aa_task2user as a , aa_leaves as b , aa_users as c WHERE  a.tu_u_id  = b.l_u_id  and a.tu_u_id = c.u_id and b.l_id = '{$l_id}'";
                                        $query = $this->db->query($sql);
                                        $results = $query->result_array();

                                        $this->load->model('leave_model');
                                        $criteria['conditions'] = array(array('l_id' => $l_id));
                                        $existingLeave = $this->leave_model->get_records($criteria, 'result');

                                        $department = 'N/A';
                                        $username = 'N/A';
                                        if (!empty($results)) {
                                                $department = $results[0]['Department'];
                                                $username = $results[0]['Username'];
                                        }

                                        $existingFromDate = '';
                                        $existingToDate = '';
                                        $existingApprovedBy = '';
                                        $existingReply = '';

                                        if (!empty($existingLeave)) {
                                                $existingFromDate = $existingLeave[0]['l_from_date'];
                                                $existingToDate = $existingLeave[0]['l_to_date'];
                                                $existingApprovedBy = $existingLeave[0]['l_approved_by'];
                                                $existingReply = $existingLeave[0]['l_reply'];
                                        }

                                        $me_text = "<b> Department  - " . $department . "</b> <br/>
                <b> Leave Date Is Updated By - " . $username . "</b> <br/>
                Message - " . $l_message . "<br/>
                Existing DateFrom : " . $existingFromDate . " && Existing ToDate : " . $existingToDate . "<br/>
                New Leave FromDate : " . convert_display2db($l_from_date) . " && New Leave ToDate : " . convert_display2db($l_to_date);

                                        $dataMessage = array(
                                                'me_datetime' => date("Y-m-d H:i:s"),
                                                'me_text' => $me_text,
                                                'me_p_id' => 0,
                                                'leave_message' => 'Yes',

                                        );



                                        //unset($data['l_create_date']);
                                        //unset($data['l_status']);
                                        ////unset($data['l_u_id']);
                                }
                                try {
                                        // if (($existingLeave[0]['l_from_date'] != (convert_display2db($l_from_date))) || ($existingLeave[0]['l_to_date'] != (convert_display2db($l_to_date)))) {
                                        //         if (($existingLeave[0]['l_approved_by'] != '') && ($existingLeave[0]['l_reply'] != '')) {
                                        //                 $leave_Message = $this->message_model->UpdateLeaveMessageToBimHead($dataMessage);
                                        //         }
                                        // }
                                        if (!empty($existingLeave)) {
                                                if (($existingLeave[0]['l_from_date'] != (convert_display2db($l_from_date))) || ($existingLeave[0]['l_to_date'] != (convert_display2db($l_to_date)))) {
                                                        if (($existingLeave[0]['l_approved_by'] != '') && ($existingLeave[0]['l_reply'] != '')) {
                                                                $leave_Message = $this->message_model->UpdateLeaveMessageToBimHead($dataMessage);
                                                        }
                                                }
                                        }

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
                                                //$this->leave_model->delete_records(array('l_id' => $l_id, 'l_u_id' => $l_u_id, 'l_status' => 'Pending'));
                                                $this->leave_model->delete_records(array('l_id' => $l_id));
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
                                        if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Project Leader'])) {
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
                                                //$single_record['l_status'] == 'Pending'
                                                if ($single_record['l_u_id'] == $l_u_id) {
                                                        $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['l_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                        if ($single_record['l_action'] == 'No')
                                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['l_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                }
                                                if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
                                                        if ($single_record['l_status'] == 'Pending') {
                                                                $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Approve\')"><i class="fa fa-thumbs-up"></i><a>&nbsp; ';
                                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Decline\')"><i class="fa fa-thumbs-down"></i><a>';
                                                        } else {
                                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['l_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                        }
                                                }
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Project Leader'])) {
                                                        if ($single_record['l_u_id'] != $l_u_id && $single_record['l_status'] == 'Pending') {
                                                                $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Approve\')"><i class="fa fa-thumbs-up"></i><a>&nbsp; ';
                                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Decline\')"><i class="fa fa-thumbs-down"></i><a>&nbsp; ';
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
                if (!$this->authorization->is_bim_head_or_higher($this->admin_session)) {
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
                                $u_app_auth = $this->post('u_app_auth');
                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
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
                                                'u_app_auth' => $u_app_auth,
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
                                                'u_app_auth' => $u_app_auth,
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
                                        if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin']))
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
                                        //$criteria['conditions'][] = array("u_id <>" => "1");
                                        $criteria['conditions'][] = array("u_type !=" => "Master Admin");
                                        $criteria['conditions'][] = array("u_type !=" => "Super Admin");
                                        $criteria['conditions'][] = array("u_type <>" => "Associate User");
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
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin']))
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

                                        $whole = floor($single_record['work_hour_total']);      // 1
                                        $fraction = $single_record['work_hour_total'] - $whole; // .25
                                        if ($fraction == '.75')
                                                $work_hour_total = str_replace($fraction, '.75', '.45');
                                        else if ($fraction == '.25')
                                                $work_hour_total = str_replace($fraction, '.25', '.15');
                                        else if ($fraction == '.50')
                                                $work_hour_total = str_replace($fraction, '.50', '.30');
                                        else
                                                $work_hour_total = $fraction;



                                        $nestedData[] = number_format($whole + $work_hour_total, 2);

                                        if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
                                                $nestedData[] = $single_record['u_salary'];
                                                //$nestedData[] = number_format($single_record['work_hour_total'] * $single_record['u_salary'], 2);
                                                $nestedData[] = $single_record['final_salary'];
                                        } else {
                                                $nestedData[] = 0;
                                                $nestedData[] = 0;
                                        }
                                        $result[] = $nestedData;
                                        if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin']))
                                                //$total = $total + $single_record['work_hour_total'] * $single_record['u_salary'];
                                                $total = $total + $single_record['final_salary'];
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
                                $p_leaders = $this->post('p_leader');
                                $p_leader = is_array($p_leaders) ? implode(',', $p_leaders) : null;

                                //print_r($pe_lbl);
                                if ($p_show_dashboard == "") $p_show_dashboard = "No";

                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
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
                                                'p_leader' => $p_leader,
                                        );
                                } else {
                                        $data = array(
                                                'p_number' => $p_number,
                                                'p_name' => $p_name,
                                                //'p_value' => $p_value,
                                                'p_contact' => $p_contact,
                                                'p_cat' => $p_cat,
                                                'p_status' => $p_status,
                                                'p_address' => $p_address,
                                                'p_scope' => $p_scope,
                                                'p_show_dashboard' => $p_show_dashboard,
                                                'p_leader' => $p_leader,
                                        );
                                }

                                if ($p_id > 0) {
                                        $data['p_id'] = $p_id;
                                } else {
                                }
                                $errors = validate_image($_FILES, $this->allowed_files_images);
                                try {
                                        if ($errors != "") throw new Exception($errors);
                                        $p_id = $this->project_model->save($data);
                                        if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
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
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
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
                                        $txt_p_leader = $this->post('txt_p_leader');

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
                                        if (!empty($txt_p_leader))
                                                //$criteria['conditions'][] = array("p_leader" => $txt_p_leader);
                                                $criteria['newcondition'] = "FIND_IN_SET(" . $txt_p_leader . ", p_leader)";

                                        $records = $this->project_model->get_records($criteria, 'result');
                                        $sql = $this->db->last_query();
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->project_model->get_records($criteria);
                                        $result = array();
                                        $this->load->model('user_model');
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['p_number'];
                                                $nestedData[] = $single_record['p_name'];
                                                $nestedData[] = $single_record['p_address'];
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
                                                        $nestedData[] = $single_record['p_value'];
                                                        $total_exp = $this->project_model->get_total_expense($single_record['p_id']);
                                                        $nestedData[] = $total_exp;
                                                        $nestedData[] = $single_record['p_value'] - $total_exp;
                                                }
                                                $nestedData[] = $single_record['p_status'];
                                                $leader_ids = explode(',', $single_record['p_leader']);
                                                $leader_names = [];
                                                foreach ($leader_ids as $lid) {
                                                        $leader = $this->user_model->get_user_by_id(trim($lid));
                                                        if ($leader) {
                                                                $leader_names[] = $leader->u_name;
                                                        }
                                                }
                                                $nestedData[] = !empty($leader_names) ? implode(', ', $leader_names) : 'N/A';
                                                $anchors = '<a href="' . base_url("home/project_detail/" . $single_record['p_id']) . '" class="btn btn-primary btn-md"><i class="fa fa-eye"></i><a>&nbsp; ';

                                                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head' || $this->admin_session['u_type'] == 'Super Admin') {
                                                        $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['p_id'] . '\',\'' . $this->admin_session['u_type'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['p_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                        $anchors .= '<a href="' . base_url("home/project_contacts/" . $single_record['p_id']) . '" class="btn btn-warning btn-md"><i class="fa fa-phone"></i><a>';
                                                }

                                                
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
        public function weeklywork_post()
        {
                $act = $this->post('act');
                $this->load->model('weeklywork_model');
                switch ($act) {
                        case "list":
                                $leader_id = $this->admin_session['u_id']; // from session
                                $user_role = $this->admin_session['u_type'];
                                $from_date = $this->post('from_date');
                                $to_date = $this->post('to_date');
                                $project_id = $this->post('project_id');
                                $filter_status = $this->post('filter_status');
                                if (in_array($user_role, ['Bim Head', 'Master Admin'])) {
                                        $records = $this->weeklywork_model->get_weekly_work_list(null, $from_date, $to_date, $project_id, $filter_status);
                                } else {
                                        $records = $this->weeklywork_model->get_weekly_work_list($leader_id, $from_date, $to_date, $project_id, $filter_status);
                                }
                                //$records = $this->weeklywork_model->get_weekly_work_list($leader_id, $from_date, $to_date);

                                $result = array();
                                foreach ($records as $rec) {
                                        $nestedData = array();
                                        $nestedData[] = convert_db2display($rec['week_from']) . " to " . convert_db2display($rec['week_to']);
                                        $nestedData[] = $rec['p_name'];
                                        $nestedData[] = $rec['task_name'];
                                        $nestedData[] = convert_db2display($rec['submission_date']);
                                        // $nestedData[] = $rec['dependency_type'];
                                        // if ($rec['dependency_type'] == "Internal")
                                        //         $nestedData[] = $rec['dep_leader_names'] ?? '';
                                        // else
                                        //         $nestedData[] = $rec['dependency_text'] ?? '';

                                        $nestedData[] = $rec['no_of_persons'];
                                        $nestedData[] = $rec['assigned_users'] ?? '-';
                                        $nestedData[] = $rec['status'];
                                        if (in_array($user_role, ['Bim Head', 'Master Admin'])) {
                                                $nestedData[] = $rec['created_by'];
                                        }
                                        $anchors = '';
                                        //if ($rec['leader_id'] == $leader_id) {
                                        $anchors .= '<a href="javascript://" class="btn btn-success btn-sm" onclick="editWeeklyWork(' . $rec['w_id'] . ')"><i class="fa fa-edit"></i></a> ';
                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-sm" onclick="deleteWeeklyWork(' . $rec['w_id'] . ')"><i class="fa fa-trash"></i></a>';
                                        $nestedData[] = $anchors;
                                        //}



                                        //$anchors = '<a href="javascript://" class="btn btn-success btn-sm" onclick="editWeeklyWork(' . $rec['w_id'] . ')"><i class="fa fa-edit"></i></a> ';
                                        // $anchors .= '<a href="javascript://" class="btn btn-danger btn-sm" onclick="deleteWeeklyWork(' . $rec['w_id'] . ')"><i class="fa fa-trash"></i></a>';


                                        $result[] = $nestedData;
                                }

                                $json_data = array(
                                        "draw" => intval($this->post('draw')),
                                        "recordsTotal" => count($records),
                                        "recordsFiltered" => count($records),
                                        "data" => $result
                                );
                                $this->response($json_data);
                                break;
                        case "add":
                                $w_id = $this->post('w_id');
                                $employeeIds   = $this->input->post('employee_ids');
                                $employeeIds = is_array($employeeIds) ? $employeeIds : [];
                                $no_of_persons = count($employeeIds);
                                $data = array(
                                        'leader_id'       => $this->admin_session['u_id'],
                                        'p_id'            => $this->post('p_id'),
                                        'week_from'       => $this->post('week_from'),
                                        'week_to'         => $this->post('week_to'),
                                        'task_name'       => $this->post('task_name'),
                                        'submission_date' => $this->post('submission_date'),
                                        'no_of_persons'   => $no_of_persons, //$this->post('no_of_persons'),
                                        'status'          => $this->post('status'),
                                );

                                try {
                                        // Save weekly work
                                        if ($w_id > 0)
                                                $data['w_id'] = $w_id;

                                        $id = $this->weeklywork_model->save_weekly_work($data);
                                        
                                        foreach ($employeeIds as $uid) {
                                                $this->db->insert('aa_weekly_work_users', [
                                                'weekly_work_id' => $id,
                                                'u_id'        => $uid
                                        ]);
                                        }

                                        // --- Handle multiple dependencies ---
                                        $dep_types         = $this->post('dep_type');
                                        $dep_leaders       = $this->post('dep_leader'); // can be multiple per dependency
                                        $dep_texts          = $this->post('dependency_text');
                                        $dep_priorities    = $this->post('dep_priority');
                                        $dep_created_dates = date('Y-m-d H:i:s');
                                        $dep_target_dates = $this->post('dep_target_date');
                                        $dep_statuses      = $this->post('dep_status');

                                        if (is_array($dep_types) && !empty($dep_types)) {
                                                foreach ($dep_types as $i => $type) {

                                                        $isEmpty = (
                                                                empty($type) &&
                                                                empty($dep_priorities[$i]) &&
                                                                empty($dep_texts[$i]) &&
                                                                empty($dep_leaders[$i])
                                                        );

                                                        if ($isEmpty) {
                                                                continue;
                                                        }

                                                        $dep_data = [
                                                                'w_id'            => $id,
                                                                'dependency_type' => $type,
                                                                'priority'        => isset($dep_priorities[$i]) ? $dep_priorities[$i] : null,
                                                                'created_date'    => date('Y-m-d'),
                                                                'target_date'  => isset($dep_target_dates[$i]) ? $dep_target_dates[$i] : null,
                                                                'status'          => isset($dep_statuses[$i]) ? $dep_statuses[$i] : null,
                                                                'created_by' => $this->admin_session['u_id'],
                                                        ];


                                                        if ($type === 'Internal') {
                                                                $leaders = isset($dep_leaders[$i]) ? (array)$dep_leaders[$i] : [];
                                                                if (empty($leaders)) {
                                                                        continue;
                                                                }
                                                                $dep_data['dep_leader_ids'] = !empty($leaders) ? implode(',', $leaders) : null;
                                                                $dep_data['dependency_text'] = $dep_texts[$i] ?? '';
                                                        } else {
                                                                $dep_data['dep_leader_ids'] = null;
                                                                if (empty($dep_texts[$i])) {
                                                                        continue;
                                                                }
                                                                $dep_data['dependency_text'] = isset($dep_texts[$i]) ? $dep_texts[$i] : null;
                                                        }



                                                        if (!empty($dep_data['dependency_type']) && (!empty($dep_data['priority']) || !empty($dep_data['dep_leader_ids']) || !empty($dep_data['dependency_text']))) {

                                                                $this->db->insert('aa_weekly_work_dependency', $dep_data);

                                                                // Optional: Notification for Internal dependencies
                                                                if ($type == "Internal" && !empty($leaders)) {
                                                                        $this->db->select('u_id, u_name');
                                                                        $this->db->where_in('u_id', $leaders);
                                                                        $dep_users = $this->db->get('aa_users')->result_array();

                                                                        $dep_user_names = array_column($dep_users, 'u_name');
                                                                        $dep_user_list = implode(', ', $dep_user_names);

                                                                        $bim_heads = $this->db->select('u_id, u_name')
                                                                                ->where('u_type', 'Bim Head')
                                                                                ->get('aa_users')
                                                                                ->result_array();

                                                                        $bim_head_ids = array_column($bim_heads, 'u_id');
                                                                        $notify_user_ids = array_unique(array_merge($leaders, $bim_head_ids));

                                                                        $title = "New Dependency Created by " . $this->admin_session['u_name'];
                                                                        $messageLoad = "A new dependency has been created by " . $this->admin_session['u_name'] .
                                                                                " for the following users: " . $dep_user_list;

                                                                        $payload = [
                                                                                'screen_name' => 'Dependency',
                                                                                'action' => $title,
                                                                                'id' => $id,
                                                                        ];

                                                                        foreach ($notify_user_ids as $uid) {
                                                                                $this->db->insert('aa_desktop_notification_queue', [
                                                                                        'u_id'    => $uid,
                                                                                        'title'   => $title,
                                                                                        'message' => $messageLoad,
                                                                                        'payload' => json_encode($payload),
                                                                                        'is_sent' => 0,
                                                                                ]);
                                                                        }
                                                                }
                                                        }
                                                }
                                        }

                                        $this->response([
                                                'status' => 'pass',
                                                'message' => 'Weekly work and dependencies saved successfully.'
                                        ]);
                                } catch (Exception $ex) {
                                        $this->response([
                                                'status' => 'fail',
                                                'message' => $ex->getMessage()
                                        ]);
                                }
                                break;
                        case "edit":
                                $w_id = $this->post('w_id');
                                $record = $this->weeklywork_model->get_weekly_work_by_id($w_id);

                                if ($record) {
                                        $dependencies = $this->db
                                                ->where('w_id', $w_id)
                                                ->get('aa_weekly_work_dependency')
                                                ->result_array();

                                        $assigned_users = $this->db
                                                ->select('u_id')
                                                ->where('weekly_work_id', $w_id)
                                                ->get('aa_weekly_work_users')
                                                ->result_array();
                                        $employee_ids = array_column($assigned_users, 'u_id');

                                        $this->response([
                                                'status' => 'pass',
                                                'data' => $record,
                                                'dependencies' => $dependencies,
                                                'assigned_employees' => $employee_ids
                                        ]);
                                } else {
                                        $this->response([
                                                'status' => 'fail',
                                                'message' => 'Record not found.'
                                        ]);
                                }
                                break;

                        case "update":
                                $w_id = $this->post('w_id');
                                $employeeIds = $this->post('employee_ids');
                                $employeeIds = is_array($employeeIds) ? $employeeIds : [];
                                $no_of_persons = count($employeeIds);
                                $data = array(
                                        'leader_id'       => $this->admin_session['u_id'],
                                        'p_id'            => $this->post('p_id'),
                                        'week_from'       => $this->post('week_from'),
                                        'week_to'         => $this->post('week_to'),
                                        'task_name'       => $this->post('task_name'),
                                        'submission_date' => $this->post('submission_date'),
                                        'no_of_persons'   => $no_of_persons,//$this->post('no_of_persons'),
                                        'status'          => $this->post('status'),
                                );

                                $this->db->where('w_id', $w_id);
                                $updated = $this->db->update('aa_weekly_work', $data);

                                if ($updated) {

                                         $this->db->where('weekly_work_id', $w_id)
                                        ->delete('aa_weekly_work_users');

                                        // Insert updated employees
                                        foreach ($employeeIds as $uid) {
                                        $this->db->insert('aa_weekly_work_users', [
                                                'weekly_work_id' => $w_id,
                                                'u_id'           => $uid
                                        ]);
                                        }

                                        $dep_types          = $this->post('dep_type');
                                        $dep_leaders        = $this->post('dep_leader');
                                        $dep_texts          = $this->post('dependency_text');
                                        $dep_priorities     = $this->post('dep_priority');
                                        $dep_target_date = $this->post('dep_target_date');
                                        $dep_statuses       = $this->post('dep_status');

                                        $dep_ids = $this->post('dep_id');
                                        $existingDeps = $this->db
                                                ->select('wd_id')
                                                ->where('w_id', $w_id)
                                                ->get('aa_weekly_work_dependency')
                                                ->result_array();
                                        $existing_ids = array_column($existingDeps, 'wd_id');
                                        $posted_ids = [];
                                        if (is_array($dep_ids)) {
                                                foreach ($dep_ids as $id) {
                                                        if (!empty($id)) {
                                                                $posted_ids[] = $id;
                                                        }
                                                }
                                        }
                                        $deleted_ids = array_diff($existing_ids, $posted_ids);
                                        if (!empty($deleted_ids)) {
                                                $this->db
                                                        ->where_in('wd_id', $deleted_ids)
                                                        ->delete('aa_weekly_work_dependency');
                                        }
                                         //$this->db->where('w_id', $w_id)->delete('aa_weekly_work_dependency');
                                        $i = 1;
                                         if (is_array($dep_types) && !empty($dep_types)) {                                                
                                                foreach ($dep_types as $i => $type) {
                                                $dep_id = $dep_ids[$i] ?? null;
                                                $isEmpty = (
                                                                empty($type) &&
                                                                empty($dep_priorities[$i]) &&
                                                                empty($dep_texts[$i]) &&
                                                                empty($dep_leaders[$i])
                                                        );

                                                        if ($isEmpty) {
                                                                continue;
                                                        }
                                                        $dep_data = [
                                                                //'w_id'            => $w_id,
                                                                'dependency_type' => $type,
                                                                'priority'        => $dep_priorities[$i] ?? null,
                                                                //'created_date'    => date('Y-m-d'),
                                                                'target_date'  => $dep_target_date[$i] ?? null,
                                                                'status'          => $dep_statuses[$i] ?? null,
                                                                //'created_by' => $this->admin_session['u_id'],
                                                        ];

                                                        if ($type === 'Internal') {
                                                                $leaders = isset($dep_leaders[$i]) ? (array)$dep_leaders[$i] : [];

                                                                if (empty($leaders)) {
                                                                        continue;
                                                                }
                                                                $dep_data['dep_leader_ids'] = implode(',', $leaders);
                                                                $dep_data['dependency_text'] = $dep_texts[$i] ?? '';
                                                        } else {
                                                                $dep_data['dep_leader_ids'] = null;
                                                                if (empty($dep_texts[$i])) {
                                                                        continue;
                                                                }
                                                                $dep_data['dependency_text'] = $dep_texts[$i] ?? '';
                                                        }

                                                        if (!empty($dep_id)) {
                                                                $this->db->where('wd_id', $dep_id)
                                                                        ->update('aa_weekly_work_dependency', $dep_data);
                                                        } else {
                                                                $dep_data['w_id'] = $w_id;
                                                                $dep_data['created_date'] = date('Y-m-d');
                                                                $dep_data['created_by'] = $this->admin_session['u_id'];
                                                                $this->db->insert('aa_weekly_work_dependency', $dep_data);
                                                        }

                                                        // if (!empty($dep_data)) {
                                                        //         $this->db->insert('aa_weekly_work_dependency', $dep_data);
                                                        // }
                                                }
                                        }
                                        if (!empty($dep_data['dependency_type']) && (!empty($dep_data['priority']) || !empty($dep_data['dep_leader_ids']) || !empty($dep_data['dependency_text']))) {
                                                if ($data['dependency_type'] == "Internal" && !empty($dep_leader) && is_array($dep_leader)) {
                                                        $this->db->select('u_name');
                                                        $this->db->where_in('u_id', $dep_leader);
                                                        $dep_users = $this->db->get('aa_users')->result_array();
                                                        $dep_user_names = array_column($dep_users, 'u_name');
                                                        $dep_user_list = implode(', ', $dep_user_names);

                                                        $bim_heads = $this->db->select('u_id, u_name')
                                                                ->where('u_type', 'Bim Head')
                                                                ->get('aa_users')
                                                                ->result_array();

                                                        $bim_head_ids = array_column($bim_heads, 'u_id');
                                                        $notify_user_ids = array_unique(array_merge($dep_leader, $bim_head_ids));

                                                        $project = $this->db->select('p_name')->where('p_id', $data['p_id'])->get('aa_projects')->row_array();
                                                        $project_name = $project ? $project['p_name'] : 'Unknown Project';

                                                        $title = "Dependency Updated by " . $this->admin_session['u_name'];
                                                        $messageLoad = "The dependency in project " . $project_name . " has been updated by " .
                                                                $this->admin_session['u_name'] . " for: " . $dep_user_list . ".";

                                                        $payload = [
                                                                'screen_name' => 'Dependency',
                                                                'action' => $title,
                                                                'id' => $w_id,
                                                        ];

                                                        foreach ($notify_user_ids as $uid) {
                                                                $this->db->insert('aa_desktop_notification_queue', [
                                                                        'u_id'    => $uid,
                                                                        'title'   => $title,
                                                                        'message' => $messageLoad,
                                                                        'payload' => json_encode($payload),
                                                                        'is_sent' => 0,
                                                                ]);
                                                        }
                                                }
                                        }

                                        $this->response(['status' => 'pass', 'message' => 'Weekly Work & dependencies updated successfully.']);
                                } else {
                                        $this->response(['status' => 'fail', 'message' => 'Update failed.']);
                                }
                                break;
                        case "delete":
                                $w_id = $this->post('w_id');
                                $this->db->delete('aa_weekly_work_dependency', ['w_id' => $w_id]);
                                $deleted = $this->db->delete('aa_weekly_work', ['w_id' => $w_id]);
                                if ($deleted) {
                                        $this->response(['status' => 'pass', 'message' => 'Weekly Work and dependencies deleted successfully.']);
                                } else {
                                        $this->response(['status' => 'fail', 'message' => 'Unable to delete record.']);
                                }
                                break;
                        case "get_dependencies":
                                $w_id = $this->post('w_id');
                                $dependencies = $this->db->where('w_id', $w_id)->get('aa_weekly_work_dependency')->result_array();
                                $this->response(['status' => 'pass', 'data' => $dependencies]);
                                break;
                        case "dependencies":
                                $w_id = $this->post('w_id');
                                $type = $this->post('type');

                                if (!$w_id) {
                                        echo json_encode(['status' => 'fail', 'message' => 'Missing Weekly Work ID']);
                                        return;
                                }

                                $this->db->select('
                                        d.wd_id,
                                        d.dependency_text,
                                        d.dependency_type,
                                        d.dep_leader_ids,
                                        d.priority,
                                        d.status,
                                        d.completed_date,d.target_date,
                                        d.created_date,
                                        u.u_name as created_by,
                                        ww.week_from,
                                        ww.week_to,
                                        p.p_name
                                ');
                                $this->db->from('aa_weekly_work_dependency d');
                                $this->db->join('aa_users u', 'u.u_id = d.created_by', 'left');
                                $this->db->join('aa_weekly_work ww', 'ww.w_id = d.w_id', 'left');
                                $this->db->join('aa_projects p', 'p.p_id = ww.p_id', 'left');
                                $this->db->where('d.w_id', $w_id);

                                if ($type === 'incomplete') {
                                        $this->db->where('d.status !=', 'Completed');
                                }

                                $this->db->order_by('d.wd_id', 'ASC');
                                $deps = $this->db->get()->result_array();

                                foreach ($deps as &$d) {
                                        if ($d['dependency_type'] === 'Internal' && !empty($d['dep_leader_ids'])) {
                                                $leader_ids = explode(',', $d['dep_leader_ids']);
                                                $this->db->select('GROUP_CONCAT(u_name SEPARATOR ", ") AS leader_names');
                                                $this->db->where_in('u_id', $leader_ids);
                                                $leader_query = $this->db->get('aa_users')->row_array();
                                                $d['assigned_to'] = $leader_query['leader_names'];
                                        } else {
                                                $d['assigned_to'] = '-';
                                        }
                                }

                                echo json_encode(['status' => 'pass', 'data' => $deps]);
                                break;
                        case "dependencies_list":
                                $user_id = $this->admin_session['u_id'];
                                $user_type = $this->admin_session['u_type'];

                                $this->db->select('
                                                d.wd_id,
                                                d.w_id,
                                                d.dependency_text,
                                                d.dependency_type,
                                                d.completed_day_diff,
                                                d.completed_assign_status,   
                                                d.priority,
                                                d.status,
                                                d.dep_leader_ids,
                                                d.created_date,
                                                d.completed_date,d.target_date,d.created_by AS created_by_id,
                                                d.completed_by_assigned,d.completed_assign_date,
                                                w.week_from,
                                                w.week_to,
                                                p.p_name AS project_name,
                                                u1.u_name AS created_by,
                                                GROUP_CONCAT(DISTINCT u2.u_name SEPARATOR ", ") AS assigned_to,
                                                CASE
                                                WHEN FIND_IN_SET(' . $user_id . ', d.dep_leader_ids)
                                                        AND d.created_by != ' . $user_id . ' THEN 1
                                                ELSE 0
                                                END AS is_dependent_on_me
                                        ');
                                $this->db->distinct();
                                $this->db->from('aa_weekly_work_dependency d');
                                $this->db->join('aa_weekly_work w', 'w.w_id = d.w_id', 'left');
                                $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
                                $this->db->join('aa_users u1', 'u1.u_id = d.created_by', 'left');
                                $this->db->join('aa_users u2', 'FIND_IN_SET(u2.u_id, d.dep_leader_ids)', 'left');
                                $createdby = $this->post('createdby') ?? 'all';

                                if ($createdby === 'all' && empty($this->post('project_id'))) {
                                echo json_encode([
                                        "draw" => 1,
                                        "recordsTotal" => 0,
                                        "recordsFiltered" => 0,
                                        "data" => [],
                                        "message" => "Please select a project"
                                ]);
                                exit;
                                }

                                if ($user_type == 'Project Leader' && $createdby !== 'all') {
                                        if ($createdby === 'own') {
                                                $this->db->where('d.created_by', $user_id);
                                        } elseif ($createdby === 'assigned') {
                                                $this->db->where("FIND_IN_SET($user_id, d.dep_leader_ids) >", 0);
                                        } elseif ($createdby === 'myall') {
                                                $this->db->group_start();
                                                $this->db->where('d.created_by', $user_id);
                                                $this->db->or_where("FIND_IN_SET($user_id, d.dep_leader_ids) >", 0);
                                                $this->db->group_end();
                                        }
                                }

                                if ($this->post('project_id')) {
                                        $this->db->where('p.p_id', $this->post('project_id'));
                                }
                                if (!$this->post('status')) {
                                $this->db->where_in('d.status', ['Pending', 'In Progress']);
                                }
                                

                                if ($this->post('status')) {
                                        if ($this->post('status') == 'All') {
                                                $this->db->where_in('d.status', ['Pending', 'In Progress', 'Completed']);
                                        } else {
                                                $this->db->where('d.status', $this->post('status'));
                                        }                                       
                                }
                                if ($this->post('type')) {
                                        $this->db->where('d.dependency_type', $this->post('type'));
                                }
                                if ($this->post('priority')) {
                                        $this->db->where('d.priority', $this->post('priority'));
                                }
                                if ($this->post('from_date') && $this->post('to_date')) {
                                        $this->db->where('DATE(d.created_date) >=', $this->post('from_date'));
                                        $this->db->where('DATE(d.created_date) <=', $this->post('to_date'));
                                }
                                if ($this->post('leader')) {
                                        $leader_id = $this->post('leader');
                                        $this->db->group_start();
                                        $this->db->where('d.created_by', $leader_id);
                                        $this->db->or_where("FIND_IN_SET($leader_id, d.dep_leader_ids) >", 0);
                                        $this->db->group_end();
                                }

                                $this->db->group_by('d.wd_id');
                                $this->db->order_by("
                                CASE d.status
                                        WHEN 'Pending' THEN 1
                                        WHEN 'In Progress' THEN 2
                                        ELSE 3
                                END
                                ", 'ASC');

                                $this->db->order_by("
                                CASE d.priority
                                        WHEN 'High' THEN 1
                                        WHEN 'Medium' THEN 2
                                        WHEN 'Low' THEN 3
                                        ELSE 4
                                END
                                ", 'ASC');
                                
                                //$this->db->order_by('d.wd_id', 'DESC');
                                $deps = $this->db->get()->result_array();
                                $final = [];
                                $i = 1;
                                foreach ($deps as $d) {
                                        $final[] = [
                                                "#" => $i++,
                                                "wd_id" => $d['wd_id'],
                                                "dependency_text" => $d['dependency_text'],
                                                "project_name" => $d['project_name'],
                                                "created_by" => $d['created_by'],
                                                "created_by_id" => $d['created_by_id'],
                                                "assigned_to" => $d['assigned_to'],
                                                "dependency_type" => $d['dependency_type'],
                                                "priority" => $d['priority'],
                                                "status" => $d['status'],
                                                "created_date" => $d['created_date'],
                                                "completed_date" => $d['completed_date'],
                                                "target_date" => $d['target_date'],
                                                "dep_leader_ids" => $d['dep_leader_ids'],
                                                "completed_by_assigned" => $d['completed_by_assigned'],
                                                "completed_assign_date" => $d['completed_assign_date'],
                                                "completed_day_diff" => $d['completed_day_diff'],
                                                "completed_assign_status" => $d['completed_assign_status'],
                                        ];
                                }

                                echo json_encode([
                                        "draw" => 1,
                                        "recordsTotal" => count($final),
                                        "recordsFiltered" => count($final),
                                        "data" => $final
                                ]);
                                break;
                        case "complete_dependency":
                                $user_id = $this->admin_session['u_id'];
                                $wd_id = $this->post('wd_id');
                                $dep = $this->db
                                        ->where('wd_id', $wd_id)
                                        ->get('aa_weekly_work_dependency')
                                        ->row_array();
                                if (!$dep) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Dependency not found'
                                        ]);
                                        return;
                                }
                                if ($dep['created_by'] != $user_id) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Unauthorized action'
                                        ]);
                                        return;
                                }
                                if ($dep['status'] == 'Completed') {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Already completed'
                                        ]);
                                        return;
                                }
                                $this->db->where('wd_id', $wd_id);
                                $this->db->update('aa_weekly_work_dependency', [
                                        'status' => 'Completed',
                                        'completed_date' => date('Y-m-d H:i:s')
                                ]);
                                echo json_encode([
                                        'status' => 'success',
                                        'message' => 'Dependency completed successfully'
                                ]);
                                break;

                        case "get_dependency":
                                $user_id = $this->admin_session['u_id'];
                                $wd_id = $this->post('wd_id');

                                // Get dependency details
                                $dep = $this->db
                                        ->where('wd_id', $wd_id)
                                        ->get('aa_weekly_work_dependency')
                                        ->row_array();

                                if (!$dep) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Dependency not found'
                                        ]);
                                        return;
                                }

                                // Check if user is the creator
                                if ($dep['created_by'] != $user_id) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Only the creator can edit this dependency'
                                        ]);
                                        return;
                                }

                                echo json_encode([
                                        'status' => 'success',
                                        'data' => $dep
                                ]);
                                break;

                        case "update_dependency":
                                $user_id = $this->admin_session['u_id'];
                                $wd_id = $this->post('wd_id');
                                $dependency_text = trim($this->post('dependency_text'));
                                $dependency_type = $this->post('dependency_type');
                                $priority = $this->post('priority');
                                $status = $this->post('status');
                                $dep_leader_ids = $this->post('dep_leader_ids');
                                $dep_target_date = $this->post('dep_target_date');
                                $dep_completed_date = $this->post('dep_completed_date');
                                // Validate required fields
                                if (empty($dependency_text) || empty($dependency_type) || empty($priority) || empty($status)) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'All fields are required'
                                        ]);
                                        return;
                                }

                                // Convert array to comma-separated string
                                if (is_array($dep_leader_ids)) {
                                        $dep_leader_ids = implode(',', $dep_leader_ids);
                                }

                                // Get existing dependency
                                $dep = $this->db
                                        ->where('wd_id', $wd_id)
                                        ->get('aa_weekly_work_dependency')
                                        ->row_array();

                                if (!$dep) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Dependency not found'
                                        ]);
                                        return;
                                }

                                // Check if user is the creator
                                if ($dep['created_by'] != $user_id) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Only the creator can update this dependency'
                                        ]);
                                        return;
                                }

                                // Prepare update data
                                $update_data = [
                                        'dependency_text' => $dependency_text,
                                        'dependency_type' => $dependency_type,
                                        'priority' => $priority,
                                        'status' => $status,
                                        'dep_leader_ids' => $dep_leader_ids,
                                        'target_date' => $dep_target_date
                                ];

                                // If status changed to Completed, set completed_date
                                if ($status === 'Completed' && $dep['status'] !== 'Completed') {
                                        if($dep_completed_date){
                                                if($dep_completed_date >= date('Y-m-d')){
                                            //$update_data['completed_date'] = date('Y-m-d', strtotime($dep_completed_date));
                                                $update_data['completed_date'] = date('Y-m-d');
                                        }else{
                                                        echo json_encode([
                                                                'status' => 'error',
                                                                'message' => 'Completed Date Must be greater than or equal to Current Date'
                                                        ]);
                                                        return;
                                        }
                                        } else {
                                            $update_data['completed_date'] = date('Y-m-d');
                                        }
                                        //$update_data['completed_date'] = date('Y-m-d H:i:s');
                                }
                                if ($dep_target_date) {
                                        if ($dep_target_date >= date('Y-m-d')) {
                                                $update_data['target_date'] = $dep_target_date;
                                        } else {
                                                echo json_encode([
                                                        'status' => 'error',
                                                        'message' => 'Target Date Must be greater than or equal to Current Date'
                                                ]);
                                                return;
                                        }
                                }


                                if (
                                isset($dep_target_date) &&
                                $dep_target_date != $dep['target_date'] &&
                                !empty($dep['completed_by_assigned']) &&
                                !empty($dep['completed_assign_date'])
                                ) {

                                $completed_date = $dep['completed_assign_date'];
                                $target_date    = $dep_target_date;

                                $day_diff = floor(
                                        (strtotime($completed_date) - strtotime($target_date)) / 86400
                                );

                                $update_data['completed_day_diff'] = $day_diff;

                                // Assign status
                                if ($day_diff <= 0) {
                                        $update_data['completed_assign_status'] = 'ontime';
                                } else {
                                        $update_data['completed_assign_status'] = 'delay';
                                }
                                }

                                // Update dependency
                                $this->db->where('wd_id', $wd_id);
                                $this->db->update('aa_weekly_work_dependency', $update_data);

                                echo json_encode([
                                        'status' => 'success',
                                        'message' => 'Dependency updated successfully'
                                ]);
                                break;

                        case "assigned_complete_dependency":
                                $user_id = $this->admin_session['u_id'];
                                $user_name = $this->admin_session['u_name'];
                                $wd_id = $this->post('wd_id');

                                // Get dependency details with project and creator info
                                $this->db->select('d.*, w.p_id, p.p_name, creator.u_name as creator_name, creator.u_email as creator_email');
                                $this->db->from('aa_weekly_work_dependency d');
                                $this->db->join('aa_weekly_work w', 'w.w_id = d.w_id', 'left');
                                $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
                                $this->db->join('aa_users creator', 'creator.u_id = d.created_by', 'left');
                                $this->db->where('d.wd_id', $wd_id);
                                $dep = $this->db->get()->row_array();

                                if (!$dep) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Dependency not found'
                                        ]);
                                        return;
                                }

                                // Check if user is assigned to this dependency
                                $assigned_ids = explode(',', $dep['dep_leader_ids']);
                                if (!in_array($user_id, $assigned_ids)) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'You are not assigned to this dependency'
                                        ]);
                                        return;
                                }

                                // Check if creator is trying to use assigned completion
                                if ($dep['created_by'] == $user_id) {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Please use the Complete button to mark your own dependency as complete'
                                        ]);
                                        return;
                                }

                                if ($dep['status'] == 'Completed') {
                                        echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Already completed'
                                        ]);
                                        return;
                                }

                                // Update dependency status
                                $completed_date = date('Y-m-d H:i:s');

                                $update_data = []; 

                                // Check column exists
                                $fields = $this->db->list_fields('aa_weekly_work_dependency');

                                $this->db->select('target_date');
                                $this->db->where('wd_id', $wd_id);
                                $row = $this->db->get('aa_weekly_work_dependency')->row();
                                if ($row && !empty($row->target_date)) {
                                        $target_date = $row->target_date;
                                        $day_diff = floor(
                                                (strtotime($completed_date) - strtotime($target_date)) / 86400
                                        );
                                        $update_data['completed_day_diff'] = $day_diff;
                                        if (in_array('completed_assign_status', $fields)) {
                                                $update_data['completed_assign_status'] = ($day_diff > 0) ? 'delay' : 'ontime';
                                        }
                                }

                                if (in_array('completed_by_assigned', $fields)) {
                                        $update_data['completed_by_assigned'] = $user_id;
                                        $update_data['completed_assign_date'] = date('Y-m-d H:i:s');
                                        
                                }

                                // Safety check
                                if (!empty($update_data)) {
                                        $this->db->where('wd_id', $wd_id);
                                        $this->db->update('aa_weekly_work_dependency', $update_data);
                                        // Debug AFTER update
                                        //echo $this->db->last_query();
                                       // exit;
                                }

                                // Send email notification to creator
                                // $this->load->library('email');
                                // $this->email->from('noreply@yourcompany.com', 'CRM Dependency System');
                                // $this->email->to($dep['creator_email']);
                                // $this->email->subject('Dependency Completed - ' . $dep['p_name']);

                                // $completed_date_formatted = date('d-m-Y H:i:s', strtotime($completed_date));
                                // $message = "
                                //         <h3>Dependency Marked as Complete</h3>
                                //         <p>A dependency you created has been marked as complete by an assigned team member.</p>
                                //         <hr>
                                //         <p><strong>Project:</strong> {$dep['p_name']}</p>
                                //         <p><strong>Dependency:</strong> {$dep['dependency_text']}</p>
                                //         <p><strong>Type:</strong> {$dep['dependency_type']}</p>
                                //         <p><strong>Priority:</strong> {$dep['priority']}</p>
                                //         <p><strong>Completed By:</strong> {$user_name}</p>
                                //         <p><strong>Completed Date:</strong> {$completed_date_formatted}</p>
                                //         <hr>
                                //         <p style='color: #666; font-size: 12px;'>This is an automated notification from the CRM system.</p>
                                // ";

                                // $this->email->message($message);
                                // $this->email->set_mailtype('html');

                                // Try to send email, but don't fail if email fails
                                try {
                                        //$this->email->send();
                                } catch (Exception $e) {
                                        // Log error but continue
                                        log_message('error', 'Failed to send dependency completion email: ' . $e->getMessage());
                                }

                                echo json_encode([
                                        'status' => 'success',
                                        'message' => 'Dependency marked as complete. Email notification sent to ' . $dep['creator_name'] . '.'
                                ]);
                                break;
                        case 'reverse_dependency':
                                $wd_id = $this->input->post('wd_id');

                                if (empty($wd_id)) {
                                        echo json_encode([
                                                'status' => false,
                                                'message' => 'Invalid dependency ID'
                                        ]);
                                        exit;
                                }

                                $update_data = [
                                        'completed_assign_status' => NULL,
                                        'completed_day_diff'      => NULL,
                                        'completed_assign_date'   => NULL,
                                        'completed_by_assigned'   => NULL,
                                        //'status'                  => 'In Progress'
                                ];

                                $this->db->where('wd_id', $wd_id);
                                $this->db->update('aa_weekly_work_dependency', $update_data);

                                if ($this->db->affected_rows() > 0) {
                                        echo json_encode([
                                                'status' => 'success',
                                                'message' => 'Dependency reversed successfully'
                                        ]);
                                } else {
                                        echo json_encode([
                                                'status'  => false,
                                                'message' => 'No changes made or dependency not found'
                                        ]);
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
                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Employee']) && in_array($act, ["add", "del", "assigns", "file_del"])) {
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
                                if ($this->post('callfrom') == 'report') {
                                        $t_name = $this->post('t_name');
                                        $p_name = $this->post('p_name');
                                }


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
                                        if ($this->post('callfrom') == 'report') {
                                                $nestedData[] = $p_name ?? "Leave";
                                                $nestedData[] = $t_name ?? "Leave";
                                        }
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
                                else if ($fraction == '.25')
                                        $total_salary = str_replace($fraction, '.25', '.15');
                                else if ($fraction == '.50')
                                        $total_salary = str_replace($fraction, '.50', '.30');
                                else
                                        $total_salary = $fraction;
                                if ($whours > 0)
                                        $total_hrs = "<b>Total Hours Worked :  " . (number_format($whole + $total_salary, 2)) . " hr</b>";
                                else
                                        $total_hrs = '';
                                if ($this->post('callfrom') == 'report')
                                        $new = array("", "", "", "", $total_hrs, "");
                                else
                                        $new = array("", "", $total_hrs, "");

                                if ($result != null)
                                        array_push($result, $new);

                                $json_data = array(
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
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
                                        if (in_array($this->admin_session['u_type'], ['Bim Head', 'Master Admin'])) {
                                        } else {
                                                $criteria['user_id'] = $this->admin_session['u_id'];
                                        }
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
                                        if (in_array($this->admin_session['u_type'], ['Bim Head', 'Master Admin'])) {
                                        } else {
                                                $criteria['user_id'] = $this->admin_session['u_id'];
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
                                                                if ($this->authorization->is_bim_head_or_higher($this->admin_session) ||  $this->admin_session['u_id'] == $single_record['t_u_id']) {
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
                                                        if ($this->authorization->is_bim_head_or_higher($this->admin_session) ||  $this->admin_session['u_id'] == $single_record['t_u_id']) {
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
                $at_u_id = $this->post('at_u_id') ?? $this->admin_session['u_id'];
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
                                        $utype = $this->admin_session['u_type'];
                                        $at_id = $this->timesheet_model->save($data, $utype);
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
                                                else if ($fraction == '.25')
                                                        $total_salary = str_replace($fraction, '.25', '.15');
                                                else if ($fraction == '.50')
                                                        $total_salary = str_replace($fraction, '.50', '.30');
                                                else
                                                        $total_salary = $fraction;
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
                //$subscription = $this->post('subscription'); /// for desktop notification

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
                                        if ($this->authorization->is_role_allowed($records['u_type'], ['Bim Head', 'Master Admin', 'Project Leader', 'Super Admin', 'TaskCoordinator'])) {
                                                $url = base_url("home/index");
                                        } else if ($this->authorization->is_role_allowed($records['u_type'], ['Associate User'])) {
                                                $url = base_url("usertask");
                                        } else {
                                                if ($records['u_username'] == 'aeplit')
                                                        $url = base_url('ticket/assigned');
                                                else if ($records['u_type'] == 'MailCoordinator')
                                                        $url = base_url('home/messages');
                                                else
                                                        $url = base_url("home/tasks");
                                        }

                                        $token = '';
                                        $isProjectLeader = $this->authorization->is_role_allowed($records['u_type'], ['Project Leader']);
                                        $canProceed = $isProjectLeader || (!empty($records['u_app_auth']) && $records['u_app_auth'] == 1);

                                        if ($canProceed) {
                                                date_default_timezone_set('Asia/Kolkata');
                                                $currentDateTime = date('Y-m-d H:i:s');
                                                $record_token = $this->user_model->get_token($records['u_id']);
                                                $key = 'af0e4b7ca1c8e091fb9a781c9a2b5f07340ea4d88f96a3b5b1b9927710460f1a';
                                                $issuedAt = time();
                                                $expirationTime = $issuedAt + (7 * 24 * 60 * 60); // 7 days
                                                $payload = [
                                                        'iat' => $issuedAt,
                                                        'exp' => $expirationTime,
                                                        'u_id' => $records['u_id'],
                                                        'u_type' => $records['u_type'],
                                                ];

                                                if (empty($record_token)) {
                                                        // No token exists, generate and insert
                                                        $token = JWT::encode($payload, $key, 'HS256');
                                                        $this->db->insert('aa_user_tokens', [
                                                                'u_id' => $records['u_id'],
                                                                'token' => $token,
                                                                'created_at' => date('Y-m-d H:i:s'),
                                                                'expires_at' => date('Y-m-d H:i:s', $expirationTime)
                                                        ]);
                                                } else {

                                                        $existing_token = $record_token[0]['token'] ?? '';

                                                        $expires_at_string = $record_token[0]['expires_at'] ?? '1970-01-01 00:00:00';


                                                        try {
                                                                $expires_at = new DateTime($expires_at_string);
                                                        } catch (Exception $e) {
                                                                $expires_at = new DateTime('1970-01-01 00:00:00');
                                                        }

                                                        $now = new DateTime();
                                                        //echo "now==" . $now->format('Y-m-d H:i:s') . "<br/>";
                                                        //echo "expires_at==" . $expires_at->format('Y-m-d H:i:s') . "<br/>";
                                                        //exit;
                                                        if ($now > $expires_at) {
                                                                // Token exists but expired – update with new token
                                                                $token = JWT::encode($payload, $key, 'HS256');
                                                                $this->db->where('u_id', $records['u_id'])->update('aa_user_tokens', [
                                                                        'token' => $token,
                                                                        'created_at' => date('Y-m-d H:i:s'),
                                                                        'expires_at' => date('Y-m-d H:i:s', $expirationTime)
                                                                ]);
                                                        } else {
                                                                // Token still valid
                                                                $token = $existing_token;
                                                        }
                                                }
                                        }

                                        $allowedRoles = ['Master Admin', 'Super Admin', 'Bim Head', 'Project Leader'];
                                        if (in_array($records['u_type'], $allowedRoles)) {
                                                $statuses = ['WIP', 'HOLD', 'PAUSE'];
                                                $today = date('Y-m-d');
                                                $this->db->where_in('status', $statuses);
                                                $this->db->where('week_to <', $today);
                                                if ($this->authorization->is_role_allowed($records['u_type'], ['Project Leader'])) {
                                                        $this->db->where('leader_id', $records['u_id']);
                                                }
                                                $tasks = $this->db->get('aa_weekly_work')->result_array();
                                                $today = new DateTime();
                                                $currentMonday = clone $today;
                                                $currentMonday->modify('monday this week');
                                                $currentFriday = clone $currentMonday;
                                                $currentFriday->modify('friday this week');
                                                foreach ($tasks as $task) {

                                                        $start = new DateTime($task['week_from']);
                                                        $end   = new DateTime($task['week_to']);

                                                        // Include start day
                                                        $duration = $start->diff($end)->days + 1;

                                                        // Extend week_to
                                                        //$new_end = clone $end;
                                                        //$new_end->modify("+{$duration} days");
                                                        
                                                        
                                                        if ($start > $today) {
                                                                $carryStart = clone $today;
                                                        } else {
                                                                $carryStart = clone $currentMonday;
                                                        }
                                                        $newEnd = clone $carryStart;
                                                        $newEnd->modify('+' . ($duration - 1) . ' days');

                                                        if ($newEnd > $currentFriday) {
                                                                $newEnd = clone $currentFriday;
                                                        }

                                                        $this->db->where('w_id', $task['w_id'])
                                                                ->update('aa_weekly_work', [
                                                                        'week_to' => $newEnd->format('Y-m-d')
                                                                ]);
                                                }
                                        }                                     

                                        $this->db->where('u_id', $records['u_id']);
                                        $this->db->update('aa_users', ['is_web_logged_in' => 1]);
                                        $this->session->set_userdata(['admin_session' => $records, 'token' => $token]);
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
                                        // $records = $this->user_model->get_records($params);
                                        // if (isset($val['title']) && $val['title'])
                                        //         $ReturnVal[$val['type']] .= '<option value="">' . $val['title'] . '</option>';
                                        // else
                                        //         $ReturnVal[$val['type']] .= '<option value="">Select Project Leader</option>';
                                        // if ($records != null) {
                                        //         foreach ($records as $row) {
                                        //                 $ReturnVal[$val['type']] .= '<option value="' . $row['u_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['u_id']) ? ' selected="selected"' : '') . '>' . $row['u_name'] . '</option>';
                                        //         }
                                        // }
                                        $records = $this->user_model->get_records($params);
                                        $selected_ids = explode(',', $val['id'] ?? '');
                                        $options = '<option value="">Please select Leader</option>';
                                        if (!empty($records)) {
                                                foreach ($records as $row) {
                                                        $selected = in_array($row['u_id'], $selected_ids) ? ' selected="selected"' : '';
                                                        $options .= '<option value="' . $row['u_id'] . '"' . $selected . '>' . $row['u_name'] . '</option>';
                                                }
                                        }
                                        $ReturnVal[$val['type']] = $options;
                                        break;
                                case 'empprojects':
                                        $u_id = $val['u_id'];
                                        $records = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name,P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="-1">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="-1">Select Project</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['p_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['p_id']) ? ' selected="selected"' : '') . '>' . $row['p_number'] . ' - ' . $row['p_name'] . '</option>';
                                                }
                                        }
                                        if (isset($val['leave'])) {
                                                $ReturnVal[$val['type']] .= '<option value="0" ' . ((isset($val['id']) && $val['id'] == 0) ? ' selected="selected"' : '') . '>On Leave</option>';
                                        }
                                        break;
                                case 'projects':  //[PENDING] Check for Assigned Projects
                                        if (in_array($this->admin_session['u_type'], ['Master Admin', 'Super Admin' ,'Bim Head', 'MailCoordinator', 'TaskCoordinator'])) {
                                                $this->load->model('project_model');
                                                $params = array();
                                                $params['sort_by'] = "p_name";
                                                if (isset($val['active_only'])) {
                                                        $params['conditions'] = array();
                                                        $params['conditions'][] = array('p_status' => 'Active');
                                                }
                                                $records = $this->project_model->get_records($params);
                                        }  else {

                                                $records = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name,P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
                                        }

                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="-1">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="-1">Select Project</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['p_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['p_id']) ? ' selected="selected"' : '') . '>' . $row['p_number'] . ' - ' . $row['p_name'] . '</option>';
                                                }
                                        }
                                        if (isset($val['leave'])) {
                                                $ReturnVal[$val['type']] .= '<option value="0" ' . ((isset($val['id']) && $val['id'] == 0) ? ' selected="selected"' : '') . '>On Leave</option>';
                                        }

                                        break;
                                case 'timesheetprojects':  //Only show these projects which tasks are not completed and assigned
                                        if (in_array($this->admin_session['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'MailCoordinator', 'TaskCoordinator'])) {
                                                $this->load->model('project_model');
                                                $params = array();
                                                $params['sort_by'] = "p_name";
                                                if (isset($val['active_only'])) {
                                                        $params['conditions'] = array();
                                                        $params['conditions'][] = array('p_status' => 'Active');
                                                }
                                                $records = $this->project_model->get_records($params);
                                        } else {

                                                //$records = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
                                                $records = $this->db->query("
                                                                SELECT DISTINCT
                                                                        P.p_id,
                                                                        P.p_name,P.p_number,
                                                                        U.u_id
                                                                FROM aa_users U
                                                                LEFT OUTER JOIN aa_task2user TU 
                                                                        ON U.u_id = TU.tu_u_id
                                                                LEFT OUTER JOIN aa_tasks T
                                                                        ON T.t_id = TU.tu_t_id
                                                                LEFT OUTER JOIN aa_projects P 
                                                                        ON TU.tu_p_id = P.p_id
                                                                WHERE
                                                                        TU.tu_removed = 'No'
                                                                        AND U.u_id = '{$u_id}'
                                                                        AND P.p_status IN ('Active', 'New')
                                                                        AND T.t_status != 'Completed'
                                                                ORDER BY P.p_name
                                                                ")->result_array();
                                                                                                        }

                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="-1">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="-1">Select Project</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['p_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['p_id']) ? ' selected="selected"' : '') . '>' . $row['p_number'] . ' - ' . $row['p_name'] . '</option>';
                                                }
                                        }
                                        if (isset($val['leave'])) {
                                                $ReturnVal[$val['type']] .= '<option value="0" ' . ((isset($val['id']) && $val['id'] == 0) ? ' selected="selected"' : '') . '>On Leave</option>';
                                        }

                                        break;
                                case 'Leaderassignprojects':  //[PENDING] Check for Assigned Projects
                                        if (in_array($this->admin_session['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'MailCoordinator', 'TaskCoordinator'])) {
                                                $this->load->model('project_model');
                                                $params = array();
                                                $params['sort_by'] = "p_name";
                                                if (isset($val['active_only'])) {
                                                        $params['conditions'] = array();
                                                        $params['conditions'][] = array('p_status' => 'Active');
                                                }
                                                $records = $this->project_model->get_records($params);
                                        } else {
                                                if (in_array($this->admin_session['u_type'], ['Project Leader'])) {
                                                        $records = $this->db
                                                                ->select('p_id, p_name, p_number')
                                                                ->from('aa_projects')
                                                                ->where("FIND_IN_SET(" . (int)$this->admin_session['u_id'] . ", p_leader) >", 0)
                                                                //->where_in('p_status', ['Active', 'New'])
                                                                ->order_by('p_name', 'ASC')
                                                                ->get()
                                                                ->result_array();
                                                } else {
                                                        $records = $this->db->query("SELECT DISTINCT(p_id) ,P.p_name,P.p_number, u_id FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id WHERE TU.tu_removed = 'No' AND (P.p_status = 'Active' OR P.p_status = 'New')  and u_id = '{$u_id}' ")->result_array();
                                                }
                                        }

                                        if (isset($val['title']) && $val['title'])
                                                $ReturnVal[$val['type']] .= '<option value="-1">' . $val['title'] . '</option>';
                                        else
                                                $ReturnVal[$val['type']] .= '<option value="-1">Select Project</option>';
                                        if ($records != null) {
                                                foreach ($records as $row) {
                                                        $ReturnVal[$val['type']] .= '<option value="' . $row['p_id'] . '" ' . ((isset($val['id']) && $val['id'] == $row['p_id']) ? ' selected="selected"' : '') . '>' . $row['p_number'] . ' - ' .$row['p_name'] . '</option>';
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

                                case 'emptasks':
                                        $u_id = $val['u_id'];
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

                                case "assigned_projects":
                                        $this->load->model('weeklywork_model');
                                        $dropobjs = $this->post('dropobjs');
                                        $leader_id = $this->admin_session['u_id'];
                                        $response = [];

                                        foreach ($dropobjs as $obj) {
                                                if ($obj['type'] == 'assigned_projects') {
                                                        $response['assigned_projects'] = $this->weeklywork_model->get_assigned_projects_html($leader_id);
                                                } elseif ($obj['type'] == 'project_leaders') {
                                                        $project_id = $obj['project_id'];
                                                        $response['project_leaders'] = $this->weeklywork_model->get_project_leaders_html($project_id, $leader_id);
                                                }
                                        }

                                        $this->response(array(
                                                'status' => 'pass',
                                                'data' => $response
                                        ));
                                        break;
                                        
                                case "project_leaders":
                                        $this->load->model('weeklywork_model');
                                        $dropobjs = $this->post('dropobjs');
                                        $leader_id = $this->admin_session['u_id'];
                                        $response = [];

                                        foreach ($dropobjs as $obj) {
                                                $project_id = $obj['p_id'];
                                                $response['project_leaders'] = $this->weeklywork_model->get_project_leaders_html($project_id, $leader_id);
                                        }

                                        $this->response(array(
                                                'status' => 'pass',
                                                'data' => $response
                                        ));
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
                if ($this->authorization->is_project_leader_or_higher($this->admin_session) || $this->authorization->is_role_allowed($this->admin_session['u_type'], ['TaskCoordinator'])) {
                        $type = $this->post('type');
                        switch ($type) {
                                case 'present_list':
                                        // $query = $this->db->query("SELECT COUNT(u_id) as total, u_department FROM aa_users, aa_present WHERE u_id = pr_u_id AND pr_date = '" . date("Y-m-d") . "' GROUP BY u_department")->result_array();

                                        //if ($this->admin_session['u_type'] != 'Master Admin') return false;
                                        if (!$this->authorization->is_bim_head_or_higher($this->admin_session)) return false;
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
                                case 'present_list_limit':
                                        if ($this->admin_session['u_type'] != ('Master Admin' || 'Bim Head' || 'Super Admin')) return false;
                                        $this->load->model('user_model');

                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = '5';

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
                                        if ($this->admin_session['u_type'] != 'Master Admin') return false;
                                        //if ($this->admin_session['u_type'] != ('Master Admin' || 'Bim Head')) return false;
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
                                                //$nestedData[] = $single_record['l_is_hourly'];
                                                if (!empty($single_record['l_is_hourly']) && $single_record['l_is_hourly'] == 1) {
                                                        $nestedData[] = 'Hourly Leave';
                                                } elseif (!empty($single_record['l_is_halfday']) && $single_record['l_is_halfday'] == 1) {
                                                        $nestedData[] = 'Half Day';
                                                } else {
                                                        $nestedData[] = '-';
                                                }

                                                $nestedData[] = $single_record['total_days'];
                                                $anchors = "";
                                                if ($this->authorization->is_project_leader_or_higher($this->admin_session) && $single_record['l_status'] == 'Pending') {
                                                        $anchors .= '<a href="javascript://" class="btn btn-success btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Approve\')"><i class="fa fa-thumbs-up"></i><a>&nbsp; ';
                                                        $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="Approve(\'' . $single_record['l_id'] . '\',\'Decline\')"><i class="fa fa-thumbs-down"></i><a>';
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
                                                $records = $this->db->query("SELECT L.*, U.u_name, U.u_department ,
                                                          AU.u_name AS approved_by_name 
                                                          FROM aa_users U LEFT JOIN aa_leaves L ON U.u_id = L.l_u_id
                                                          LEFT JOIN aa_users AU ON AU.u_id = L.l_approved_by_id
                                                           WHERE L.l_from_date <= '$currentdate' AND L.l_to_date >= '$currentdate' 
                                                           AND L.l_status = 'Approved' and U.u_leader = '$leaderid'")->result_array();
                                        } else
                                                $records = $this->db->query("SELECT L.*, U.u_name, U.u_department,
                                                 AU.u_name AS approved_by_name
                                                 FROM aa_users U LEFT JOIN aa_leaves L ON U.u_id = L.l_u_id
                                                 LEFT JOIN aa_users AU ON AU.u_id = L.l_approved_by_id
                                                  WHERE L.l_from_date <= '$currentdate' AND L.l_to_date >= '$currentdate' 
                                                  AND L.l_status = 'Approved' ")->result_array();

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
                                                //$nestedData[] = $single_record['l_approved_by_id'];
                                                $nestedData[] = !empty($single_record['approved_by_name']) ? $single_record['approved_by_name'] . " - " . $single_record['l_approved_by'] : 'Not Approved';

                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => 0, //intval($totalData),
                                                "recordsFiltered" => 0, //intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                        break;
                                case 'basic':
                                        $data = array();
                                        $logged_user_id   = $this->admin_session['u_id'];
                                        $logged_user_type = $this->admin_session['u_type'];
                                        $query = [];
                                        if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Project Leader'])) {
                                                $data['box']['total_projects'] = $this->db
                                                        ->where("FIND_IN_SET($logged_user_id, p_leader)", null, false)
                                                        ->count_all_results('aa_projects');

                                                $data['box']['active_projects'] = $this->db
                                                        ->where('p_status', 'Active')
                                                        ->where("FIND_IN_SET($logged_user_id, p_leader)", null, false)
                                                        ->count_all_results('aa_projects');

                                                $data['box']['completed_projects'] = $this->db
                                                        ->where('p_status', 'Completed')
                                                        ->where("FIND_IN_SET($logged_user_id, p_leader)", null, false)
                                                        ->count_all_results('aa_projects');
                                                $data['box']['total_employee'] = $this->db
                                                        ->where('u_leader', $logged_user_id)
                                                        ->count_all_results('aa_users');
                                                $query = $this->db->query("
                                                        SELECT COUNT(u_id) as total, u_department 
                                                        FROM aa_users 
                                                        JOIN aa_present ON u_id = pr_u_id
                                                        WHERE pr_date = '" . date('Y-m-d') . "'
                                                        AND u_leader = $logged_user_id
                                                        GROUP BY u_department
                                                        ")->result_array();
                                        }
                                        else
                                        {
                                                $query = $this->db->select("COUNT(*) as total")->from("aa_projects")->get()->result_array();
                                                $data['box']['total_projects'] = $query[0]['total'];
                                                $query = $this->db->select("COUNT(*) as total")->from("aa_projects")->where("p_status", "Active")->get()->result_array();
                                                $data['box']['active_projects'] = $query[0]['total'];
                                                $query = $this->db->select("COUNT(*) as total")->from("aa_projects")->where("p_status", "Completed")->get()->result_array();
                                                $data['box']['completed_projects'] = $query[0]['total'];
                                                $query = $this->db->select("COUNT(*) as total")->from("aa_users")->get()->result_array();
                                                $data['box']['total_employee'] = ($query[0]['total'] - 2);
                                                $query = $this->db->query("SELECT COUNT(u_id) as total, u_department FROM aa_users, aa_present WHERE u_id = pr_u_id AND pr_date = '" . date("Y-m-d") . "' GROUP BY u_department")->result_array();
                                        }
                                        
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
                $type = $this->post('type');

                if (!$this->authorization->is_bim_head_or_higher($this->admin_session)) {
                        $this->response(array(
                                'status' => "fail",
                                'message' => 'You do not have access for this page.'
                        ));
                }
                $u_id = $this->admin_session['u_id'];
                $rpt_start = convert_display2db($this->post('rpt_start'));
                $rpt_end = convert_display2db($this->post('rpt_end'));
                switch ($type) {
                        case "dependenciesReport":
                                $this->load->model('weeklywork_model');

                                $status = $this->post('status');

                                $this->db->select('
                                        d.wd_id,
                                        d.dependency_text,
                                        d.dependency_type,
                                        d.dep_leader_ids,
                                        d.priority,
                                        d.status,
                                        d.completed_date,
                                        d.created_date,
                                        u.u_name as created_by,
                                        ww.week_from,
                                        ww.week_to,
                                        p.project_name
                                ');
                                $this->db->from('aa_weekly_work_dependency d');
                                $this->db->join('aa_users u', 'u.u_id = d.created_by', 'left');
                                $this->db->join('aa_weekly_work ww', 'ww.w_id = d.w_id', 'left');
                                $this->db->join('aa_project p', 'p.p_id = ww.project_id', 'left');

                                // Default: show not completed
                                if (empty($status) || $status === 'Incomplete') {
                                        $this->db->where('d.status !=', 'Completed');
                                } elseif ($status !== 'All') {
                                        $this->db->where('d.status', $status);
                                }

                                $this->db->order_by('d.created_date', 'DESC');
                                $deps = $this->db->get()->result_array();

                                foreach ($deps as &$d) {
                                        if ($d['dependency_type'] === 'Internal' && !empty($d['dep_leader_ids'])) {
                                                $leader_ids = explode(',', $d['dep_leader_ids']);
                                                $this->db->select('GROUP_CONCAT(u_name SEPARATOR ", ") AS leader_names');
                                                $this->db->where_in('u_id', $leader_ids);
                                                $leader_query = $this->db->get('aa_users')->row_array();
                                                $d['assigned_to'] = $leader_query['leader_names'];
                                        } else {
                                                $d['assigned_to'] = '-';
                                        }
                                }

                                echo json_encode(['status' => 'pass', 'data' => $deps]);
                                break;
                        case "projectData":
                                $this->load->model('weeklywork_model');

                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $search      = $this->post('search');
                                $txt_search  = $this->post('txt_search'); // optional

                                $leader_id   = $this->post('leader_id');
                                $from_date   = $this->post('from_date');
                                $to_date     = $this->post('to_date');
                                $project_id = $this->post('project_id');

                                $filter_status = $this->post('filter_status');  
                                $records = $this->weeklywork_model->getWeeklyWork($leader_id, $from_date, $to_date, $limit, $offset, null, $project_id, $filter_status);
                                $totalFiltered = $totalData = count($records);

                                $result = [];
                                foreach ($records as $row) {
                                        $nestedData = [];

                                        $nestedData[] = $row['leader_name'] ?? '-';
                                        $nestedData[] = (int)($row['team_assigned'] ?? 0);
                                        $nestedData[] = htmlspecialchars($row['no_of_persons'] ?? '');
                                        $nestedData[] = htmlspecialchars($row['assigned_users'] ?? '-');
                                        $nestedData[] = (int)($row['no_of_projects'] ?? 0);
                                        $nestedData[] = htmlspecialchars($row['project_name'] ?? '');
                                        $nestedData[] = htmlspecialchars(convert_display2db($row['week_from'] ?? '') . " to " . convert_display2db($row['week_to'] ?? ''));
                                        $nestedData[] = htmlspecialchars($row['task_name'] ?? '');
                                        $nestedData[] = htmlspecialchars($row['submission_date'] ?? '');


                                        $statusHtml = htmlspecialchars($row['status'] ?? '');
                                        $nestedData[]  = $statusHtml;
                                        $dependencyHTML = '';
                                        if (!empty($row['incomplete_deps']) && $row['incomplete_deps'] > 0) {
                                                $dependencyHTML .= '<br><a href="javascript:void(0);" class="btn btn-warning btn-xs view-dep-btn"
                data-wid="' . $row['w_id'] . '" data-type="incomplete">
                View Incomplete (' . $row['incomplete_deps'] . ')
            </a>';
                                        }
                                        $dependencyHTML .= '<br><a href="javascript:void(0);" class="btn btn-info btn-xs view-dep-btn"
            data-wid="' . $row['w_id'] . '" data-type="all">View All</a>';
                                        $nestedData[] = $dependencyHTML;

                                        $result[] = $nestedData;
                                }

                                $json_data = [
                                        "draw" => intval($draw),
                                        "recordsTotal" => intval($totalData),
                                        "recordsFiltered" => intval($totalFiltered),
                                        "data" => $result,
                                ];

                                $this->response($json_data);
                                break;


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
                                $records = $this->leave_model->get_reports($rpt_start, $rpt_end, $txt_search);
                                $totalFiltered = $totalData = count($records);
                                $result = array();

                                foreach ($records as $single_record) {
                                        $l_u_id = $single_record['l_u_id'];
                                        $leaves_approved = $this->leave_model->get_reports_approved($rpt_start, $rpt_end, $l_u_id);
                                        $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;
                                        $leaves_declined = $this->leave_model->get_reports_declined($rpt_start, $rpt_end, $l_u_id);
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
                        case "leave_date":
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
                                $criteria['conditions'][] = array('l_is_hourly' => 'No');
                                $criteria['dateseletion'] = array($rpt_start, $rpt_start);
                                if ($txt_search != null) {
                                        $criteria['newcondition'] = ' (u_name like "%' . $txt_search . '%")';
                                }
                                $records = $this->leave_model->get_records($criteria, 'result');
                                $totalFiltered = $totalData = count($records);
                                $result = array();

                                // $criteria_setting['conditions'] = array(array('s_key' => 'total_emp_leaves'));
                                // $records_settings = $this->settings_model->get_records($criteria_setting, 'result');
                                // $total_leaves =  $records_settings[0]['s_value'];
                                foreach ($records as $single_record) {
                                        $l_u_id = $single_record['l_u_id'];
                                        //$leaves_approved = $this->leave_model->get_reports_approved($rpt_start, $rpt_end, $l_u_id);
                                        // $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;
                                        // $leaves_declined = $this->leave_model->get_reports_declined($rpt_start, $rpt_end, $l_u_id);
                                        // $declined_leaves = $leaves_declined[0]['declined_leave'] ?? 0;

                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        //$nestedData[] = (fmod($single_record['final_leave'], 1) !== 0.0) ? $single_record['final_leave'] : (int)$single_record['final_leave'];
                                        //$nestedData[] =  (fmod($approved_leaves, 1) !== 0.0) ? $approved_leaves : (int)$approved_leaves;
                                        //$nestedData[] = (fmod($declined_leaves, 1) !== 0.0) ? $declined_leaves : (int)$declined_leaves;
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $datetime1 = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $datetime2 = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $diff = strtotime($datetime2) - strtotime($datetime1);
                                        if ($single_record['l_is_halfday'] == 'Yes') {
                                                $interval = abs(round($diff / 86400)) + 0.5;
                                                $half_time = " - " . $single_record['l_halfday_time'] . " Half";
                                        } else {
                                                $interval = abs(round($diff / 86400)) + 1;
                                                $half_time =  '';
                                        }
                                        $nestedData[] = $interval;
                                        $nestedData[] = $single_record['l_status'];
                                        $nestedData[] = $single_record['l_is_halfday'] . $half_time;
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['l_u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start . '\', \'' . $rpt_start . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
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
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $datetime1 = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $datetime2 = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $diff = strtotime($datetime2) - strtotime($datetime1);
                                        if ($single_record['l_is_halfday'] == 'Yes') {
                                                $interval = abs(round($diff / 86400)) + 0.5;
                                                $half_time = " - " . $single_record['l_halfday_time'] . " Half";
                                        } else {
                                                $interval = abs(round($diff / 86400)) + 1;
                                                $half_time =  '';
                                        }
                                        $nestedData[] = $interval;
                                        $nestedData[] = $single_record['l_status'];
                                        $nestedData[] = $single_record['l_is_halfday'] . $half_time;
                                        $nestedData[] = $single_record['l_message'] . ((!empty($single_record['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $single_record['l_reply'] : "");
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
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
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
                                        $nestedData[] = $single_record['l_status'];
                                        $nestedData[] = $single_record['l_hourly_time'];
                                        $nestedData[] = $single_record['l_message'] . ((!empty($single_record['l_reply'])) ? "<br/><b>Reply:</b><br/>" . $single_record['l_reply'] : "");

                                        //$nestedData[] = $single_record['l_is_halfday'];

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
                                $totalMinutes = 0;
                                foreach ($records as $single_record) {
                                        $total_hrs = $total_hrs + $single_record['l_hourly_time_hour'];
                                        $parts = explode('.', number_format($single_record['l_hourly_time_hour'], 2));
                                        $hours = (int)$parts[0];
                                        $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
                                        $totalMinutes += ($hours * 60) + $minutes;
                                }
                                $totalHours = intdiv($totalMinutes, 60);
                                $totalRemainingMinutes = $totalMinutes % 60;
                                $result = sprintf("%d.%02d", $totalHours, $totalRemainingMinutes);
                                $json_data = array(
                                        "status" => 'pass',
                                        //total_hrs" => "<b>" . number_format($total_hrs, 2) . "</b>",
                                        "total_hrs" => "<b>" . $result . " hrs</b>",
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
                                //$fromdate = "01-04-" . $this->post('rpt_start');
                                //$todate = "31-03-" . $this->post('rpt_end');
                                $fromdate = $this->post('rpt_start');
                                $todate = $this->post('rpt_end');
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
                                        $approved_leaves_hourly = 0;
                                        $approved_leaves = 0;
                                        $l_u_id = $single_record['u_id'];
                                        $leaves_approved = $this->leave_model->get_reports_approved($rpt_start1, $rpt_end1, $l_u_id);
                                        $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;

                                        $leaves_approved_hourly = $this->leave_model->get_reports_approved_hourly($rpt_start1, $rpt_end1, $l_u_id);
                                        $approved_leaves_hourly = $leaves_approved_hourly[0]['approved_leave'] ?? 0;
                                        $app_leaves = 0;
                                        $app_leaves = $approved_leaves + 0;
                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] =  (fmod($approved_leaves, 1) !== 0.0) ? $approved_leaves : (int)$approved_leaves;
                                        $nestedData[] =  (fmod($approved_leaves_hourly, 1) !== 0.0) ? $approved_leaves_hourly : (int)$approved_leaves_hourly;
                                        $nestedData[]  =  ($total_leaves - $app_leaves);
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start1 . '\', \'' . $rpt_end1 . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors;
                                        $anchors_hourly = '<a href="javascript://" class="btn btn-success btn-md" onClick="showDataHour(\'' . $single_record['u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start1 . '\', \'' . $rpt_end1 . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
                                        $nestedData[] = $anchors_hourly;
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
                        case "hourly_leave_date":
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
                                $criteria['conditions'][] = array('l_is_hourly' => 'Yes');
                                $criteria['dateseletion'] = array($rpt_start, $rpt_start);
                                if ($txt_search != null) {
                                        $criteria['newcondition'] = ' (u_name like "%' . $txt_search . '%")';
                                }
                                $records = $this->leave_model->get_records($criteria, 'result');
                                $totalFiltered = $totalData = count($records);
                                $result = array();
                                foreach ($records as $single_record) {
                                        $l_u_id = $single_record['l_u_id'];

                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'];
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_create_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $nestedData[] = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $datetime1 = date("d-m-Y", strtotime($single_record['l_from_date']));
                                        $datetime2 = date("d-m-Y", strtotime($single_record['l_to_date']));
                                        $nestedData[] = number_format($single_record['l_hourly_time_hour'], 2);
                                        $nestedData[] = $single_record['l_status'];
                                        $nestedData[] = $single_record['l_hourly_time'];
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showData(\'' . $single_record['l_u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $rpt_start . '\', \'' . $rpt_start . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
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
                                        $approved_leaves = 0;
                                        $declined_leaves = 0;
                                        $total_leave = 0;
                                        $l_u_id = $single_record['l_u_id'];
                                        $leaves_approved = $this->leave_model->get_reports_approved_hourly($rpt_start, $rpt_end, $l_u_id);
                                        $approved_leaves = $leaves_approved[0]['approved_leave'] ?? 0;
                                        $leaves_declined = $this->leave_model->get_reports_declined_hourly($rpt_start, $rpt_end, $l_u_id);
                                        $declined_leaves = $leaves_declined[0]['declined_leave'] ?? 0;
                                        $total_leavehour = $this->leave_model->get_reports_total_hourly($rpt_start, $rpt_end, $l_u_id);
                                        $total_leave = $total_leavehour[0]['total_leave'] ?? 0;
                                        $nestedData = array();
                                        $nestedData[] = $single_record['u_name'] ?? null;
                                        //$nestedData[] = (fmod($single_record['final_leave'], 1) !== 0.0) ? $single_record['final_leave'] : (int)$single_record['final_leave'];
                                        $nestedData[] =  (fmod($total_leave, 1) !== 0.0) ? $total_leave : (int)$total_leave;
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
                                        else if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        else if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        else
                                                $total_salary = $fraction;
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
                                        else if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        else if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        else
                                                $total_salary = $fraction;

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
                        case "getDaysHeader":
                                $month = $this->post('month') ?: date('m');
                                $year  = $this->post('year') ?: date('Y');

                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                                // Ensure proper JSON output
                                $this->output
                                        ->set_content_type('application/json')
                                        ->set_output(json_encode(["days" => $daysInMonth]));
                                break;

                        case "attendencedaily":
                                $month = $this->post('month') ?: date('m');
                                $year  = $this->post('year') ?: date('Y');
                                $txt_search = $this->post('txt_search');

                                $this->load->model('timesheet_model');
                                $records = $this->timesheet_model->get_monthly_attendance($month, $year, $txt_search);

                                $result = [];
                                foreach ($records as $idx => $r) {
                                        $nestedData = [];
                                        $nestedData[] = $idx + 1;          // Serial Number
                                        $nestedData[] = $r['u_name'];      // Employee Name

                                        // Add day-wise attendance
                                        foreach ($r['days'] as $d => $status) {
                                                $nestedData[] = $status;
                                        }

                                        $nestedData[] = number_format($r['total_hours'], 2) . " hrs";

                                        $result[] = $nestedData;
                                }

                                $json_data = [
                                        "draw" => intval($this->post('draw')),
                                        "recordsTotal" => count($records),
                                        "recordsFiltered" => count($records),
                                        "data" => $result,
                                ];
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
                                        else if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        else if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        else
                                                $total_salary = $fraction;

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


                        case "Leaderempattendance":
                                $sub_type = $this->post('sub_type');
                                //$rpt_end = $this->post('rpt_end');
                                //$rpt_start = $this->post('rpt_start');
                               
                                $this->load->model('timesheet_model');
                                $draw = $this->post('draw');
                                $sort = $this->post('order');
                                $search = $this->post('search');
                                $offset = $this->post('start');
                                $limit = $this->post('length');
                                $leader_id = $this->post('leader_id');
                                $criteria = array();
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $txt_search = $this->post('txt_search');
                                $records = $this->timesheet_model->get_report_allleaderemployee($rpt_start, $rpt_end, $txt_search, $leader_id);
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
                                        else if ($fraction == '.25')
                                                $total_salary = str_replace($fraction, '.25', '.15');
                                        else if ($fraction == '.50')
                                                $total_salary = str_replace($fraction, '.50', '.30');
                                        else
                                                $total_salary = $fraction;

                                        $nestedData[] = number_format($whole + $total_salary, 2);
                                        $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="postFormData(\'' . $single_record['u_id'] . '\', \'' . $single_record['u_name'] . '\', \'' . $leader_id . '\')"><i class="fa fa-eye"></i><a>&nbsp; ';
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
                                else if ($fraction == '.25')
                                        $total_salary = str_replace($fraction, '.25', '.15');
                                else if ($fraction == '.50')
                                        $total_salary = str_replace($fraction, '.50', '.30');
                                else
                                        $total_salary = $fraction;
                                if ($whours > 0)
                                        $total_hrs = "<b>Total Hours Worked :  " . (number_format($whole + $total_salary, 2)) . " hr</b>";
                                else
                                        $total_hrs = '';
                                $new = array("", "", "", "", $total_hrs, "");
                                if ($result != null)
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
                                $txt_p_cat = $this->post('txt_p_cat');

                                $criteria = array();
                                $criteria['sort_by'] = 'p_name';
                                $criteria['page_no'] = $offset;
                                $criteria['page_size'] = $limit;
                                $criteria['conditions'] = array();
                                $criteria['conditions'][] = array("p_cat" => $txt_p_cat);
                                if ($txt_p_status != '') {
                                        $criteria['conditions'][] = array("p_status LIKE " => $txt_p_status);
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
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin']))
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
                                                                else if ($fraction == '.25')
                                                                        $total_salary = str_replace($fraction, '.25', '.15');
                                                                else if ($fraction == '.50')
                                                                        $total_salary = str_replace($fraction, '.50', '.30');
                                                                else
                                                                        $total_salary = $fraction;

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
                                                //$criteria['conditions'][] = array("t_parent" => 0);
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
                                                                $nestedData[] =  ($single_record['t_parent'] != 0) ? 'Sub Task' : 'Main Task';
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

                                                                $n = $single_record['t_hours_total'];
                                                                $whole = floor($n);      // 1
                                                                $fraction = $n - $whole; // .25
                                                                if ($fraction == '.75')
                                                                        $total_salary = str_replace($fraction, '.75', '.45');
                                                                else if ($fraction == '.25')
                                                                        $total_salary = str_replace($fraction, '.25', '.15');
                                                                else if ($fraction == '.50')
                                                                        $total_salary = str_replace($fraction, '.50', '.30');
                                                                else
                                                                        $total_salary = $fraction;
                                                                $nestedData[] = number_format(($whole + $total_salary) ?? 0, 2);

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
                                                        if ($single_record['t_parent'] != 0) {
                                                                $maintaskname = $this->task_model->getmaintaskname($single_record['t_parent']);
                                                                $nestedData[] = $maintaskname[0]['t_title'] . "<br/> <b>- " . $single_record['t_title'] . "</b>";
                                                        } else {
                                                                $nestedData[] = $single_record['t_title'];
                                                        }
                                                        $nestedData[] =  ($single_record['t_parent'] != 0) ? 'Sub Task' : 'Main Task';
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

                                                        $n = $single_record['t_hours_total'];
                                                        $whole = floor($n);      // 1
                                                        $fraction = $n - $whole; // .25
                                                        if ($fraction == '.75')
                                                                $total_salary = str_replace($fraction, '.75', '.45');
                                                        else if ($fraction == '.25')
                                                                $total_salary = str_replace($fraction, '.25', '.15');
                                                        else if ($fraction == '.50')
                                                                $total_salary = str_replace($fraction, '.50', '.30');
                                                        else
                                                                $total_salary = $fraction;
                                                        $nestedData[] = number_format(($whole + $total_salary) ?? 0, 2);

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
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
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
                                                if ($this->authorization->is_role_allowed($this->admin_session['u_type'], ['Master Admin'])) {
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
                                        $n = $single_record['total_hrs'];
                                        $whole = floor($n);      // 1
                                        $fraction = $n - $whole; // .25
                                        if ($fraction == '.75')
                                                $total_hrs = str_replace($fraction, '.75', '.45');
                                        else if ($fraction == '.25')
                                                $total_hrs = str_replace($fraction, '.25', '.15');
                                        else if ($fraction == '.50')
                                                $total_hrs = str_replace($fraction, '.50', '.30');
                                        else
                                                $total_hrs = $fraction;

                                        $nestedData[] = number_format($whole + $total_hrs, 2);
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
                                //$criteria['conditions'][] = array("u_id <>" => "1");
                                $criteria['conditions'][] = array("u_type !=" => "Master Admin");
                                $criteria['conditions'][] = array("u_type !=" => "Associate User");
                                $criteria['conditions'][] = array("u_type !=" => "Super Admin");

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
                if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
                        //$this->admin_session['u_type'] == 'Project Leader'
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
                                $checkMessage = $this->db->query("SELECT conference_message,task_message,schedule_message FROM  aa_message WHERE me_id = '{$mu_me_id}'")->result_array();
                                if (!empty($checkMessage) && ($checkMessage[0]['conference_message'] == 'Yes' || $checkMessage[0]['task_message'] == 'Yes' || $checkMessage[0]['schedule_message'] == 'Yes')) {
                                        $this->db->where(['mu_me_id' => $mu_me_id, 'mu_u_id' => $mu_u_id, 'mu_read' => 1])->delete("aa_message_users");
                                        $query = $this->db->query("SELECT COUNT(*) as unread_count FROM aa_message_users WHERE  mu_me_id = '{$mu_me_id}' and mu_read = '0' ")->result_array();
                                        if (!empty($query)) {
                                                $unreadUsers = $query[0]['unread_count'] ?? 0;
                                        }
                                        if ($unreadUsers == 0) {
                                                $this->db->where(['mu_me_id' => $mu_me_id])->delete("aa_message_users");
                                                $this->db->where(['me_id' => $mu_me_id])->delete("aa_message");
                                        }
                                }
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
                                        $criteria['conditions'] = array(array('leave_message' => 'No', 'conference_message' => 'No', 'task_message' => 'No', 'schedule_message' => 'No'));

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
                if ($this->authorization->is_bim_head_or_higher($this->admin_session)) {
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
                                                // "recordsTotal" => intval($totalData),
                                                //  "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }


        public function projectmessages_post()
        {
                $this->load->model('project_message_model');
                $act = $this->post('act');
                $u_id = $this->admin_session['u_id'];
                switch ($act) {
                        case 'add':
                                $pm_id = intval($this->post('pm_id'));
                                $pm_p_id = $this->post('pm_p_id') ? intval($this->post('pm_p_id')) : null; // null => general
                                $pm_text = trim($this->post('pm_text'));
                                $creator = intval($this->admin_session['u_id']);
                                $pm_descipline = $this->post('pm_descipline');
                                $selected_users = $this->post('u_ids');
                                $recipients = [$creator];

                                if (empty($pm_text)) {
                                        $this->response(array('status' => 'fail', 'type' => 'popup', 'message' => 'Please enter message text.'));
                                        return;
                                }

                                $data = array(
                                        'pm_p_id' => $pm_p_id,
                                        'pm_created_by' => $creator,
                                        'pm_text' => $pm_text,
                                        'pm_datetime' => date('Y-m-d H:i:s'),
                                        'pm_descipline' => $pm_descipline,
                                );
                                if ($pm_id > 0) $data['pm_id'] = $pm_id;

                                try {
                                        $this->db->trans_start();
                                        $saved_id = $this->project_message_model->save($data);
                                        if (!empty($selected_users) && in_array("ALL_PROJECT", $selected_users)) {
                                                // ALL PROJECT MEMBERS LOGIC

                                                $projUsers = $this->project_message_model->get_recipients_by_project($pm_p_id);
                                                $recipients = array_merge($recipients, $projUsers);
                                        } else {
                                                // ONLY SELECTED USERS
                                                if (!empty($selected_users)) {
                                                        $recipients = array_merge($recipients, $selected_users);
                                                }
                                        }

                                        $recipients = array_unique(array_filter($recipients));
                                        $this->project_message_model->add_recipients($saved_id, $recipients);
                                        $DesktopQueue = 'aa_desktop_notification_queue';
                                        $UserTable = 'aa_users';
                                        $title = "New Message Added";
                                        $message = $pm_text;
                                        $payload = [
                                                'screen_name' => 'Message',
                                                'id' => $saved_id,
                                        ];
                                        foreach (array_unique($recipients)  as $uid) {
                                                $user = $this->db
                                                        ->where('u_id', $uid)
                                                        ->get($UserTable)
                                                        ->row_array();
                                                $this->db->insert($DesktopQueue, [
                                                        'u_id' => $uid,
                                                        'title' => $title,
                                                        'message' => $message,
                                                        'payload' => json_encode($payload),
                                                        'is_sent' => 0,
                                                ]);
                                                if ($user && isset($user['is_web_logged_in']) && $user['is_web_logged_in'] == 1) {
                                                        //log_message('info', "User $uid is logged in. Notification added for polling.");
                                                }
                                        }
                                        $this->db->trans_complete();
                                        $this->response(array('status' => 'pass', 'message' => 'Message saved.'));
                                } catch (Exception $ex) {
                                        $this->response(array('status' => 'fail', 'message' => $ex->getMessage()));
                                }
                                break;

                        case 'list':
                                $pm_id = intval($this->post('pm_id'));
                                $project_id = intval($this->post('project_id')) ?: null;
                                $filter_date = trim($this->post('search_date')) ?: null;
                                $filter_discipline = trim($this->post('search_discipline')) ?: null;
                                if ($pm_id > 0) {
                                        // single record details (header only)
                                        $message = $this->db->where('pm_id', $pm_id)->get('aa_project_messages')->row_array();
                                        if (!empty($message)) {
                                                $message['pm_datetime'] = date("M d, Y H:i", strtotime($message['pm_datetime']));
                                                $this->response(array('status' => 'pass', 'data' => $message));
                                        } else {
                                                $this->response(array('status' => 'fail', 'message' => 'Record not found.'));
                                        }
                                        return;
                                }

                                // datatable listing
                                $draw = intval($this->post('draw'));
                                $offset = intval($this->post('start'));
                                $limit = intval($this->post('length')) ?: 25;
                                $is_admin = in_array(
                                        $this->admin_session['u_type'],
                                        ['Master Admin', 'Super Admin', 'Bim Head', 'MailCoordinator']
                                );

                                $is_leader = ($this->admin_session['u_type'] === 'Project Leader');
                                $criteria = array();
                                $criteria['offset'] = $offset;
                                $criteria['limit'] = $limit;

                                if ($is_admin) {
                                        
                                }
                                elseif ($is_leader) {
                                        $criteria['is_leader'] = true;
                                        $criteria['leader_id'] = $this->admin_session['u_id'];
                                }
                                else {
                                        $criteria['u_id'] = $this->admin_session['u_id'];
                                }


                                // if (empty($is_allowed_create)) {
                                //         // filter by recipient (non admin)
                                //         $criteria['u_id'] = intval($this->admin_session['u_id']);
                                // }
                                if (!empty($project_id)) {
                                        $criteria['project_id'] = $project_id;
                                }
                                if (!empty($filter_date)) {
                                        $criteria['filter_date'] = $filter_date; // YYYY-MM-DD
                                }
                                if (!empty($filter_discipline)) {
                                        $criteria['filter_discipline'] = $filter_discipline;
                                }
                                $list = $this->project_message_model->get_list($criteria);
                                $countCriteria = $criteria;
                                unset($countCriteria['limit'], $countCriteria['offset']);
                                $total = $this->project_message_model->get_list($countCriteria);

                                $data_out = array();
                                foreach ($list as $row) {
                                        $rowOut = array();
                                        $rowOut[] = date("M d, Y", strtotime($row['pm_datetime']));
                                        $rowOut[] = (!empty($row['p_name']) ? $row['p_name'] : 'General');
                                        $rowOut[] = (!empty($row['pm_text']) ? htmlspecialchars(mb_strimwidth($row['pm_text'], 0, 200, '...')) : '');
                                        $rowOut[] = (!empty($row['pm_descipline']) ? $row['pm_descipline'] : '');
                                        
                                        $rowOut[] = $row['reply_count'];
                                        // actions: view thread, edit (if creator/leader/core), delete (if creator/core/master)
                                        $actions = '<a href="javascript://" class="btn btn-info btn-md" onClick="showThreadModal(\'' . $row['pm_id'] . '\')"><i class="fa fa-comments"></i></a>&nbsp';

                                        // if (in_array($this->admin_session['u_type'], ['Master Admin', 'Bim Head', 'Leader'])) {
                                        //         $actions .= '<a href="javascript://" class="btn btn-success btn-md" onClick="showMessageModal(\'' . $row['pm_id'] . '\')"><i class="fa fa-edit"></i></a>&nbsp;';
                                        // }
                                        if (in_array($this->admin_session['u_type'], ['Super Admin', 'Master Admin', 'Bim Head'])) {
                                                $actions .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteProjectMessage(\'' . $row['pm_id'] . '\')"><i class="fa fa-trash"></i></a>';
                                        }
                                        $rowOut[] = $actions;
                                        $data_out[] = $rowOut;
                                }

                                $json = array(
                                        'draw' => $draw,
                                        'recordsTotal' => intval($total),
                                        'recordsFiltered' => intval($total),
                                        'data' => $data_out
                                );
                                $this->response($json);
                                break;

                        case 'thread':
                                $uid = $this->admin_session['u_id'];
                                $pm_id = intval($this->post('pm_id'));
                                $thread = $this->project_message_model->get_thread($pm_id);
                                if (empty($thread['message'])) {
                                        $this->response(['status' => 'fail', 'message' => 'Message not found']);
                                        return;
                                }
                                $msg = $thread['message'];
                                $creator = 'Unknown';
                                if (!empty($thread['participants'])) {
                                        foreach ($thread['participants'] as $p) {
                                                if ($p['pmu_u_id'] == $msg['pm_created_by']) {
                                                        $creator = $p['u_name'];
                                                        break;
                                                }
                                        }
                                }

                                $header_html = '<div class="main-msg">
        <strong>' . htmlspecialchars($creator) . '</strong><br>'
                                        . nl2br(htmlspecialchars($msg['pm_text'])) . '<br>
        <small>' . date("M d, Y H:i", strtotime($msg['pm_datetime'])) . '</small>
    </div>';

                                $replies_html = "";
                                if (!empty($thread['replies'])) {
                                        foreach ($thread['replies'] as $rep) {
                                                $replies_html .= '<div class="reply-msg">
                <strong>' . htmlspecialchars($rep['user_name']) . '</strong><br>'
                                                        . nl2br(htmlspecialchars($rep['pmr_text'])) . '<br>
                <small>' . date("M d, Y H:i", strtotime($rep['pmr_datetime'])) . '</small>
            </div>';
                                        }
                                }
                                
                                $this->db->where('pmu_pm_id', $pm_id)
                                        ->where('pmu_u_id', $uid)
                                        ->update('aa_project_message_users', ['pmu_read' => 1]);

                                $this->response([
                                        'status' => 'pass',
                                        'data' => [
                                                'header' => $header_html,
                                                'replies_html' => $replies_html
                                        ]
                                ]);
                                return;

                        case 'reply':
                                $pm_id = intval($this->post('pm_id'));
                                $rep_text = trim($this->post('rep_text'));
                                $uid = intval($this->admin_session['u_id']);
                                if ($pm_id <= 0 || empty($rep_text)) {
                                        $this->response(array('status' => 'fail', 'message' => 'Invalid request'));
                                        return;
                                }
                                try {
                                        $rep_id = $this->project_message_model->add_reply($pm_id, $uid, $rep_text);

                                        $messageData = $this->db->select('pm_created_by, pm_text')
                                                ->from('aa_project_messages')
                                                ->where('pm_id', $pm_id)
                                                ->get()
                                                ->row_array();
                                        if ($messageData) {
                                                $creator_id = intval($messageData['pm_created_by']);
                                                $origText   = $messageData['pm_text'];

                                                if ($creator_id != $uid) {

                                                        $DesktopQueue = 'aa_desktop_notification_queue';
                                                        $UserTable    = 'aa_users';
                                                        $title = "New Reply";
                                                        $shortMsg = substr($origText, 0, 50) . '...';

                                                        $payload = [
                                                                'screen_name' => 'Message',
                                                                'pm_id'       => $pm_id,

                                                        ];

                                                        $senderName = $this->admin_session['u_name'] ?? 'Someone';

                                                        $notifMsg = "$senderName replied to your message";
                                                        $user = $this->db
                                                                ->where('u_id', $uid)
                                                                ->get($UserTable)
                                                                ->row_array();
                                                        $this->db->insert($DesktopQueue, [
                                                                'u_id'    => $creator_id,
                                                                'title'   => $title,
                                                                'message' => $notifMsg,
                                                                'payload' => json_encode($payload),
                                                                'is_sent' => 0
                                                        ]);

                                                        if ($user && isset($user['is_web_logged_in']) && $user['is_web_logged_in'] == 1) {
                                                                //log_message('info', "User $uid is logged in. Notification added for polling.");
                                                        }
                                                }
                                        }




                                        $this->response(array('status' => 'pass', 'message' => 'Reply added', 'rep_id' => $rep_id));
                                } catch (Exception $ex) {
                                        $this->response(array('status' => 'fail', 'message' => $ex->getMessage()));
                                }
                                break;

                        case 'del':
                                $pm_id = intval($this->post('pm_id'));
                                if ($pm_id <= 0) {
                                        $this->response(array('status' => 'fail', 'message' => 'Invalid request'));
                                        return;
                                }
                                try {
                                        $this->project_message_model->delete($pm_id, intval($this->admin_session['u_id']));
                                        $this->response(array('status' => 'pass', 'message' => 'Message deleted'));
                                } catch (Exception $ex) {
                                        $this->response(array('status' => 'fail', 'message' => $ex->getMessage()));
                                }
                                break;

                        default:
                                $this->response(array('status' => 'fail', 'message' => 'Invalid action'));
                }
        }
        public function message_report_post()
        {
                $this->load->model('message_model');

                $project_id  = $this->post('project_id');
                $search_date = $this->post('search_date');
                $discipline  = $this->post('discipline');

                $draw   = $this->post('draw');
                $start  = $this->post('start');
                $length = $this->post('length');

                $totalData = $this->message_model->get_message_report_count(
                        $project_id,
                        $search_date,
                        $discipline
                );

                $records = $this->message_model->get_message_report(
                        $project_id,
                        $search_date,
                        $discipline,
                        $start,
                        $length
                );

                $data = [];

                foreach ($records as $row) {
                        $data[] = [
                                $row->p_name,
                                date('d M Y', strtotime($row->pm_datetime)),
                                $row->pm_text,
                                $row->pm_descipline,
                                $row->all_replies ?? '—'
                        ];
                }

                echo json_encode([
                        "draw" => intval($draw),
                        "recordsTotal" => intval($totalData),
                        "recordsFiltered" => intval($totalData),
                        "data" => $data
                ]);
        }
}
