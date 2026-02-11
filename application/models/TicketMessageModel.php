<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TicketMessageModel extends CI_Model
{

    public function insert_message($data)
    {
        $this->db->insert('ticket_messages', $data);
        return $this->db->insert_id();
    }

    public function get_messages_by_ticket($ticket_id)
    {
        $this->db->select('tm.*, u.name as sender_name');
        $this->db->from('ticket_messages tm');
        $this->db->join('users u', 'tm.sender_id = u.id', 'left');
        $this->db->where('tm.ticket_id', $ticket_id);
        $this->db->order_by('tm.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function delete_messages_by_ticket($ticket_id)
    {
        $this->db->delete('ticket_messages', ['ticket_id' => $ticket_id]);
    }
}
