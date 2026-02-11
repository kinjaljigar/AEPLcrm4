<?php

class Project_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        if (isset($data['p_id']) && $data['p_id'] > 0) {
            $p_id = $data['p_id'];
            unset($data['p_id']);
            $this->db->select("*")
                ->from('aa_projects')
                ->where('p_number', $data['p_number'])
                ->where('p_id <> ', $p_id);

            $query = $this->db->get();
            if ($results = $query->result_array()) {
                throw new Exception('Project number is already exists.');
            } else {
                $this->db->where('p_id', $p_id)->update('aa_projects', $data);
            }
        } else {

            $this->db->select("*")
                ->from('aa_projects')
                ->where('p_number', $data['p_number']);

            $query = $this->db->get();
            if ($results = $query->result_array()) {
                throw new Exception('Project number is already exists.');
            } else {
                $this->db->insert('aa_projects', $data);
                $p_id = $this->db->insert_id();
            }
        }
        return $p_id;
    }
    public function delete_records($data)
    {
        $this->db->delete('aa_projects', $data); // [PENDING]
    }

    public function get_records($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'p_name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : '*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'
        $params['newcondition'] = isset($params['newcondition']) ? $params['newcondition'] : '';

        if ($params['newcondition'] != '') {
            $this->db->where($params['newcondition']);
        }
        $this->db->select($params['select_list'])
            ->from('aa_projects')
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
    public function save_project_contacts($data)
    {
        if (isset($data['pc_id']) && $data['pc_id'] > 0) {
            $pc_id = $data['pc_id'];
            unset($data['pc_id']);
            $this->db->where('pc_id', $pc_id)->update('aa_project_contacts', $data);
        } else {
            $this->db->insert('aa_project_contacts', $data);
        }
    }
    public function delete_project_contacts($data)
    {
        $this->db->delete('aa_project_contacts', $data);
    }
    public function get_project_contacts($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'pc_name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : '*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_project_contacts')
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
    public function save_project_expense($p_id, $lbl, $val)
    {
        $this->db->delete('aa_project_expense', array('pe_p_id' => $p_id));
        $data = array();
        foreach ($lbl as $key => $value) {
            if ($key == 0) continue;
            $data[] = array(
                'pe_p_id' => $p_id,
                'pe_lbl' => $lbl[$key],
                'pe_val' => $val[$key],
            );
        }
        if (count($data) > 0) {
            $this->db->insert_batch('aa_project_expense', $data);
        }
    }
    public function get_project_expense($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'pe_p_id';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : '*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_project_expense')
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
    public function get_project_by_id($p_id)
    {
        $this->db->select('*')
            ->from('aa_projects')
            ->where('p_id', $p_id);

        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_project_vcom($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'pv_datetime';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'DESC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'V.*, U.u_name';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_project_vcom V')
            //->join('aa_projects P', 'V.pv_p_id = P.p_id')
            ->join('aa_users U', 'V.pv_u_id = U.u_id')
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
    public function save_project_vcom($data)
    {
        if (isset($data['pv_id']) && $data['pv_id'] > 0) {
            $pv_id = $data['pv_id'];
            unset($data['pv_id']);
            //$this->db->where('pv_id', $pv_id)->update('aa_project_vcom', $data);
        } else {
            $this->db->insert('aa_project_vcom', $data);
        }
    }
    public function get_total_salary($p_id)
    {
        //$sql = "SELECT SUM(total_salary) as final_salary FROM (SELECT ((at_end - at_start) / 60 * u_salary) as total_salary FROM aa_attendance A INNER JOIN aa_users U ON A.at_u_id = U.u_id WHERE at_p_id = '{$p_id}') as FinnalDB";
        $sql = "SELECT SUM(total_salary) as final_salary FROM (SELECT ((at_end - at_start) / 60 * u_salary) as total_salary FROM aa_attendance A INNER JOIN aa_users_salary  U ON A.at_u_id = U.u_id WHERE at_p_id = '{$p_id}' and at_date >= u_start_date and at_date < u_end_date) as FinnalDB";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_total_expense($p_id)
    {
        $total_salary = $this->get_total_salary($p_id);
        $total_salary = $total_salary[0]['final_salary'] ?? 0;

        $sql = "SELECT SUM(pe_val) as total_expense FROM aa_project_expense WHERE pe_p_id = '{$p_id}'";
        $query = $this->db->query($sql);
        $total_expense = $query->result_array();
        $total_expense = $total_expense[0]['total_expense'] ?? 0;

        return $total_salary + $total_expense;
    }
    public function get_project_team($p_id)
    {
        //$sql = "SELECT u_name, u_email, u_salary, u_id, SUM(work_hours) as work_hour_total  FROM (SELECT DB.*, (at_end - at_start) / 60 as work_hours  FROM (SELECT DISTINCT u_id, u_name, u_email, u_salary FROM aa_users U INNER JOIN aa_task2user TU ON tu_u_id = u_id WHERE tu_p_id = '{$p_id}') as DB LEFT JOIN aa_attendance ATN ON DB.u_id = ATN.at_u_id AND ATN.at_p_id = '{$p_id}') AS DB2 GROUP BY u_id";
        $sql = "SELECT total_salary as final_salary , u_salary ,u_email , work_hour_total, u_id , u_name FROM (SELECT (SUM((at_end - at_start) / 60 * US.u_salary)) as total_salary , SUM((at_end - at_start) / 60) as work_hour_total,U.u_id as UserId,U.u_name as u_name , U.u_salary, U.u_email, U.u_id FROM aa_attendance A INNER JOIN aa_users_salary US  ON A.at_u_id = US.u_id , aa_users as U WHERE at_p_id = '{$p_id}' and at_date >= US.u_start_date and at_date < US.u_end_date  and U.u_id = US.u_id group by U.u_id) as FinnalDB";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_emply_salary($p_id)
    {
        //$sql = "SELECT total_salary as final_salary , Username FROM (SELECT (SUM((at_end - at_start) / 60 * u_salary)) as total_salary,U.u_id as UserId,U.u_username as Username FROM aa_attendance A INNER JOIN aa_users U ON A.at_u_id = U.u_id WHERE at_p_id = '{$p_id}' group by U.u_id ) as FinnalDB;";
        $sql = "SELECT total_salary as final_salary , total_hrs, Username FROM (SELECT (SUM((at_end - at_start) / 60 * US.u_salary)) as total_salary , SUM((at_end - at_start) / 60) as total_hrs,U.u_id as UserId,U.u_name as Username FROM aa_attendance A INNER JOIN aa_users_salary US  ON A.at_u_id = US.u_id , aa_users as U WHERE at_p_id = '{$p_id}' and at_date >= US.u_start_date and at_date < US.u_end_date  and U.u_id = US.u_id group by U.u_id) as FinnalDB";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function getProjectsForUser(array $projectIds)
    {
        return $this->db->where_in('p_id', $projectIds);
    }
}
