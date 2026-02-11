<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TicketModel extends CI_Model
{

    public function insert_ticket($data)
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->db->insert('aa_tickets', $data);
        return $this->db->insert_id();
    }

    public function get_all_tickets()
    {
        $this->db->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $this->db->from('aa_tickets t');
        $this->db->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $this->db->join('aa_users u', 't.u_id = u.u_id', 'left');
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_tickets_for_assigned_user($u_id, $filters = [])
    {
        $this->db->select('category_id');
        $this->db->where('u_id', $u_id);
        $categories = $this->db->get('aa_ticket_category_users')->result();

        if (empty($categories)) return [];

        $category_ids = array_map(fn($row) => $row->category_id, $categories);

        $this->db->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $this->db->from('aa_tickets t');
        $this->db->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $this->db->join('aa_users u', 't.u_id = u.u_id', 'left');

        if (!empty($filters['ticket_number'])) {
            $this->db->like('t.ticket_number', $filters['ticket_number']);
        }
        if (!empty($filters['subject'])) {
            $this->db->like('t.subject', $filters['subject']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        // if (!empty($filters['from_date'])) {
        //     $this->db->where('DATE(t.created_at) >=', $filters['from_date']);
        // }
        // if (!empty($filters['to_date'])) {
        //     $this->db->where('DATE(t.created_at) <=', $filters['to_date']);
        // }
        if (!empty($filters['from_date'])) {
            $this->db->where('t.created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('t.created_at <=', $filters['to_date'] . ' 23:59:59');
        }

        $this->db->where_in('t.category_id', $category_ids);

        return $this->db->get()->result();
    }

    public function get_assigned_users($category_id)
    {
        $this->db->select('u.u_id, u.u_name as name, u.email');
        $this->db->from('aa_ticket_category_users c');
        $this->db->join('aa_users u', 'u.u_id = c.u_id');
        $this->db->where('c.category_id', $category_id);
        return $this->db->get()->result();
    }

    public function get_tickets_by_user($u_id, $filters = [])
    {

        $this->db->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $this->db->from('aa_tickets t');
        $this->db->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $this->db->join('aa_users u', 't.u_id = u.u_id', 'left');
        $this->db->where('t.u_id', $u_id);
        if (!empty($filters['ticket_number'])) {
            $this->db->like('t.ticket_number', $filters['ticket_number']);
        }
        if (!empty($filters['subject'])) {
            $this->db->like('t.subject', $filters['subject']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('t.created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('t.created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();

        //return $this->db->where('u_id', $u_id)->get('aa_tickets')->result();
    }

    public function get_ticket($id)
    {
        $this->db->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $this->db->from('aa_tickets t');
        $this->db->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $this->db->join('aa_users u', 't.u_id = u.u_id', 'left');
        $this->db->where('t.id', $id);
        $ticket = $this->db->get()->row();

        if ($ticket) {
            $this->db->select('u.u_id, u.u_name, u.u_email');
            $this->db->from('aa_ticket_category_users acu');
            $this->db->join('aa_users u', 'acu.u_id = u.u_id');
            $this->db->where('acu.category_id', $ticket->category_id);
            $ticket->assigned_users = $this->db->get()->result();
        }

        return $ticket;
    }


    public function update_ticket($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('aa_tickets', $data);
    }
    public function update_status($ticket_id, $status)
    {
        return $this->db->where('id', $ticket_id)->update('aa_tickets', ['status' => $status]);
    }

    public function delete_ticket($id)
    {
        return $this->db->delete('aa_tickets', ['id' => $id]);
    }

    public function get_ticket_messages($ticket_id)
    {
        $this->db->select('tm.*, u.u_name as sender_name');
        $this->db->from('aa_ticket_messages tm');
        $this->db->join('aa_users u', 'tm.sender_id = u.u_id', 'left');
        $this->db->where('tm.ticket_id', $ticket_id);
        $this->db->order_by('tm.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function insert_message($data)
    {
        $this->db->insert('aa_aa_ticket_messages', $data);
        return $this->db->insert_id();
    }
    public function add_message($ticket_id, $sender_id, $message)
    {

        date_default_timezone_set('Asia/Kolkata');
        $data = [
            'ticket_id'   => $ticket_id,
            'sender_id'     => $sender_id,
            'message'     => $message,
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $this->db->insert('aa_ticket_messages', $data);
        return $this->db->insert_id();
        // return $this->db->insert('aa_ticket_messages', [
        //     'ticket_id' => $ticket_id,
        //     'sender_id' => $sender_id,
        //     'message' => $message,
        //     'created_at' => date('Y-m-d H:i:s')
        // ]);
    }

    public function get_tickets_by_status($status)
    {
        $this->db->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $this->db->from('aa_tickets t');
        $this->db->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $this->db->join('aa_users u', 't.u_id = u.u_id', 'left');
        $this->db->where('t.status', $status);
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_tickets_by_category($category_id)
    {
        $this->db->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $this->db->from('aa_tickets t');
        $this->db->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $this->db->join('aa_users u', 't.u_id = u.u_id', 'left');
        $this->db->where('t.category_id', $category_id);
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();
    }
    public function get_ticket_by_id($id)
    {
        return $this->db->where('id', $id)->get('aa_tickets')->row();
    }
    public function get_category_id_by_ticket($ticket_id)
    {
        $this->db->select('category_id');
        $this->db->from('aa_tickets');
        $this->db->where('id', $ticket_id);
        $result = $this->db->get()->row();

        return $result ? $result->category_id : null;
    }
    public function genUniqueTickNum()
    {
        do {
            $random_number = mt_rand(1000, 9999);
            $ticket_number = 'TCKT-' . $random_number;
        } while ($this->isTicketNumberExists($ticket_number));

        return $ticket_number;
    }

    private function isTicketNumberExists($ticket_number)
    {
        return $this->db->where('ticket_number', $ticket_number)
            ->count_all_results('aa_tickets') > 0;
    }
}
