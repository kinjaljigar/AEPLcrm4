<?php

use chriskacerguis\RestServer\RestController;

class CompanyController extends CI_Controller
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
    public function list()
    {

        $search = $this->input->post('search');

        if ($search == '')
            $url = 'company/list?page=1&limit=1000';
        else
            $url = 'company/list?search=' . urlencode($search) . '&page=1&limit=1000';
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
        $data['companies'] = json_decode($response, true);

        $this->view_data['page'] = 'company/list';
        $this->view_data['meta_title'] = 'Companies';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
        $this->view_data['companies'] = $data['companies'];
        $this->view_data['search'] = $search;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function listUser()
    {

        $search = $this->input->post('search');

        if ($search == '')
            $url = 'company/user/list';
        else
            $url = 'company/user/list?search=' . urlencode($search);
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
        $data['companyUsers'] = json_decode($response, true);

        $this->view_data['page'] = 'company/user/list';
        $this->view_data['meta_title'] = 'Company User List';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['plugins'] = array('datatable' => true, 'form_validation' => true);
        $this->view_data['companyUsers'] = $data['companyUsers'];
        $this->view_data['search'] = $search;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function edit($company_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/edit/' . $company_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching Company details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);
        $this->view_data['page'] = 'company/edit';
        $this->view_data['meta_title'] = 'Edit Company';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['company'] = $data;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function editUser($user_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/edit/user/' . $user_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching User details.');
        }
        curl_close($ch);
        $data = json_decode($response, true);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/list?page=1&limit=1000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $companiesResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching companies.');
        }
        curl_close($ch);
        $companies = json_decode($companiesResponse, true);
        $available_companies  =  $companies['data'];



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'projectlist?page=1&limit=10000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $companiesResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching projects.');
        }
        curl_close($ch);
        $projects = json_decode($companiesResponse, true);
        $available_projects  =  $projects['data'];


        $this->view_data['page'] = 'company/user/edit';
        $this->view_data['meta_title'] = 'Edit Company User';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['companyUser'] = $data['data'][0];
        $this->view_data['available_companies'] = $available_companies;
        $this->view_data['available_projects'] = $available_projects;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function update($company_id)
    {
        $updated_data =
            [
                'company_name'       => $this->input->post('company_name'),
                'address' => $this->input->post('address'),
                'status'        => $this->input->post('status'),

            ];

        $json_data = json_encode($updated_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/update/' . $company_id);
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
            $error_message = 'An error occurred while adding the company. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('company');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('company/edit/' . $company_id);
        }
    }
    public function updateUser($user_id)
    {
        $updated_data =
            [
                'name'  => $this->input->post('name'),
                'mobile'  => $this->input->post('mobile'),
                'username' => $this->input->post('username'),
                'email'    => $this->input->post('email'),
                'password' => $this->input->post('password'),
                'status' => $this->input->post('status'),
                'company_id' => $this->input->post('company_id'),
                'project_ids' => $this->input->post('project_ids'),

            ];

        $json_data = json_encode($updated_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/update/user/' . $user_id);
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
            $error_message = 'An error occurred while updating user. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('companyuser');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('companyuser/edit/' . $user_id);
        }
    }
    public function delete($company_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/delete/' . $company_id);
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
            show_error('An error occurred while deleting the company.');
            print $error_message;
            curl_close($ch);
        } else {
            curl_close($ch);
            redirect('company');
        }
    }
    public function deleteUser($user_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/delete/user/' . $user_id);
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
            show_error('An error occurred while deleting the companyUser.');
            print $error_message;
            curl_close($ch);
        } else {
            curl_close($ch);
            redirect('companyuser');
        }
    }
    public function add()
    {
        $this->view_data['page'] = 'company/add';
        $this->view_data['meta_title'] = 'Add Company';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['token'] = $this->token;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function addUser()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/list?page=1&limit=1000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $companiesResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching companies.');
        }
        curl_close($ch);
        $companies = json_decode($companiesResponse, true);
        $available_companies  =  $companies['data'];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'projectlist?page=1&limit=10000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $companiesResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'cURL error: ' . $error_message);
            show_error('An error occurred while fetching projects.');
        }
        curl_close($ch);
        $projects = json_decode($companiesResponse, true);
        $available_projects  =  $projects['data'];

        $this->view_data['page'] = 'company/user/add';
        $this->view_data['meta_title'] = 'Add Company User';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['token'] = $this->token;
        $this->view_data['available_companies'] = $available_companies;
        $this->view_data['available_projects'] = $available_projects;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function addData()
    {
        $new_data = [
            'company_name'       => $this->input->post('company_name'),
            'address' => $this->input->post('address'),
            'status'        => $this->input->post('status'),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/add');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($new_data)); // Send as form-encoded data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while adding the company. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('company');
            }
        }


        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('company/add');
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
    public function addDataUser()
    {
        $new_data = [
            'name'  => $this->input->post('name'),
            'mobile'  => $this->input->post('mobile'),
            'username' => $this->input->post('username'),
            'email'    => $this->input->post('email'),
            'password' => $this->input->post('password'),
            'status' => $this->input->post('status'),
            'company_id' => $this->input->post('company_id'),
            'project_ids' => $this->input->post('project_ids'),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . 'company/add-user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($new_data)); // Send as form-encoded data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            $error_message = 'An error occurred while adding the company. Please try again later.';
            log_message('error', 'cURL error: ' . curl_error($ch));
        } else {
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] != 200) {
                $error_message = isset($response_data['message']) ? $response_data['message'] : 'An unknown error occurred.';
            } else {
                redirect('companyuser');
            }
        }

        $data['error_message'] = $error_message ?? null;
        if (isset($error_message)) {
            $this->session->set_flashdata('error_message', $error_message);
            redirect('companyuser/add');
        }
    }
}
