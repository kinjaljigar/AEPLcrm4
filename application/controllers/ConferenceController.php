<?php

use chriskacerguis\RestServer\RestController;

class ConferenceController extends CI_Controller
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

        if ($search == '')
            $url = 'conference/list?page=1&limit=1000&data=' . $dataURL;
        else
            $url = 'conference/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL;

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
        $this->view_data['page'] = 'conference/list';
        $this->view_data['meta_title'] = 'Conferences';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
        $this->view_data['conferences'] = $data['conferences'];
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        $this->load->view("template", array('view_data' => $this->view_data));
        //$this->load->view('conference/list', $data);
    }
    public function edit($conference_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/edit/' . $conference_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching conference details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/timeslots/' . $data['data']['date']); // Change to your actual timeslot API endpoint
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
        $this->view_data['page'] = 'conference/edit';
        $this->view_data['meta_title'] = 'Edit Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['conference'] = $data;
        $this->view_data['timeslots'] = $available_slots;
        $this->view_data['cliBaseUrl'] = $this->cliBaseUrl;
        $this->view_data['token'] = $this->token;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function view($conference_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/edit/' . $conference_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching conference details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/timeslots'); // Change to your actual timeslot API endpoint
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
        $this->view_data['page'] = 'conference/view';
        $this->view_data['meta_title'] = 'View Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['conference'] = $data;
        $this->view_data['timeslots'] = $available_slots;
        $this->view_data['cliBaseUrl'] = $this->cliBaseUrl;
        $this->view_data['token'] = $this->token;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function update($conference_id)
    {
        $updated_data =
            [
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'date'        => $this->input->post('date'),
                'room_id'     => $this->input->post('room_id'),
                'timeslot_id' => $this->input->post('timeslot_id'),
            ];


        $json_data = json_encode($updated_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/update/' . $conference_id);
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
            $error_message = 'An error occurred while adding the conference. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('conference');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('conference/edit/' . $conference_id);
        }
    }
    public function delete($conference_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/delete/' . $conference_id);
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
            show_error('An error occurred while deleting the conference.');
            print $error_message;
            curl_close($ch);
        } else {
            curl_close($ch);
            redirect('conference');
        }
    }
    public function add()
    {
        $sql = "SELECT id , value from aa_conference_time_slots ";
        $query = $this->db->query($sql);
        $timeslots = $query->result_array();
        $this->view_data['page'] = 'conference/add';
        $this->view_data['meta_title'] = 'Add Conference';
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
            'room_id'     => $this->input->post('room_id'),
            'timeslot_id' => $this->input->post('timeslot_id'),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'conference/add');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($new_data)); // Send as form-encoded data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while adding the conference. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('conference');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('conference/add');
        }

        // if (curl_errno($ch)) {
        //     $error_message = curl_error($ch);
        //     log_message('error', 'cURL error: ' . $error_message);
        //     show_error('An error occurred while adding the conference.');
        //     print $error_message;
        //     curl_close($ch);
        // } else {
        //     curl_close($ch);
        //     if ($response['status'] == 200) {
        //         redirect('conference');
        //     } else {
        //         print_r($response['message']);
        //         exit;
        //     }
        // }
    }
    public function updateConference()
    {
        $date = $this->input->post('date');
        $room_id = $this->input->post('room_id');

        // Call external API (or your business logic here)
        $response = $this->callApi($date, $room_id);

        // Check the response and send back the timeslots data (or handle errors)
        $apiResponse  = json_decode($response, true); // Assuming the response is JSON encoded

        $timeslots = isset($apiResponse['availableslots']) ? $apiResponse['availableslots'] : [];

        // Send back a response to the client
        echo json_encode([
            'status' => 'success',
            'message' => 'Conference updated successfully',
            'api_response' => [
                'timeslots' => $timeslots
            ]
        ]);
    }
    function callApi($date, $room_id)
    {
        $url =  'conference/timeslots/' . $date . '/' . $room_id;
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
        //$data['conferences'] = json_decode($response, true);
        return $response;
    }
}
