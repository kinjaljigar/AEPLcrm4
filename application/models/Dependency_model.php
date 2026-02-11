<?php
class Dependency_model extends CI_Model
{
    public function get_dependencies($params)
    {
        $page_no     = isset($params['page_no']) ? intval($params['page_no']) : 0;
        $page_size   = isset($params['page_size']) ? intval($params['page_size']) : 10;
        $search      = $params['search'] ?? '';
        $project_id  = $params['project_id'] ?? '';
        $status      = $params['status'] ?? '';
        $created_by  = $params['created_by'] ?? '';
        $assigned_to = $params['assigned_to'] ?? '';
        $from_date   = $params['from_date'] ?? '';
        $to_date     = $params['to_date'] ?? '';
        $user_id     = $params['user_id'];
        $user_type   = $params['user_type'];
        $result_type = $params['result_type'] ?? 'all_records';
        $sort_by     = $params['sort_by'] ?? 'd.created_date';
        $sort_type   = $params['sort_type'] ?? 'DESC';

        $this->db->select("
        d.wd_id,
        d.w_id,
        d.dependency_text,
        d.dependency_type,
        d.priority,
        d.status,
        d.dep_leader_ids,
        d.created_date,
        d.completed_date,
        p.p_name AS project_name,
        u1.u_name AS created_by,
        u1.u_id AS created_by_id,
        GROUP_CONCAT(DISTINCT u2.u_name SEPARATOR ', ') AS assigned_to
    ");
        $this->db->from('aa_weekly_work_dependency d');
        $this->db->join('aa_weekly_work w', 'w.w_id = d.w_id', 'left');
        $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
        $this->db->join('aa_users u1', 'u1.u_id = d.created_by', 'left');
        $this->db->join('aa_users u2', 'FIND_IN_SET(u2.u_id, d.dep_leader_ids)', 'left');

        if ($user_type == 'Project Leader') {
            $this->db->group_start();
            $this->db->where('d.created_by', $user_id);
            $this->db->or_where("FIND_IN_SET('$user_id', d.dep_leader_ids) >", 0);
            $this->db->group_end();
        }

        if (!empty($project_id)) $this->db->where('p.p_id', $project_id);
        if (!empty($status)) $this->db->where('d.status', $status);
        if (!empty($created_by)) $this->db->where('d.created_by', $created_by);
        if (!empty($assigned_to)) $this->db->where("FIND_IN_SET('$assigned_to', d.dep_leader_ids) >", 0);
        if (!empty($from_date) && !empty($to_date)) {
            $this->db->where("DATE(d.created_date) >=", $from_date);
            $this->db->where("DATE(d.created_date) <=", $to_date);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('d.dependency_text', $search);
            $this->db->or_like('p.p_name', $search);
            $this->db->or_like('u1.u_name', $search);
            $this->db->or_like('u2.u_name', $search);
            $this->db->group_end();
        }

        $this->db->group_by('d.wd_id');
        $this->db->order_by($sort_by, $sort_type);

        if ($result_type == 'count_records') {
            return $this->db->count_all_results();
        }

        if ($result_type == 'count_filtered') {
            $query = $this->db->get();
            return $query->num_rows();
        }

        if ($page_size > 0) {
            $this->db->limit($page_size, $page_no);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_latest_dependencies($user_id, $user_type, $limit = 10)
    {
        $this->db->select('
        d.wd_id,
        d.w_id,
        d.dependency_text,
        d.dependency_type,
        d.priority,
        d.status,
        d.dep_leader_ids,
        d.created_date,
        d.completed_date,d.target_date,
        w.week_from,
        w.week_to,
        p.p_name AS project_name,
        u1.u_name AS created_by,
        u1.u_id AS created_by_id,
         GROUP_CONCAT(u2.u_name SEPARATOR ", ") AS assigned_to,
        CASE 
            WHEN FIND_IN_SET(' . $user_id . ', d.dep_leader_ids) 
                 AND d.created_by != ' . $user_id . ' THEN 1 
            ELSE 0 
        END AS is_dependent_on_me
    ');
        $this->db->from('aa_weekly_work_dependency d');
        $this->db->join('aa_weekly_work w', 'w.w_id = d.w_id', 'left');
        $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
        $this->db->join('aa_users u1', 'u1.u_id = d.created_by', 'left');
        $this->db->join('aa_users u2', 'FIND_IN_SET(u2.u_id, d.dep_leader_ids)', 'left');

        if ($user_type == 'Project Leader') {
            $this->db->group_start();
            $this->db->where('d.created_by', $user_id);
            $this->db->or_where("FIND_IN_SET($user_id, d.dep_leader_ids) >", 0);
            $this->db->group_end();
        } elseif ($user_type == 'Bim Head') {
            $this->db->where('d.status !=', 'Completed');
        } elseif ($user_type == 'Master Admin') {
            $this->db->where('d.status !=', 'Completed');
        }

        $this->db->where('d.status !=', 'Completed');

        $this->db->group_by('d.wd_id');
        //$this->db->order_by('d.wd_id', 'DESC');
        $this->db->order_by("
        CASE d.status
                WHEN 'Pending' THEN 1
                WHEN 'In Progress' THEN 2
                ELSE 3
        END
        ", 'ASC');                                              

        $this->db->order_by("
        CASE d.priority
                WHEN 'High' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Low' THEN 3
                ELSE 4
        END
        ", 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }
}
