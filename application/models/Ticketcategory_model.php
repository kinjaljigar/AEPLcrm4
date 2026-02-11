<?php

class Ticketcategory_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_records($params)
    {

        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'C.name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'C.id, C.name, C.status, C.description, GROUP_CONCAT(U.u_name SEPARATOR ", ") as assigned_users , GROUP_CONCAT(U.u_id SEPARATOR ", ") as assigned_users_ids';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_ticket_categories C')
            ->join('aa_ticket_category_users  TU', 'TU.category_id = C.id', 'left')
            ->join('aa_users U', 'U.u_id = TU.u_id', 'left')
            ->group_by(['C.id', 'C.name', 'C.status', 'C.description'])
            ->order_by($params['sort_by'], $params['sort_type']);

        foreach ($params['conditions'] as $value) {
            $this->db->where($value);
        }
        foreach ($params['or_conditions'] as $value) {
            $this->db->or_where($value);
        }
        if ($params['result_type'] != 'count_records') {
            if ($params['page_size'] > 0) {
                $this->db->limit($params['page_size'], $params['page_no']);
            }
        }
        if ($params['result_type'] == 'all_records') {
            $query = $this->db->get();
            //secho $this->db->last_query();
            return $query->result_array();
        } else {
            $query = $this->db->get();
            return $query->num_rows();
        }
    }
    public function get_all_categories($search = '')
    {
        //return $this->db->get('aa_ticket_categories')->result();
        $this->db->select('C.id, C.name, C.status, C.parent_id, C.description, 
                       GROUP_CONCAT(U.u_name SEPARATOR ", ") as assigned_users, 
                       GROUP_CONCAT(U.u_id SEPARATOR ", ") as assigned_users_ids')
            ->from('aa_ticket_categories C')
            ->join('aa_ticket_category_users TU', 'TU.category_id = C.id', 'left')
            ->join('aa_users U', 'U.u_id = TU.u_id', 'left');

        if (!empty($search)) {
            $this->db->group_start()
                ->like('C.name', $search)
                ->or_like('C.status', $search)
                ->or_like('U.u_name', $search)
                ->group_end();
        }

        $this->db->group_by(['C.id', 'C.name', 'C.status', 'C.description', 'C.parent_id'])
            ->order_by('C.name', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_parent_categories($exclude_id = null)
    {
        $this->db->where('parent_id', 0);
        $this->db->where('status', 'Active');
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('aa_ticket_categories')->result();
    }
    public function get_child_categories($parent_id)
    {
        $this->db->where('status', 'Active');
        $this->db->where('parent_id', $parent_id);
        return $this->db->get('aa_ticket_categories')->result();
    }
    public function get_active_categories()
    {
        $this->db->where('status', 'Active');
        $query = $this->db->get('aa_ticket_categories');
        $categories = $query->result();
        return $this->build_category_tree($categories);
    }
    public function build_category_tree($categories, $parent_id = 0, $level = 0)
    {

        $branch = [];

        foreach ($categories as $category) {

            if ((int)$category->parent_id === (int)$parent_id) {
                $category->level = $level;

                $branch[] = $category;

                $children = $this->build_category_tree($categories, $category->id, $level + 1);
                foreach ($children as $child) {

                    $branch[] = $child;
                }
            }
        }
        return $branch;
    }
    public function insert_category($data)
    {
        $this->db->insert('aa_ticket_categories', $data);
        return $this->db->insert_id();
    }
    public function get_category($id)
    {
        return $this->db->get_where('aa_ticket_categories', ['id' => $id])->row();
    }
    public function update_category($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('aa_ticket_categories', $data);
    }
    public function delete_category($id)
    {
        $this->db->delete('aa_ticket_categories', ['id' => $id]);
    }
    public function assign_user_to_category($category_id, $user_id)
    {
        $this->db->insert('aa_ticket_category_users', [
            'category_id' => $category_id,
            'u_id' => $user_id
        ]);
    }
    public function get_assigned_users($category_id)
    {
        $this->db->select('u_id');
        $this->db->where('category_id', $category_id);
        $query = $this->db->get('aa_ticket_category_users');
        return array_column($query->result_array(), 'u_id');
    }
    public function remove_all_assigned_users($category_id)
    {
        $this->db->delete('aa_ticket_category_users', ['category_id' => $category_id]);
    }
    public function delete_category_and_related($category_id)
    {
        $this->db->where('parent_id', $category_id);
        $subcategories = $this->db->get('aa_ticket_categories')->result_array();

        $all_category_ids = [$category_id];
        foreach ($subcategories as $subcat) {
            $all_category_ids[] = $subcat['id'];
        }

        foreach ($all_category_ids as $cat_id) {
            $this->db->where('category_id', $cat_id);
            $tickets = $this->db->get('aa_tickets')->result_array();

            foreach ($tickets as $ticket) {
                $this->db->where('ticket_id', $ticket['id']);
                $this->db->delete('aa_tickets_messages');

                $this->db->where('id', $ticket['id']);
                $this->db->delete('aa_tickets');
            }

            $this->db->where('category_id', $cat_id);
            $this->db->delete('aa_ticket_category_users');
        }

        $this->db->where('parent_id', $category_id);
        $this->db->delete('aa_ticket_categories');

        $this->db->where('id', $category_id);
        $this->db->delete('aa_ticket_categories');
    }
}
