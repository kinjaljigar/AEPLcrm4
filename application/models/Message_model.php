<?php

class Message_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        if (isset($data['me_id']) && $data['me_id'] > 0) {
            $me_id = $data['me_id'];
            unset($data['me_id']);
            $this->db->where('me_id', $me_id)->update('aa_message', $data);
        } else {
            $this->db->insert('aa_message', $data);
            $me_id = $this->db->insert_id();
            $users = [];
            $sql = "SELECT DISTINCT tu_u_id FROM aa_task2user WHERE tu_p_id = '{$data['me_p_id']}'";
            $query = $this->db->query($sql);
            $results = $query->result_array();
            foreach ($results as $val) {
                $users[] = $val['tu_u_id'];
            }
            $sql = "SELECT DISTINCT t_u_id FROM aa_tasks WHERE t_p_id = '{$data['me_p_id']}'";
            $query = $this->db->query($sql);
            $results = $query->result_array();
            foreach ($results as $val) {
                $users[] = $val['t_u_id'];
            }
            $users = array_unique($users);
            $batchdata = [];
            foreach ($users as $val) {
                $batchdata[] = array(
                    'mu_p_id' => $data['me_p_id'],
                    'mu_u_id' => $val,
                    'mu_me_id' => $me_id,
                );
            }
            $this->db->insert_batch('aa_message_users', $batchdata);
        }
    }
    public function UpdateLeaveMessageToBimHead($data, $department = null)
    {
        if (isset($data['me_id']) && $data['me_id'] > 0) {
            $me_id = $data['me_id'];
            unset($data['me_id']);
            $this->db->where('me_id', $me_id)->update('aa_message', $data);
        } else {
            $this->db->insert('aa_message', $data);
            $me_id = $this->db->insert_id();
            $users = [];
            //$sql = "SELECT DISTINCT t_u_id FROM aa_tasks WHERE t_p_id = '{$data['me_p_id']}'";
            $sql = "select DISTINCT u_id  from aa_users where u_type in ('Bim Head')";
            $query = $this->db->query($sql);
            $results = $query->result_array();
            foreach ($results as $val) {
                //$users[] = $val['t_u_id'];
                $users[] = $val['u_id'];
            }
            $users = array_unique($users);
            $batchdata = [];
            foreach ($users as $val) {
                $batchdata[] = array(
                    'mu_p_id' => $data['me_p_id'],
                    'mu_u_id' => $val,
                    'mu_me_id' => $me_id,
                );
            }
            $this->db->insert_batch('aa_message_users', $batchdata);
        }
    }

    public function saveLeaveMessage($data, $department = null)
    {
        if (isset($data['me_id']) && $data['me_id'] > 0) {
            $me_id = $data['me_id'];
            unset($data['me_id']);
            $this->db->where('me_id', $me_id)->update('aa_message', $data);
        } else {
            $this->db->insert('aa_message', $data);
            $me_id = $this->db->insert_id();
            $users = [];
            //$sql = "SELECT DISTINCT t_u_id FROM aa_tasks WHERE t_p_id = '{$data['me_p_id']}'";
            $sql = "select DISTINCT u_id  from aa_users where u_department in ('{$department}','Admin') and u_type in ('Bim Head')";
            $query = $this->db->query($sql);
            $results = $query->result_array();
            foreach ($results as $val) {
                //$users[] = $val['t_u_id'];
                $users[] = $val['u_id'];
            }
            $users = array_unique($users);
            $batchdata = [];
            foreach ($users as $val) {
                $batchdata[] = array(
                    'mu_p_id' => $data['me_p_id'],
                    'mu_u_id' => $val,
                    'mu_me_id' => $me_id,
                );
            }
            $this->db->insert_batch('aa_message_users', $batchdata);
        }
    }
    public function delete_records($data)
    {
        $this->db->delete('aa_message', $data);
        $this->db->delete('aa_message_users', ['mu_me_id' => $data['me_id']]);
    }

    public function get_records($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'me_datetime';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'DESC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'M.*, P.p_name';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_message M')
            ->join('aa_projects P', 'P.p_id = M.me_p_id', 'left')
            ->order_by($params['sort_by'], $params['sort_type']);

        if (!empty($params['u_id'])) {
            $this->db->join('aa_message_users MU', 'MU.mu_me_id = M.me_id', 'left');
            $this->db->where("mu_u_id", $params['u_id']);
        }
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

    public function get_message_report($project_id, $date, $discipline, $start, $limit)
    {
        $this->db->select("
        p.p_name,
        m.pm_id,
        m.pm_text,
        m.pm_datetime,
        m.pm_descipline,

        GROUP_CONCAT(
            CONCAT(
                u.u_name,
                ' (', DATE_FORMAT(r.pmr_datetime, '%d %b %Y %H:%i'), '): ',
                r.pmr_text
            )
            ORDER BY r.pmr_datetime ASC
            SEPARATOR '\n\n'
        ) AS all_replies
    ");

        $this->db->from('aa_project_messages m');
        $this->db->join('aa_projects p', 'p.p_id = m.pm_p_id', 'left');
        $this->db->join('aa_project_message_replies r', 'r.pmr_pm_id = m.pm_id', 'left');
        $this->db->join('aa_users u', 'u.u_id = r.pmr_u_id', 'left');

        if ($project_id) {
            $this->db->where('m.pm_p_id', $project_id);
        }

        if ($discipline) {
            $this->db->where('m.pm_descipline', $discipline);
        }

        if ($date) {
            $this->db->where('DATE(m.pm_datetime)', $date);
        }

        $this->db->group_by('m.pm_id');
        $this->db->order_by('m.pm_datetime', 'DESC');
        $this->db->limit($limit, $start);

        return $this->db->get()->result();
    }


    public function get_message_report_count($project_id, $date, $discipline)
    {
        $this->db->select('COUNT(DISTINCT m.pm_id) AS total');
        $this->db->from('aa_project_messages m');

        if ($project_id) {
            $this->db->where('m.pm_p_id', $project_id);
        }

        if ($discipline) {
            $this->db->where('m.pm_descipline', $discipline);
        }

        if ($date) {
            $this->db->where('DATE(m.pm_datetime)', $date);
        }

        return $this->db->get()->row()->total;
    }
}
