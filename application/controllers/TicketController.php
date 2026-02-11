<?php

use chriskacerguis\RestServer\RestController;

class TicketController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('TicketModel');
        $this->load->model('Ticketcategory_model');
        $this->load->model('User_model');
        $this->admin_session = $this->session->userdata('admin_session');
    }

    public function index()
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        $tickets = $this->TicketModel->get_all_tickets();
        $this->view_data['page'] = 'ticket/list';
        $this->view_data['tickets'] = $tickets;
        $this->view_data['meta_title'] = 'Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }

    public function assigned_tickets()
    {
        $user_id = $this->admin_session['u_id'];
        if (!$user_id) {
            redirect('home/login');
        }

        $ticket_number = $this->input->post('ticket_number');
        $subject = $this->input->post('subject');
        $status = $this->input->post('status');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $filters = [];
        if (!empty($ticket_number)) {
            $filters['ticket_number'] = $ticket_number;
        }
        if (!empty($subject)) {
            $filters['subject'] = $subject;
        }
        if (!empty($status)) {
            $filters['status'] = $status;
        } else {
            $filters['status'] = 'open';
        }
        if (!empty($from_date)) {
            $filters['from_date'] = $from_date;
        }
        if (!empty($to_date)) {
            $filters['to_date'] = $to_date;
        }


        $this->view_data['ticket_number'] = $ticket_number;
        $this->view_data['subject'] = $subject;
        $this->view_data['status'] = $status;
        $this->view_data['from_date'] = $from_date;
        $this->view_data['to_date'] = $to_date;

        $tickets  = $this->TicketModel->get_tickets_for_assigned_user($user_id, $filters);
        $this->view_data['page'] = 'ticket/assign_tickets';
        $this->view_data['tickets'] = $tickets;
        $this->view_data['meta_title'] = 'Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }

    public function my_tickets()
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        $user_id =  $this->admin_session['u_id'];

        $ticket_number = $this->input->post('ticket_number');
        $subject = $this->input->post('subject');
        $status = $this->input->post('status');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $filters = [];
        if (!empty($ticket_number)) {
            $filters['ticket_number'] = $ticket_number;
        }
        if (!empty($subject)) {
            $filters['subject'] = $subject;
        }
        if (!empty($status)) {
            $filters['status'] = $status;
        }
        if (!empty($from_date)) {
            $filters['from_date'] = $from_date;
        }
        if (!empty($to_date)) {
            $filters['to_date'] = $to_date;
        }


        $this->view_data['ticket_number'] = $ticket_number;
        $this->view_data['subject'] = $subject;
        $this->view_data['status'] = $status;
        $this->view_data['from_date'] = $from_date;
        $this->view_data['to_date'] = $to_date;

        $tickets = $this->TicketModel->get_tickets_by_user($user_id, $filters);
        $this->view_data['page'] = 'ticket/my_tickets';
        $this->view_data['tickets'] = $tickets;
        $this->view_data['meta_title'] = 'Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }


    public function create()
    {

        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            //show_error('You are not authorized to add ticket.');
            redirect('home/login');
        }
        //$categories = $this->Ticketcategory_model->get_active_categories();
        $categories = $this->Ticketcategory_model->get_parent_categories();
        $this->view_data['page'] = 'ticket/add';
        $this->view_data['categories'] = $categories;
        $this->view_data['meta_title'] = 'Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }

    public function store()
    {
        date_default_timezone_set('Asia/Kolkata');
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        $this->load->library('form_validation');
        $this->form_validation->set_rules('subject', 'Subject', 'required');
        $this->form_validation->set_rules('category_id', 'Category', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $ticket_data = [
                //'ticket_number' => 'TCKT-' . time(),
                'ticket_number' => $this->TicketModel->genUniqueTickNum(),
                'subject' => $this->input->post('subject'),
                'description' => $this->input->post('description'),
                'category_id' => $this->input->post('category_id'),
                'u_id' => $this->admin_session['u_id'],
                'desktop_number' => $this->input->post('desktop_number'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $ticket_id = $this->TicketModel->insert_ticket($ticket_data);
            $ticket_number = $ticket_data['ticket_number'];
            if (!empty($_FILES['attachments']['name'][0])) {
                $filesCount = count($_FILES['attachments']['name']);

                $ticket_files = $this->config->item('ticket_files'); // e.g., ./assets/tickets/
                $ticket_folder = $ticket_files . $ticket_id . "/";
                if (!is_dir($ticket_folder)) {
                    mkdir($ticket_folder, 0777, true);
                }
                for ($i = 0; $i < $filesCount; $i++) {
                    $_FILES['file']['name']     = $_FILES['attachments']['name'][$i];
                    $_FILES['file']['type']     = $_FILES['attachments']['type'][$i];
                    $_FILES['file']['tmp_name'] = $_FILES['attachments']['tmp_name'][$i];
                    $_FILES['file']['error']    = $_FILES['attachments']['error'][$i];
                    $_FILES['file']['size']     = $_FILES['attachments']['size'][$i];
                    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                    $new_filename = $ticket_number . '_' . ($i + 1) . '.' . $ext;

                    $config['upload_path']   = $ticket_folder;
                    $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|mp4|avi|mov|wmv|mkv|flv|3gp|webm';
                    $config['max_size']      = 2048; // 2 MB
                    $config['file_name']     = $new_filename;
                    $config['overwrite']     = true;

                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('file')) {
                        $fileData = $this->upload->data();
                        $this->db->insert('aa_ticket_attachments', [
                            'ticket_id'  => $ticket_id,
                            'file_name'  => $fileData['file_name'],
                            'file_type'  => $fileData['file_type'],
                            'file_size'  => $fileData['file_size'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    } else {

                        log_message('error', $this->upload->display_errors());
                    }
                }
            }
            $assigned_users = $this->db
                ->select('u.u_id, u.u_name, u.is_web_logged_in')
                ->from('aa_ticket_category_users acu')
                ->join('aa_users u', 'acu.u_id = u.u_id')
                ->where('acu.category_id', $ticket_data['category_id'])
                ->get()
                ->result_array();

            $title = "New Ticket Created";
            $message = "Ticket: " . $ticket_data['title'] . " created by " . $this->admin_session['u_name'];
            $payload = [
                'screen_name' => 'Ticket',
                'action' => 'ticket_created',
                'id' => $ticket_id,
            ];

            $DesktopQueue = $this->db->insert_batch(
                'aa_desktop_notification_queue',
                array_map(function ($user) use ($title, $message, $payload) {
                    return [
                        'u_id' => $user['u_id'],
                        'title' => $title,
                        'message' => $message,
                        'payload' => json_encode($payload),
                        'is_sent' => 0,
                    ];
                }, $assigned_users)
            );


            redirect('ticket/my');
        }
    }

    public function view($id)
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        $ticket = $this->TicketModel->get_ticket($id);
        $is_ticket_creator = $ticket->u_id == $logged_user_id;
        $is_assigned_user = false;

        if (!empty($ticket->assigned_users)) {
            foreach ($ticket->assigned_users as $user) {
                if ($user->u_id == $logged_user_id) {
                    $is_assigned_user = true;
                    break;
                }
            }
        }

        if (!$is_ticket_creator && !$is_assigned_user) {
            show_error('You are not authorized to view this ticket.');
        }

        $attachments = $this->db
            ->where('ticket_id', $id)
            ->get('aa_ticket_attachments')
            ->result();

        $this->view_data['attachments'] = $attachments;

        $messages = $this->TicketModel->get_ticket_messages($id);
        $this->view_data['page'] = 'ticket/view';
        $this->view_data['ticket'] = $ticket;
        $this->view_data['messages'] = $messages;
        $this->view_data['is_ticket_creator'] = $is_ticket_creator;
        $this->view_data['is_assigned_user'] = $is_assigned_user;
        $this->view_data['meta_title'] = 'Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->load->view("template", array('view_data' => $this->view_data));
    }
    public function add_message($ticket_id)
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }
        $ticket = $this->TicketModel->get_ticket($ticket_id);
        $current_user_id = $this->admin_session['u_id'];

        if ($ticket->status === 'closed') {
            show_error('Ticket is closed. No more replies allowed.');
        }

        $message = $this->input->post('message');

        $from = $this->input->post('from');

        if (empty($message)) {
            redirect('ticket/view/' . $ticket_id . (!empty($from) ? '?from=' . $from : ''));
        }

        $this->TicketModel->update_status($ticket_id, 'pending');
        $message_id = $this->TicketModel->add_message($ticket_id, $current_user_id, $message);


        $title = "New Message in Ticket - " . $ticket->ticket_number;
        $messageLoad = "Message sent by: " . $this->admin_session['u_name'];
        $payload = [
            'screen_name' => 'Ticket',
            'action' => 'message sent by:' . $this->admin_session['u_name'],
            'id' => $ticket_id,
            'message_id' => $message_id,
        ];
        $notify_user_ids = [];
        $creator_id = $ticket->u_id;
        $assigned_user_ids = array_map(function ($user) {
            return $user->u_id;
        }, $ticket->assigned_users ?? []);
        if ($current_user_id == $creator_id) {
            $notify_user_ids = $assigned_user_ids;
        } elseif (in_array($current_user_id, $assigned_user_ids)) {
            $notify_user_ids = array_diff($assigned_user_ids, [$current_user_id]);
            $notify_user_ids[] = $creator_id;
        }

        $notify_user_ids = array_unique($notify_user_ids);
        foreach ($notify_user_ids as $uid) {
            $user = $this->db->where('u_id', $uid)->get('aa_users')->row_array();
            $this->db->insert('aa_desktop_notification_queue', [
                'u_id'    => $uid,
                'title'   => $title,
                'message' => $messageLoad,
                'payload' => json_encode($payload),
                'is_sent' => 0,
            ]);

            if ($user && isset($user['is_web_logged_in']) && $user['is_web_logged_in'] == 1) {
                //log_message('info', "User $uid is logged in. Notification added for polling.");
            }
        }


        redirect('ticket/view/' . $ticket_id . (!empty($from) ? '?from=' . $from : ''));
    }

    public function close($id)
    {
        $logged_user_id = $this->admin_session['u_id'];
        if (!$logged_user_id) {
            redirect('home/login');
        }

        $from = $this->input->get('from');

        $ticket = $this->TicketModel->get_ticket($id);
        if (!$ticket) {
            $this->session->set_flashdata('error', 'Ticket Not Found.');
            redirect('ticket/view/' . $id);
            return;
        }
        $is_assigned_user = false;
        if (!empty($ticket->assigned_users)) {
            foreach ($ticket->assigned_users as $user) {
                if ($user->u_id == $logged_user_id) {
                    $is_assigned_user = true;
                    break;
                }
            }
        }

        if (!$is_assigned_user) {
            $this->session->set_flashdata('error', 'You are not authorized to close this ticket.');
            redirect('ticket/view/' . $id . (!empty($from) ? '?from=' . $from : ''));
            return;
        }
        $this->TicketModel->update_status($id, 'closed');


        $title = "Ticket Closed - " . $ticket->ticket_number;
        $messageLoad = "Ticket is Closed By: " . $this->admin_session['u_name'];
        $payload = [
            'screen_name' => 'Ticket',
            'action' => 'Ticket is Closed By:' . $this->admin_session['u_name'],
            'id' => $id,
        ];
        $notify_user_ids = [];
        $creator_id = $ticket->u_id;
        $assigned_user_ids = array_map(function ($user) {
            return $user->u_id;
        }, $ticket->assigned_users ?? []);
        if ($logged_user_id == $creator_id) {
            $notify_user_ids = $assigned_user_ids;
        } elseif (in_array($logged_user_id, $assigned_user_ids)) {
            $notify_user_ids = array_diff($assigned_user_ids, [$logged_user_id]);
            $notify_user_ids[] = $creator_id;
        }

        $notify_user_ids = array_unique($notify_user_ids);
        foreach ($notify_user_ids as $uid) {
            $user = $this->db->where('u_id', $uid)->get('aa_users')->row_array();
            $this->db->insert('aa_desktop_notification_queue', [
                'u_id'    => $uid,
                'title'   => $title,
                'message' => $messageLoad,
                'payload' => json_encode($payload),
                'is_sent' => 0,
            ]);

            if ($user && isset($user['is_web_logged_in']) && $user['is_web_logged_in'] == 1) {
                //log_message('info', "User $uid is logged in. Notification added for polling.");
            }
        }


        $this->session->set_flashdata('success', 'Ticket closed successfully.');
        redirect('ticket/view/' . $id . (!empty($from) ? '?from=' . $from : ''));
    }
    public function get_child_categories_ajax()
    {
        $parent_id = $this->input->post('parent_id');
        $children = $this->Ticketcategory_model->get_child_categories($parent_id);

        echo json_encode($children);
    }
    public function delete($id)
    {
        $ticket = $this->TicketModel->get_ticket_by_id($id);
        $user_id = $this->admin_session['u_id'];
        if (!$user_id) {
            redirect('home/login');
        }
        if ($ticket && $ticket->u_id == $user_id) {
            $attachments = $this->db->where('ticket_id', $id)->get('aa_ticket_attachments')->result();
            foreach ($attachments as $file) {
                $file_path = FCPATH . 'assets/tickets/' . $ticket->id . '/' . $file->file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            $this->db->where('ticket_id', $id)->delete('aa_ticket_attachments');
            $this->TicketModel->delete_ticket($id);
            $this->session->set_flashdata('success_message', 'Ticket deleted successfully.');
        } else {
            $this->session->set_flashdata('error_message', 'You are not allowed to delete this ticket.');
        }
        redirect('ticket/my');
    }
    public function deleteassign($id)
    {
        $user_id = $this->admin_session['u_id'];
        if (!$user_id) {
            redirect('home/login');
        }
        $category_id = $this->TicketModel->get_category_id_by_ticket($id);
        if (!$category_id) {
            $this->session->set_flashdata('error_message', 'Invalid ticket.');
            return redirect('ticket/assigned');
        }
        $assigned_users = $this->Ticketcategory_model->get_assigned_users($category_id);
        if (in_array($user_id, $assigned_users)) {
            $this->TicketModel->delete_ticket($id);
            $this->session->set_flashdata('success_message', 'Ticket deleted successfully.');
        } else {
            $this->session->set_flashdata('error_message', 'You are not allowed to delete this ticket.');
        }
        redirect('ticket/assigned');
    }
}
