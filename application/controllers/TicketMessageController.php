<?php
class TicketMessage extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('TicketMessageModel');
    }

    public function store($ticket_id)
    {
        date_default_timezone_set('Asia/Kolkata');
        $message_data = [
            'ticket_id' => $ticket_id,
            'sender_id' => $this->session->userdata('user_id'),
            'message' => $this->input->post('message'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->TicketMessageModel->insert($message_data);
        redirect('ticket/show/' . $ticket_id);
    }
}
