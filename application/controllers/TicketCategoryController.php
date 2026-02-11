<?php

use chriskacerguis\RestServer\RestController;

class TicketCategoryController extends CI_Controller
{
    protected $token;
    protected $admin_session;
    protected $cliBaseUrl;

    function __construct()
    {
        parent::__construct();
        if (isset($this->session)) {
            $this->token = $this->session->userdata('token');
            $this->admin_session = $this->session->userdata('admin_session');
            $this->cliBaseUrl = config_item('cli_base_url');
        }
        $this->load->model('Ticketcategory_model');
        $this->load->model('User_model');
    }


    public function index()
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        if (
            $this->admin_session['u_type'] != 'Master Admin' &&
            $this->admin_session['u_type'] != 'Super Admin' &&
            $this->admin_session['u_type'] != 'Bim Head'
        ) {
            show_error('You are not authorized to check data.');
        }
        $search = $this->input->post('search');
        $categories = $this->Ticketcategory_model->get_all_categories($search);
        $this->view_data['page'] = 'ticket/category/list';
        $this->view_data['categories'] = $categories;
        $this->view_data['meta_title'] = 'Ticket Category';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['search'] = $search;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function create()
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        if (
            $this->admin_session['u_type'] != 'Master Admin' &&
            $this->admin_session['u_type'] != 'Super Admin' &&
            $this->admin_session['u_type'] != 'Bim Head'
        ) {
            show_error('You are not authorized to check data.');
        }
        $this->load->helper(['form']);
        $this->load->library(['form_validation']);
        $data['categories'] = $this->Ticketcategory_model->get_parent_categories();
        $data['users'] = $this->User_model->get_active_users();

        $this->view_data['page'] = 'ticket/category/add';
        $this->view_data['data'] = $data;
        $this->view_data['meta_title'] = 'Add Ticket Category';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function store()
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        if (
            $this->admin_session['u_type'] != 'Master Admin' &&
            $this->admin_session['u_type'] != 'Super Admin' &&
            $this->admin_session['u_type'] != 'Bim Head'
        ) {
            show_error('You are not authorized to check data.');
        }
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Category Name', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $category_data = [
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'parent_id' => $this->input->post('parent_id'),
                'status' => $this->input->post('status')
            ];
            $category_id = $this->Ticketcategory_model->insert_category($category_data);

            if ($category_id) {
                $assigned_users = $this->input->post('assigned_users');
                if (!empty($assigned_users)) {
                    foreach ($assigned_users as $user_id) {
                        $this->Ticketcategory_model->assign_user_to_category($category_id, $user_id);
                    }
                    redirect('ticket-category');
                } else {
                    //$this->Ticketcategory_model->delete_category($category_id);
                    $this->session->set_flashdata('error_message', 'Category was saved.');
                    redirect('ticket-category');
                }
            } else {
                $this->session->set_flashdata('error_message', 'Failed to save category.');
                redirect('ticket-category/add');
            }
        }
    }
    public function edit($id)
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        if (
            $this->admin_session['u_type'] != 'Master Admin' &&
            $this->admin_session['u_type'] != 'Super Admin' &&
            $this->admin_session['u_type'] != 'Bim Head'
        ) {
            show_error('You are not authorized to check data.');
        }
        $data['category'] = $this->Ticketcategory_model->get_category($id);
        //$data['categories'] = $this->Ticketcategory_model->get_all_categories();
        $data['categories'] = $this->Ticketcategory_model->get_parent_categories();
        $data['users'] = $this->User_model->get_active_users();
        $data['assigned_users'] = $this->Ticketcategory_model->get_assigned_users($id);
        $this->view_data['page'] = 'ticket/category/edit';

        $this->view_data['data'] = $data;
        $this->view_data['meta_title'] = 'Edit Ticket Category';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function update($id)
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        if (
            $this->admin_session['u_type'] != 'Master Admin' &&
            $this->admin_session['u_type'] != 'Super Admin' &&
            $this->admin_session['u_type'] != 'Bim Head'
        ) {
            show_error('You are not authorized to check data.');
        }
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Category Name', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {

            $category_data = [
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'parent_id' => $this->input->post('parent_id'),
                'status' => $this->input->post('status')
            ];
            $this->Ticketcategory_model->update_category($id, $category_data);

            $this->Ticketcategory_model->remove_all_assigned_users($id);
            $assigned_users = $this->input->post('assigned_users');
            if (!empty($assigned_users)) {
                foreach ($assigned_users as $user_id) {
                    $this->Ticketcategory_model->assign_user_to_category($id, $user_id);
                }
            }

            redirect('ticket-category');
        }
    }
    public function delete($id)
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        if (
            $this->admin_session['u_type'] != 'Master Admin' &&
            $this->admin_session['u_type'] != 'Super Admin' &&
            $this->admin_session['u_type'] != 'Bim Head'
        ) {
            show_error('You are not authorized to check data.');
        }
        $this->load->model('Ticketcategory_model');
        $this->Ticketcategory_model->delete_category_and_related($id);
        $this->session->set_flashdata('success_message', 'Category and related data deleted successfully.');
        redirect('ticket-category');
    }
}
