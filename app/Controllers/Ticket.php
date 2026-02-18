<?php

namespace App\Controllers;

class Ticket extends BaseController
{
    public function category()
    {
        $request = service('request');
        $db = \Config\Database::connect();

        $search = $request->getPost('search') ?? '';

        $builder = $db->table('aa_ticket_categories C');
        $builder->select('C.id, C.name, C.status, C.parent_id, C.description,
                         GROUP_CONCAT(U.u_name SEPARATOR ", ") as assigned_users,
                         GROUP_CONCAT(U.u_id SEPARATOR ", ") as assigned_users_ids');
        $builder->join('aa_ticket_category_users TU', 'TU.category_id = C.id', 'left');
        $builder->join('aa_users U', 'U.u_id = TU.u_id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('C.name', $search)
                ->orLike('C.status', $search)
                ->orLike('U.u_name', $search)
                ->groupEnd();
        }

        $builder->groupBy(['C.id', 'C.name', 'C.status', 'C.description', 'C.parent_id']);
        $builder->orderBy('C.name', 'ASC');

        $categories = $builder->get()->getResultArray();

        $this->view_data['page'] = 'ticket/category/list';
        $this->view_data['meta_title'] = 'Ticket Category';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['categories'] = $categories;
        $this->view_data['search'] = $search;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $db = \Config\Database::connect();
        $categories = $db->table('aa_ticket_categories')
            ->where('parent_id', 0)
            ->where('status', 'Active')
            ->get()->getResult();

        $this->view_data['page'] = 'ticket/add';
        $this->view_data['meta_title'] = 'Raise Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = $this->session->get('token');
        $this->view_data['categories'] = $categories;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function store()
    {
        $request = service('request');
        $db = \Config\Database::connect();

        $ticket_data = [
            'ticket_number' => $this->genUniqueTickNum($db),
            'subject' => $request->getPost('subject'),
            'description' => $request->getPost('description'),
            'category_id' => $request->getPost('category_id'),
            'u_id' => $this->admin_session['u_id'],
            'desktop_number' => $request->getPost('desktop_number'),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $db->table('aa_tickets')->insert($ticket_data);
        $ticket_id = $db->insertID();

        // Handle file attachments
        $files = $request->getFiles();
        if (isset($files['attachments'])) {
            $attachments = $files['attachments'];
            $ticket_folder = FCPATH . 'assets/tickets/' . $ticket_id . '/';
            if (!is_dir($ticket_folder)) {
                mkdir($ticket_folder, 0777, true);
            }
            $i = 1;
            foreach ($attachments as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $ext = $file->getClientExtension();
                    $new_filename = $ticket_data['ticket_number'] . '_' . $i . '.' . $ext;
                    $file->move($ticket_folder, $new_filename);
                    $db->table('aa_ticket_attachments')->insert([
                        'ticket_id' => $ticket_id,
                        'file_name' => $new_filename,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $i++;
                }
            }
        }

        // Desktop notification: notify assigned users for this ticket's category
        $assigned_users = $db->table('aa_ticket_category_users acu')
            ->select('u.u_id')
            ->join('aa_users u', 'acu.u_id = u.u_id')
            ->where('acu.category_id', $ticket_data['category_id'])
            ->get()->getResultArray();

        if (!empty($assigned_users)) {
            $notifTitle = "New Ticket Created";
            $notifMessage = "Ticket: " . $ticket_data['subject'] . " created by " . $this->admin_session['u_name'];
            $notifPayload = json_encode(['screen_name' => 'Ticket', 'action' => 'ticket_created', 'id' => $ticket_id]);

            $batchData = [];
            foreach ($assigned_users as $au) {
                $batchData[] = [
                    'u_id' => $au['u_id'], 'title' => $notifTitle, 'message' => $notifMessage,
                    'payload' => $notifPayload, 'is_sent' => 0,
                ];
            }
            if (!empty($batchData)) {
                $db->table('aa_desktop_notification_queue')->insertBatch($batchData);
            }
        }

        return redirect()->to('ticket/my');
    }

    public function view($id)
    {
        $db = \Config\Database::connect();

        $ticket = $db->table('aa_tickets t')
            ->select('t.*, tc.name as category_name, u.u_name as created_by_name')
            ->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left')
            ->join('aa_users u', 't.u_id = u.u_id', 'left')
            ->where('t.id', $id)
            ->get()->getRow();

        if (!$ticket) {
            return redirect()->to('ticket/my');
        }

        // Get assigned users for the ticket's category
        $assigned_users = $db->table('aa_ticket_category_users acu')
            ->select('u.u_id, u.u_name, u.u_email')
            ->join('aa_users u', 'acu.u_id = u.u_id')
            ->where('acu.category_id', $ticket->category_id)
            ->get()->getResult();
        $ticket->assigned_users = $assigned_users;

        $is_ticket_creator = ($ticket->u_id == $this->admin_session['u_id']);
        $is_assigned_user = false;
        foreach ($assigned_users as $user) {
            if ($user->u_id == $this->admin_session['u_id']) {
                $is_assigned_user = true;
                break;
            }
        }

        // Get ticket messages
        $messages = $db->table('aa_ticket_messages tm')
            ->select('tm.*, u.u_name as sender_name')
            ->join('aa_users u', 'tm.sender_id = u.u_id', 'left')
            ->where('tm.ticket_id', $id)
            ->orderBy('tm.created_at', 'DESC')
            ->get()->getResult();

        // Get attachments
        $attachments = $db->table('aa_ticket_attachments')
            ->where('ticket_id', $id)
            ->get()->getResult();

        $this->view_data['page'] = 'ticket/view';
        $this->view_data['meta_title'] = 'View Ticket';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['ticket'] = $ticket;
        $this->view_data['messages'] = $messages;
        $this->view_data['attachments'] = $attachments;
        $this->view_data['is_ticket_creator'] = $is_ticket_creator;
        $this->view_data['is_assigned_user'] = $is_assigned_user;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add_message($ticket_id)
    {
        $request = service('request');
        $db = \Config\Database::connect();

        $message = $request->getPost('message');
        $from = $request->getPost('from');

        if (!empty($message)) {
            $db->table('aa_tickets')->where('id', $ticket_id)->update(['status' => 'pending']);
            $db->table('aa_ticket_messages')->insert([
                'ticket_id' => $ticket_id,
                'sender_id' => $this->admin_session['u_id'],
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $message_id = $db->insertID();

            // Desktop notification for ticket message
            $ticket = $db->table('aa_tickets')->where('id', $ticket_id)->get()->getRow();
            if ($ticket) {
                $current_user_id = $this->admin_session['u_id'];
                $creator_id = $ticket->u_id;

                // Get assigned users for this category
                $assigned_user_ids = array_column(
                    $db->table('aa_ticket_category_users')->select('u_id')->where('category_id', $ticket->category_id)->get()->getResultArray(),
                    'u_id'
                );

                $notify_user_ids = [];
                if ($current_user_id == $creator_id) {
                    $notify_user_ids = $assigned_user_ids;
                } elseif (in_array($current_user_id, $assigned_user_ids)) {
                    $notify_user_ids = array_diff($assigned_user_ids, [$current_user_id]);
                    $notify_user_ids[] = $creator_id;
                }

                $notify_user_ids = array_unique(array_filter($notify_user_ids));
                $notifTitle = "New Message in Ticket - " . $ticket->ticket_number;
                $notifMessage = "Message sent by: " . $this->admin_session['u_name'];
                $notifPayload = json_encode(['screen_name' => 'Ticket', 'action' => 'message sent by:' . $this->admin_session['u_name'], 'id' => $ticket_id, 'message_id' => $message_id]);

                foreach ($notify_user_ids as $nuid) {
                    $db->table('aa_desktop_notification_queue')->insert([
                        'u_id' => $nuid, 'title' => $notifTitle, 'message' => $notifMessage,
                        'payload' => $notifPayload, 'is_sent' => 0,
                    ]);
                }
            }
        }

        return redirect()->to('ticket/view/' . $ticket_id . (!empty($from) ? '?from=' . $from : ''));
    }

    public function close($id)
    {
        $db = \Config\Database::connect();
        $from = service('request')->getGet('from');

        // Verify ticket exists
        $ticket = $db->table('aa_tickets')->where('id', $id)->get()->getRow();
        if (!$ticket) {
            session()->setFlashdata('error', 'Ticket not found.');
            return redirect()->to('ticket/my');
        }

        // Only assigned users for this ticket's category can close it
        $logged_user_id = $this->admin_session['u_id'];
        $is_assigned_user = $db->table('aa_ticket_category_users')
            ->where('category_id', $ticket->category_id)
            ->where('u_id', $logged_user_id)
            ->countAllResults() > 0;

        if (!$is_assigned_user) {
            session()->setFlashdata('error', 'You are not authorized to close this ticket.');
            return redirect()->to('ticket/view/' . $id . (!empty($from) ? '?from=' . $from : ''));
        }

        $db->table('aa_tickets')->where('id', $id)->update(['status' => 'closed']);
        session()->setFlashdata('success', 'Ticket closed successfully.');

        // Desktop notification for ticket closure
        $logged_user_id = $this->admin_session['u_id'];
        $creator_id = $ticket->u_id;

        $assigned_user_ids = array_column(
            $db->table('aa_ticket_category_users')->select('u_id')->where('category_id', $ticket->category_id)->get()->getResultArray(),
            'u_id'
        );

        $notify_user_ids = [];
        if ($logged_user_id == $creator_id) {
            $notify_user_ids = $assigned_user_ids;
        } elseif (in_array($logged_user_id, $assigned_user_ids)) {
            $notify_user_ids = array_diff($assigned_user_ids, [$logged_user_id]);
            $notify_user_ids[] = $creator_id;
        }

        $notify_user_ids = array_unique(array_filter($notify_user_ids));
        $notifTitle = "Ticket Closed - " . $ticket->ticket_number;
        $notifMessage = "Ticket is Closed By: " . $this->admin_session['u_name'];
        $notifPayload = json_encode(['screen_name' => 'Ticket', 'action' => 'Ticket is Closed By:' . $this->admin_session['u_name'], 'id' => $id]);

        foreach ($notify_user_ids as $nuid) {
            $db->table('aa_desktop_notification_queue')->insert([
                'u_id' => $nuid, 'title' => $notifTitle, 'message' => $notifMessage,
                'payload' => $notifPayload, 'is_sent' => 0,
            ]);
        }

        return redirect()->to('ticket/view/' . $id . (!empty($from) ? '?from=' . $from : ''));
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $ticket = $db->table('aa_tickets')->where('id', $id)->get()->getRow();

        if ($ticket && $ticket->u_id == $this->admin_session['u_id']) {
            $db->table('aa_ticket_attachments')->where('ticket_id', $id)->delete();
            $db->table('aa_ticket_messages')->where('ticket_id', $id)->delete();
            $db->table('aa_tickets')->where('id', $id)->delete();
            session()->setFlashdata('success_message', 'Ticket deleted successfully.');
        }

        return redirect()->to('ticket/my');
    }

    public function deleteassign($id)
    {
        $db = \Config\Database::connect();
        $ticket = $db->table('aa_tickets')->where('id', $id)->get()->getRow();
        if (!$ticket) {
            session()->setFlashdata('error_message', 'Invalid ticket.');
            return redirect()->to('ticket/assigned');
        }

        $u_id = $this->admin_session['u_id'];
        $assigned = $db->table('aa_ticket_category_users')
            ->where('category_id', $ticket->category_id)
            ->where('u_id', $u_id)
            ->countAllResults();

        if ($assigned > 0) {
            $db->table('aa_ticket_attachments')->where('ticket_id', $id)->delete();
            $db->table('aa_ticket_messages')->where('ticket_id', $id)->delete();
            $db->table('aa_tickets')->where('id', $id)->delete();
            session()->setFlashdata('success_message', 'Ticket deleted successfully.');
        } else {
            session()->setFlashdata('error_message', 'You are not allowed to delete this ticket.');
        }

        return redirect()->to('ticket/assigned');
    }

    public function get_child_categories_ajax()
    {
        $request = service('request');
        $db = \Config\Database::connect();
        $parent_id = $request->getPost('parent_id');

        $children = $db->table('aa_ticket_categories')
            ->where('parent_id', $parent_id)
            ->where('status', 'Active')
            ->get()->getResult();

        return $this->response->setJSON($children);
    }

    public function my()
    {
        $request = service('request');
        $db = \Config\Database::connect();
        $u_id = $this->admin_session['u_id'];

        $ticket_number = $request->getPost('ticket_number') ?? '';
        $subject = $request->getPost('subject') ?? '';
        $status = $request->getPost('status') ?? '';
        $from_date = $request->getPost('from_date') ?? '';
        $to_date = $request->getPost('to_date') ?? '';

        $builder = $db->table('aa_tickets t');
        $builder->select('t.*, tc.name as category_name, u.u_name as created_by_name');
        $builder->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
        $builder->join('aa_users u', 't.u_id = u.u_id', 'left');
        $builder->where('t.u_id', $u_id);
        if (!empty($ticket_number)) $builder->like('t.ticket_number', $ticket_number);
        if (!empty($subject)) $builder->like('t.subject', $subject);
        if (!empty($status)) $builder->where('t.status', $status);
        if (!empty($from_date)) $builder->where('t.created_at >=', $from_date . ' 00:00:00');
        if (!empty($to_date)) $builder->where('t.created_at <=', $to_date . ' 23:59:59');
        $builder->orderBy('t.created_at', 'DESC');
        $tickets = $builder->get()->getResult();

        $this->view_data['page'] = 'ticket/my_tickets';
        $this->view_data['meta_title'] = 'Ticket History';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = $this->session->get('token');
        $this->view_data['tickets'] = $tickets;
        $this->view_data['ticket_number'] = $ticket_number;
        $this->view_data['subject'] = $subject;
        $this->view_data['status'] = $status;
        $this->view_data['from_date'] = $from_date;
        $this->view_data['to_date'] = $to_date;

        return view('template', ['view_data' => $this->view_data]);
    }

    public function assigned()
    {
        $request = service('request');
        $db = \Config\Database::connect();
        $u_id = $this->admin_session['u_id'];

        $created_by = $request->getPost('created_by') ?? '';
        $desktop_number = $request->getPost('desktop_number') ?? '';
        $status = $request->getPost('status') ?? 'open';
        $from_date = $request->getPost('from_date') ?? '';
        $to_date = $request->getPost('to_date') ?? '';

        // Get categories assigned to this user
        $category_ids = $db->table('aa_ticket_category_users')
            ->select('category_id')
            ->where('u_id', $u_id)
            ->get()->getResultArray();
        $cat_ids = array_column($category_ids, 'category_id');

        $tickets = [];
        if (!empty($cat_ids)) {
            $builder = $db->table('aa_tickets t');
            $builder->select('t.*, tc.name as category_name, u.u_name as created_by_name');
            $builder->join('aa_ticket_categories tc', 't.category_id = tc.id', 'left');
            $builder->join('aa_users u', 't.u_id = u.u_id', 'left');
            $builder->whereIn('t.category_id', $cat_ids);
            if (!empty($created_by)) $builder->like('u.u_name', $created_by);
            if (!empty($desktop_number)) $builder->like('t.desktop_number', $desktop_number);
            if ($status !== '') $builder->where('t.status', $status);
            if (!empty($from_date)) $builder->where('t.created_at >=', $from_date . ' 00:00:00');
            if (!empty($to_date)) $builder->where('t.created_at <=', $to_date . ' 23:59:59');
            $builder->orderBy('t.created_at', 'DESC');
            $tickets = $builder->get()->getResult();
        }

        $this->view_data['page'] = 'ticket/assign_tickets';
        $this->view_data['meta_title'] = 'Assign Tickets';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = $this->session->get('token');
        $this->view_data['tickets'] = $tickets;
        $this->view_data['created_by'] = $created_by;
        $this->view_data['desktop_number'] = $desktop_number;
        $this->view_data['status'] = $status;
        $this->view_data['from_date'] = $from_date;
        $this->view_data['to_date'] = $to_date;

        return view('template', ['view_data' => $this->view_data]);
    }

    public function categoryAdd()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('aa_ticket_categories');
        $builder->where('parent_id', 0);
        $builder->where('status', 'Active');
        $parent_categories = $builder->get()->getResultArray();

        $userBuilder = $db->table('aa_users');
        $userBuilder->where('u_status', 'Active');
        $users = $userBuilder->get()->getResultArray();

        $this->view_data['page'] = 'ticket/category/add';
        $this->view_data['meta_title'] = 'Add Ticket Category';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['data'] = [
            'categories' => $parent_categories,
            'users' => $users
        ];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function categoryStore()
    {
        $request = service('request');
        $db = \Config\Database::connect();

        $category_data = [
            'name' => $request->getPost('name'),
            'description' => $request->getPost('description'),
            'parent_id' => $request->getPost('parent_id') ?? 0,
            'status' => $request->getPost('status')
        ];

        $builder = $db->table('aa_ticket_categories');
        $builder->insert($category_data);
        $category_id = $db->insertID();

        $assigned_users = $request->getPost('assigned_users');
        if ($category_id && !empty($assigned_users)) {
            foreach ($assigned_users as $user_id) {
                $db->table('aa_ticket_category_users')->insert([
                    'category_id' => $category_id,
                    'u_id' => $user_id
                ]);
            }
        }

        return redirect()->to('ticket-category');
    }

    public function categoryEdit($id)
    {
        $db = \Config\Database::connect();

        $category = $db->table('aa_ticket_categories')->where('id', $id)->get()->getRowArray();

        $builder = $db->table('aa_ticket_categories');
        $builder->where('parent_id', 0);
        $builder->where('status', 'Active');
        if ($id) {
            $builder->where('id !=', $id);
        }
        $parent_categories = $builder->get()->getResultArray();

        $users = $db->table('aa_users')->where('u_status', 'Active')->get()->getResultArray();

        $assigned_users = $db->table('aa_ticket_category_users')
            ->select('u_id')
            ->where('category_id', $id)
            ->get()
            ->getResultArray();
        $assigned_user_ids = array_column($assigned_users, 'u_id');

        $this->view_data['page'] = 'ticket/category/edit';
        $this->view_data['meta_title'] = 'Edit Ticket Category';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['data'] = [
            'category' => $category,
            'categories' => $parent_categories,
            'users' => $users,
            'assigned_users' => $assigned_user_ids
        ];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function categoryUpdate($id)
    {
        $request = service('request');
        $db = \Config\Database::connect();

        $category_data = [
            'name' => $request->getPost('name'),
            'description' => $request->getPost('description'),
            'parent_id' => $request->getPost('parent_id') ?? 0,
            'status' => $request->getPost('status')
        ];

        $db->table('aa_ticket_categories')->where('id', $id)->update($category_data);

        $db->table('aa_ticket_category_users')->where('category_id', $id)->delete();

        $assigned_users = $request->getPost('assigned_users');
        if (!empty($assigned_users)) {
            foreach ($assigned_users as $user_id) {
                $db->table('aa_ticket_category_users')->insert([
                    'category_id' => $id,
                    'u_id' => $user_id
                ]);
            }
        }

        return redirect()->to('ticket-category');
    }

    public function categoryDelete($id)
    {
        $db = \Config\Database::connect();

        $db->table('aa_ticket_category_users')->where('category_id', $id)->delete();

        $subcategories = $db->table('aa_ticket_categories')->where('parent_id', $id)->get()->getResultArray();
        foreach ($subcategories as $subcat) {
            $db->table('aa_ticket_category_users')->where('category_id', $subcat['id'])->delete();
        }
        $db->table('aa_ticket_categories')->where('parent_id', $id)->delete();

        $db->table('aa_ticket_categories')->where('id', $id)->delete();

        return redirect()->to('ticket-category');
    }

    private function genUniqueTickNum($db)
    {
        do {
            $random_number = mt_rand(1000, 9999);
            $ticket_number = 'TCKT-' . $random_number;
        } while ($db->table('aa_tickets')->where('ticket_number', $ticket_number)->countAllResults() > 0);
        return $ticket_number;
    }
}
