<?php

class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        if (isset($data['u_password'])) $data['u_password'] = md5($data['u_password']);
        if (isset($data['u_id']) && $data['u_id'] > 0) {
            $u_id = $data['u_id'];
            unset($data['u_id']);

            $this->db->select("*")
                ->from('aa_users')
                ->where('u_username', $data['u_username'])
                ->where('u_id <> ', $u_id);

            $query = $this->db->get();
            if ($results = $query->result_array()) {
                throw new Exception('Username is already exists.');
            } else {
                $this->db->where('u_id', $u_id)->update('aa_users', $data);
            }
        } else {

            $this->db->select("*")
                ->from('aa_users')
                ->where('u_username', $data['u_username']);

            $query = $this->db->get();
            if ($results = $query->result_array()) {
                throw new Exception('Username is already exists.');
            } else {
                $this->db->insert('aa_users', $data);
                $u_id = $this->db->insert_id();
            }
        }
        return $u_id;
    }
    public function delete_records($data)
    {
        $this->db->delete('aa_users', $data); // [PENDING]
    }

    public function get_records($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'u_name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : '*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'
        $params['newcondition'] = isset($params['newcondition']) ? $params['newcondition'] : ''; // new condition for both OR and AND together
        if ($params['newcondition'] != '') {
            $this->db->where($params['newcondition']);
        }

        $this->db->select($params['select_list'])
            ->from('aa_users')
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
            //echo $this->db->last_query();
            return $query->result_array();
        } else {
            $query = $this->db->get();
            return $query->num_rows();
        }
    }
    public function get_records_present($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'u_name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : '*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_users U')
            ->join('aa_present P', 'U.u_id = P.pr_u_id')
            ->order_by($params['sort_by'], $params['sort_type']);

        foreach ($params['conditions'] as $value) {
            $this->db->where($value);
        }
        if ($params['result_type'] != 'count_records') {
            if ($params['page_size'] > 0) {
                $this->db->limit($params['page_size'], $params['page_no']);
            }
        }
        if ($params['result_type'] == 'all_records') {
            $query = $this->db->get();
            //echo $this->db->last_query();
            return $query->result_array();
        } else {
            $query = $this->db->get();
            return $query->num_rows();
        }
    }


    public function save_users_salary($data, $admin_id)
    {
        if ($data['u_join_date'] == null) $data['u_join_date'] = date("Y-m-d");
        if (isset($data['u_id']) && $data['u_salary'] > 0) {
            //$this->db->where('pc_id', $pc_id)->update('aa_project_contacts', $data);
            $this->db->select("id,u_salary,u_start_date")
                ->from('aa_users_salary')
                ->where('u_id', $data['u_id'])
                ->order_by('id', 'desc')
                ->limit('1', '0');
            $query = $this->db->get();
            if ($results = $query->result_array()) {
                if ($results[0]['u_salary'] != $data['u_salary']) {
                    if ($results[0]['u_start_date'] != date("Y-m-d")) {
                        $this->db->where('id', $results[0]['id'])->update('aa_users_salary', ['u_end_date' => date("Y-m-d", strtotime('now - 1day'))]);
                        $this->db->insert('aa_users_salary', ['u_id' => $data['u_id'], 'u_start_date' => date("Y-m-d"), 'u_end_date' => date("Y-m-d", strtotime(' + 10 years')), 'u_salary' => $data['u_salary']]);
                    } else {
                        $this->db->where('id', $results[0]['id'])->update('aa_users_salary', ['u_id' => $data['u_id'], ' u_start_date' => date("Y-m-d"), 'u_end_date' => date("Y-m-d", strtotime(' + 10 years')), 'u_salary' => $data['u_salary']]);
                    }
                }
            } else {
                $this->db->insert('aa_users_salary', ['u_id' => $data['u_id'], ' u_start_date' => $data['u_join_date'], 'u_salary' => $data['u_salary']]);
            }
            //$this->db->insert('aa_users_salary', ['u_id' => $data['u_id'], ' u_start_date' => $date, 'u_salary' => $data['u_salary']]);
            //$this->db->insert('aa_users_salary', $data);
        } else {
            //$this->db->insert('aa_project_contacts', $data);
            $this->db->insert('aa_users_salary', ['u_id' => $admin_id, ' u_start_date' => $data['u_join_date'], 'u_end_date' => date("Y-m-d", strtotime(' + 10 years')), 'u_salary' => $data['u_salary']]);
        }
    }
    public function delete_users_salary($data)
    {
        $this->db->delete('aa_users_salary', $data);
    }
    public function get_users_salary($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'id';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : '*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        //SELECT a.* , b.u_username FROM `aa_users_salary`as a left join aa_users as b on a.u_id = b.u_id WHERE a.`u_id` = '53' ORDER BY a.`id` ASC;
        $this->db->select($params['select_list'])
            ->from('aa_users U')
            ->join('aa_users_salary US', 'U.u_id = US.u_id')
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
            // echo $this->db->last_query();
            return $query->result_array();
        } else {
            $query = $this->db->get();
            return $query->num_rows();
        }
    }
    public function get_token($u_id)
    {
        $query =  $this->db->select('token,expires_at')
            ->from('aa_user_tokens')
            ->where('u_id', $u_id)
            ->get();
        return $query->result_array();
    }

    public function get_active_users()
    {
        $this->db->select('u_id, u_name, u_type');
        $this->db->from('aa_users');
        $this->db->where('u_status', 'Active');
        //$this->db->where_in('u_type', ['Project Leader', 'Employee']);
        $this->db->order_by('u_type', 'ASC');
        $query = $this->db->get();

        return $query->result_array();
    }
    public function get_user_by_id($u_id)
    {
        return $this->db->get_where('aa_users', ['u_id' => $u_id])->row();
    }
    public function get_all_leaders($check = null)
    {
        $this->db->select('u_id, u_name,u_status');
        $this->db->from('aa_users');
        $this->db->where_in('u_type', ['Project Leader']);
        if($check != null)
        {
            $this->db->where('u_status', 'Active');
        }
        $this->db->order_by('u_name', 'ASC');
        return $this->db->get()->result_array();
    }
}
