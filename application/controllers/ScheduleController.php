<?php

use chriskacerguis\RestServer\RestController;

class ScheduleController extends CI_Controller
{
    protected $token;
    protected $admin_session;
    protected $cliBaseUrl;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        if (isset($this->session)) {
            $this->token = $this->session->userdata('token');
            $this->admin_session = $this->session->userdata('admin_session');
            $this->cliBaseUrl = config_item('cli_base_url');
        }
        $this->load->model('message_model');
        $params = array();
        $params['u_id'] = $this->admin_session['u_id'];
        $params['conditions'] = array(array('mu_read' => 0));
        $messages = $this->message_model->get_records($params);
        $this->session->set_userdata(['messages' => $messages]);
    }
    public function listView()
    {
        $search = $this->input->post('search');
        $dataURL = $this->input->post('data') ?? 'upcoming';
        $type = $this->input->post('type') ?? 'mydata';

        if ($search == '')
            $url = 'schedule/list?page=1&limit=1000&data=' . $dataURL . '&type=' . $type;
        else
            $url = 'schedule/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL . '&type=' . $type;

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
        $this->view_data['page'] = 'schedule/list';
        $this->view_data['meta_title'] = 'Schedules';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
        $this->view_data['schedules'] = $data['schedules'];
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        $this->view_data['type'] = $type;
        $this->load->view("template", array('view_data' => $this->view_data));
        //$this->load->view('schedule/list', $data);
    }
    public function edit($schedule_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/edit/' . $schedule_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching Schedule details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/timeslots/' . $data['data']['date']); // Change to your actual timeslot API endpoint
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $timeslotsResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching timeslots.');
        }
        curl_close($ch);
        $timeslots = json_decode($timeslotsResponse, true);
        $available_slots  =  $timeslots['availableslots'];
        $this->view_data['page'] = 'schedule/edit';
        $this->view_data['meta_title'] = 'Edit schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['schedule'] = $data;
        $this->view_data['timeslots'] = $available_slots;
        $this->view_data['cliBaseUrl'] = $this->cliBaseUrl;
        $this->view_data['token'] = $this->token;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function view($schedule_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/edit/' . $schedule_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching schedule details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/timeslots'); // Change to your actual timeslot API endpoint
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $timeslotsResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching timeslots.');
        }
        curl_close($ch);
        $timeslots = json_decode($timeslotsResponse, true);
        $available_slots  =  $timeslots['availableslots'];
        $this->view_data['page'] = 'schedule/view';
        $this->view_data['meta_title'] = 'View schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['schedule'] = $data;
        $this->view_data['timeslots'] = $available_slots;
        $this->view_data['cliBaseUrl'] = $this->cliBaseUrl;
        $this->view_data['token'] = $this->token;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function update($schedule_id)
    {
        $updated_data =
            [
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'date'        => $this->input->post('date'),
                'shedule_type'    => $this->input->post('shedule_type'),
                'timeslot_id' => $this->input->post('timeslot_id'),
            ];


        $json_data = json_encode($updated_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/update/' . $schedule_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
            'Content-Length: ' . strlen($json_data)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while adding the schedule. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('schedule');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('schedule/edit/' . $schedule_id);
        }
    }
    public function delete($schedule_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/delete/' . $schedule_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while deleting the schedule.');
            print $error_message;
            curl_close($ch);
        } else {
            curl_close($ch);
            redirect('schedule');
        }
    }
    public function add()
    {
        $sql = "SELECT id , value from aa_shedule_time_slots ";
        $query = $this->db->query($sql);
        $timeslots = $query->result_array();
        $this->view_data['page'] = 'schedule/add';
        $this->view_data['meta_title'] = 'Add schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['timeslots'] = $timeslots;
        $this->view_data['token'] = $this->token;
        $this->view_data['cliBaseUrl'] = $this->cliBaseUrl;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function addData()
    {
        $new_data = [
            'title'       => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'date'        => $this->input->post('date'),
            'shedule_type'    => $this->input->post('shedule_type'),
            'timeslot_id' => $this->input->post('timeslot_id'),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'schedule/add');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($new_data)); // Send as form-encoded data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while adding the schedule. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('schedule');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('schedule/add');
        }

        // if (curl_errno($ch)) {
        //     $error_message = curl_error($ch);
        //     log_message('error', 'cURL error: ' . $error_message);
        //     show_error('An error occurred while adding the schedule.');
        //     print $error_message;
        //     curl_close($ch);
        // } else {
        //     curl_close($ch);
        //     if ($response['status'] == 200) {
        //         redirect('schedule');
        //     } else {
        //         print_r($response['message']);
        //         exit;
        //     }
        // }
    }
    public function updateSchedule()
    {
        $date = $this->input->post('date');
        $response = $this->callApi($date);

        // Check the response and send back the timeslots data (or handle errors)
        $apiResponse  = json_decode($response, true); // Assuming the response is JSON encoded

        $timeslots = isset($apiResponse['availableslots']) ? $apiResponse['availableslots'] : [];

        // Send back a response to the client
        echo json_encode([
            'status' => 'success',
            'message' => 'schedule updated successfully',
            'api_response' => [
                'timeslots' => $timeslots
            ]
        ]);
    }
    function callApi($date)
    {
        $url =  'schedule/timeslots/' . $date;
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
        //$data['schedules'] = json_decode($response, true);
        return $response;
    }
    public function getProjectUsers($projectId)
    {
        $project = $this->db->query("
        SELECT p_leader
        FROM aa_projects
        WHERE p_id = ?
    ", [$projectId])->row_array();

        if (!$project) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $leaders = [];
        if (!empty($project['p_leader'])) {
            $leaders = $this->db->query("
            SELECT u_id, u_name, u_type
            FROM aa_users
            WHERE FIND_IN_SET(u_id, ?)
              AND u_status = 'Active'
        ", [$project['p_leader']])->result_array();
        }

         $employees = [];
        // if (!empty($project['p_employee'])) {
        //     $employees = $this->db->query("
        //     SELECT u_id, u_name, u_type
        //     FROM aa_users
        //     WHERE FIND_IN_SET(u_id, ?)
        //       AND u_status = 'Active'
        // ", [$project['p_employee']])->result_array();
        // }
        $masters = [];
                $allUsers = array_merge($leaders, $employees, $masters);
        $uniqueUsers = [];
        foreach ($allUsers as $u) {
            $uniqueUsers[$u['u_id']] = $u;
        }
              $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array_values($uniqueUsers)));
    }
}
