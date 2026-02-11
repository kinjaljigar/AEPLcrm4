<?php

use chriskacerguis\RestServer\RestController;

class AssociateUserTaskController extends CI_Controller
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
        $this->load->model('ProjectTaskModel');
        $params = array();
        $params['u_id'] = $this->admin_session['u_id'];
        //$params['conditions'] = array(array('mu_read' => 0));
        $params['conditions'] = array(array('MU.mu_read' => 0));
        $messages = $this->message_model->get_records($params);
        $this->session->set_userdata(['messages' => $messages]);
    }
    public function list()
    {

        $u_id =  $this->admin_session['u_id'];
        $u_type = $this->admin_session['u_type'];
        $app_user =  $this->admin_session['u_app_auth'];
        $search = $this->input->post('search');
        $dataURL = $this->input->post('data') ?? 'pending';

        // if ($app_user != '1' && $u_type == 'Project Leader') {

        //     $page = 1;
        //     $limit = 10000;
        //     $searchText = $search ?? '';
        //     $data = $dataURL ?? '';
        //     $offset = ($page - 1) * $limit;

        //     $AllTasks = $this->ProjectTaskModel->GetAllTasks($u_id, $search, '1000', $offset, $u_type, $data);
        //     // $TotalTasks = $this->$ProjectTaskModel->getTasks($u_id, $search, $u_type, $data);
        //     //  print_r($TotalTasks);
        //     //exit;

        //     if (empty($AllTasks)) {
        //         $data = ['status' => 'success', 'data' => []];
        //     } else {
        //         $data = ['status' => 'success', 'data' => $AllTasks];
        //     }
        // } else {
        if ($search == '')
            $url = 'task/list?page=1&limit=1000&data=' . $dataURL;
        else
            $url = 'task/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL;
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
        //}
        $this->view_data['page'] = 'company/user/task/list';
        $this->view_data['meta_title'] = 'Task list';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
        $this->view_data['tasks'] = $data;
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function view($task_id)
    {
        $u_id =  $this->admin_session['u_id'];
        $u_type = $this->admin_session['u_type'];
        $app_user =  $this->admin_session['u_app_auth'];

        // if ($app_user != '1' && $u_type == 'Project Leader') {
        //     $Task = $this->ProjectTaskModel->CheckTaskexist($task_id, $u_type);
        //     if (empty($Task)) {
        //         $data = ['status' => 'success', 'task' => []];
        //     } else {
        //         $data = ['status' => 'success', 'task' => $Task];
        //     }
        //     $task = $data['task'];
        //     $allusers = [];
        // } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/edit/' . $task_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching task details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);
        $task = $data['task'];


        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/user/list?page=1&limit=1000');
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Authorization: Bearer ' . $this->token,
        // ]);
        // $response = curl_exec($ch);

        // if (curl_errno($ch)) {
        //     // Handle error
        //     $error_message = curl_error($ch);
        //     log_message('error', 'cURL error: ' . $error_message);
        //     show_error('An error occurred while fetching data.');
        // }
        // curl_close($ch);
        // $dataUsers = json_decode($response, true);
        // $allusers = $dataUsers['adminallusers'];
        //}

        $this->view_data['page'] = 'company/user/task/view';
        $this->view_data['meta_title'] = 'View Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['task'] = $task;
        //$this->view_data['allusers'] = $allusers;
        $this->load->view("template", array('view_data' => $this->view_data));
    }

    public function status($id)
    {

        $this->load->model('ProjectTaskModel');
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            // Handle GET request (fetch task data)
            $task = $this->ProjectTaskModel->CheckTaskexist($id);
            if ($task) {
                // echo json_encode([
                //     'status' => 'pass',
                //     'data' => $task
                // ]);
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'pass',
                        'data' => $task
                    ]));
            } else {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'fail',
                        'message' => 'Task not found'
                    ]));
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Handle POST request (update task status)
            $comment = $this->input->post('t_comment');

            if (empty($comment)) {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Comment is required'
                ]);
                return;
            }
            date_default_timezone_set('Asia/Kolkata');
            $data = [
                'task_completed' => '1',
                'task_reason' => $comment,
                'completed_at' => date('Y-m-d H:i:s')
            ];

            $u_id =  $this->admin_session['u_id'];
            //$this->ProjectTaskModel->updateTask($id, $data, $u_id);

            $json_data = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/statusupdate/' . $id);
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
                $error_message = 'An error occurred while updating task. Please try again later.';
                log_message('error', 'cURL error: ' . curl_error($ch));
            } else {
                $response_data = json_decode($response, true);
                if (isset($response_data['status']) && $response_data['status'] != 200) {
                    $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
                } else {
                    //redirect('usertask');
                    echo json_encode([
                        'status' => 'pass',
                        'message' => 'Task marked as completed'
                    ]);
                }
            }


            $data['error_message'] = $error_message ?? null;
            if (isset($error_message)) {
                $this->session->set_flashdata('error_message', $error_message);
                redirect('usertask/status/' . $id);
            }

            // echo json_encode([
            //     'status' => 'pass',
            //     'message' => 'Task marked as completed'
            // ]);
        } else {
            echo json_encode([
                'status' => 'fail',
                'message' => 'Invalid request method'
            ]);
        }
    }

    public function edit($task_id)
    {
        $adminsession = $this->admin_session;
        if ($adminsession['u_app_auth'] != 1) {
            redirect('usertask');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/edit/' . $task_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching task details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);
        $task = $data['task'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/user/list?page=1&limit=1000');
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
        $dataUsers = json_decode($response, true);
        //$allusers = $dataUsers['adminallusers'];
        $allusers = array_merge(
            isset($dataUsers['allusers']) ? $dataUsers['allusers'] : [],
            isset($dataUsers['adminallusers']) ? $dataUsers['adminallusers'] : []
        );


        $this->view_data['page'] = 'company/user/task/edit';
        $this->view_data['meta_title'] = 'Edit Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['task'] = $task;
        $this->view_data['allusers'] = $allusers;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function update($task_id)
    {
        $adminsession = $this->admin_session;
        if ($adminsession['u_app_auth'] != 1) {
            redirect('usertask');
        }
        $user_ids_array = $this->input->post('user_ids');
        $user_ids_array = array_map('intval', $user_ids_array);
        $user_ids = json_encode($user_ids_array);
        $updated_data = [
            'title'       => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'date' => $this->input->post('date'),
            'time' => $this->input->post('time'),
            'user_ids' => $user_ids, //$this->input->post('user_ids'),
        ];

        $attachments = $_FILES['attachments'] ?? null;
        if ($attachments && isset($attachments['tmp_name']) && is_array($attachments['tmp_name'])) {
            foreach ($attachments['tmp_name'] as $index => $tmpName) {
                if (!empty($tmpName)) {
                    $updated_data["attachments[$index]"] = new CURLFile(
                        $tmpName,
                        $attachments['type'][$index],
                        $attachments['name'][$index]
                    );
                }
            }
        }

        $deletedAttachments = $this->input->post('delete_attachments');
        if ($deletedAttachments && is_array($deletedAttachments)) {
            foreach ($deletedAttachments as $index => $deletedFile) {
                $updated_data["delete_attachments[$index]"] = $deletedFile;
            }
        }


        //$json_data = json_encode($updated_data);

        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/update/' . $task_id);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Content-Type: application/json',
        //     'Authorization: Bearer ' . $this->token,
        //     'Content-Length: ' . strlen($json_data)
        // ]);

        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/update/' . $task_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updated_data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while updating task. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('usertask');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('usertask/edit/' . $task_id);
        }
    }
    public function delete($task_id)
    {
        $adminsession = $this->admin_session;
        if ($adminsession['u_app_auth'] != 1) {
            redirect('usertask');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/delete/' . $task_id);
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
            show_error('An error occurred while deleting the task.');
            print $error_message;
            curl_close($ch);
        } else {
            curl_close($ch);
            redirect('usertask');
        }
    }
    public function add()
    {
        $adminsession = $this->admin_session;
        if ($adminsession['u_app_auth'] != 1) {
            redirect('usertask');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/user/list?page=1&limit=10000');
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
        $dataUsers = json_decode($response, true);
        //$allusers = $dataUsers['adminallusers'];
        //$allusers = isset($dataUsers['adminallusers']) ? $dataUsers['adminallusers'] : [];
        $allusers = array_merge(
            isset($dataUsers['allusers']) ? $dataUsers['allusers'] : [],
            isset($dataUsers['adminallusers']) ? $dataUsers['adminallusers'] : []
        );

        $this->view_data['page'] = 'company/user/task/add';
        $this->view_data['meta_title'] = 'Add Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['token'] = $this->token;
        $this->view_data['allusers'] = $allusers;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function addData()
    {
        $user_ids_array = $this->input->post('user_ids');
        $user_ids_array = array_map('intval', $user_ids_array);
        $user_ids = json_encode($user_ids_array);
        $new_data = [
            'title'       => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'date' => $this->input->post('date'),
            'time' => $this->input->post('time'),
            'user_ids' => $user_ids,
        ];

        $attachments = $_FILES['attachments'] ?? null;
        if ($attachments && isset($attachments['tmp_name']) && is_array($attachments['tmp_name'])) {
            foreach ($attachments['tmp_name'] as $index => $tmpName) {
                if (!empty($tmpName)) {
                    $new_data["attachments[$index]"] = new CURLFile(
                        $tmpName,
                        $attachments['type'][$index],
                        $attachments['name'][$index]
                    );
                }
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'task/add');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $new_data);

        // DO NOT set Content-Type manually
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while adding the task. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('usertask');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('usertask/add');
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

    public function fetchDesktopNotifications()
    {
        $u_id =  $this->admin_session['u_id'];
        if (!$u_id) {
            echo json_encode([]);
            return;
        }

        try {
            // Fetch notifications
            $this->db->where('u_id', $u_id);
            $this->db->where('is_sent', 0);
            $this->db->order_by('id', 'DESC');
            $query = $this->db->get('aa_desktop_notification_queue');
            $notifications = $query->result_array();

            // Mark as sent
            if (!empty($notifications)) {
                // $ids = array_column($notifications, 'id');
                // $this->db->where_in('id', $ids);
                // $this->db->update('aa_desktop_notification_queue', ['is_sent' => 1]);
                $ids = array_column($notifications, 'id');
                $this->db->where_in('id', $ids);
                $this->db->delete('aa_desktop_notification_queue');

                $unsentIds = array_column($notifications, 'id');

                $this->db->where_in('id', $unsentIds);
                $this->db->update('aa_desktop_notification_queue', ['is_sent' => 1]);


                $this->db->where('u_id', $u_id);
                $this->db->where('is_sent', 1);
                $this->db->order_by('id', 'DESC');
                $sentQuery = $this->db->get('aa_desktop_notification_queue');
                $sentNotifications = $sentQuery->result_array();
                $latest10Sent = array_slice($sentNotifications, 0, 10);
                $latest10Ids = array_column($latest10Sent, 'id');

                if (!empty($latest10Ids)) {
                    $this->db->where('u_id', $u_id);
                    $this->db->where('is_sent', 1);
                    $this->db->where_not_in('id', $latest10Ids);
                    $this->db->delete('aa_desktop_notification_queue');
                }
            } else {
                $this->db->where('u_id', $u_id);
                $this->db->where('is_sent', 1);
                $this->db->order_by('id', 'DESC');
                $sentQuery = $this->db->get('aa_desktop_notification_queue');
                $sentNotifications = $sentQuery->result_array();
                $latest10Sent = array_slice($sentNotifications, 0, 10);
                $latest10Ids = array_column($latest10Sent, 'id');
                if (!empty($latest10Ids)) {
                    $this->db->where('u_id', $u_id);
                    $this->db->where('is_sent', 1);
                    $this->db->where_not_in('id', $latest10Ids);
                    $this->db->delete('aa_desktop_notification_queue');
                }
            }
            echo json_encode($notifications);
        } catch (Exception $e) {
            log_message('error', 'Exception in fetchDesktopNotifications: ' . $e->getMessage());
            echo json_encode(['error' => 'Internal Server Error']);
        }
    }

    public function fetchTasks()
    {
        $search = $this->input->post('search');
        $dataURL = $this->input->post('data') ?? 'pending';

        $url = 'task/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $this->view_data['tasks'] = $data;
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view('company/user/task/task_table_partial', array('view_data' => $this->view_data));
    }
}
