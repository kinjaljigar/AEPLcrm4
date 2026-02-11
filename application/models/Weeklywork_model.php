<?php
class Weeklywork_model extends CI_Model
{
    public function get_weekly_work_list($leader_id = null, $from_date = null, $to_date = null, $project_id = null, $filter_status = null)
    {
        $this->db->select('
    w.*,
    p.p_name,
    p.p_number,
    u.u_name AS created_by,
    (
        SELECT GROUP_CONCAT(u2.u_name ORDER BY u2.u_name SEPARATOR ", ")
        FROM aa_weekly_work_users wu
        JOIN aa_users u2 ON u2.u_id = wu.u_id
        WHERE wu.weekly_work_id = w.w_id
    ) AS assigned_users
');
        $this->db->from('aa_weekly_work w');
        $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
        $this->db->join('aa_users u', 'u.u_id = w.leader_id', 'left');
        //$this->db->where('w.leader_id', $leader_id);
        if (!empty($leader_id)) {
            $this->db->where('w.leader_id', $leader_id);
        }
        if (!empty($project_id)) {
            $this->db->where('w.p_id', $project_id);
        }

        if (!empty($from_date) && !empty($to_date)) {
            $this->db->where('w.week_from >=', $from_date);
            $this->db->where('w.week_to <=', $to_date);
        } elseif (!empty($from_date)) {
            $this->db->where('w.week_from >=', $from_date);
        } elseif (!empty($to_date)) {
            $this->db->where('w.week_to <=', $to_date);
        }
        if (!empty($filter_status) && $filter_status != 'All') {
            $this->db->where('w.status', $filter_status);
        }

        $this->db->order_by('w.w_id', 'DESC');
        $query = $this->db->get();
        $records = $query->result_array();

        foreach ($records as &$r) {
            if ($r['dep_leader_ids']) {
                $ids = explode(',', $r['dep_leader_ids']);
                $this->db->where_in('u_id', $ids);
                $users = $this->db->get('aa_users')->result_array();
                $names = array_column($users, 'u_name');
                $r['dep_leader_names'] = implode(', ', $names);
            } else {
                $r['dep_leader_names'] = '';
            }
        }

        return $records;
    }

    public function save_weekly_work($data)
    {
        if (isset($data['w_id']) && $data['w_id'] > 0) {
            $this->db->where('w_id', $data['w_id']);
            $this->db->update('aa_weekly_work', $data);
            return $data['w_id'];
        } else {
            $this->db->insert('aa_weekly_work', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_records($where)
    {
        $this->db->where($where);
        $this->db->delete('aa_weekly_work');
    }

    public function get_assigned_projects_html($leader_id)
    {
        $this->db->select('p_id, p_name,p_number, p_leader');
        $query = $this->db->get('aa_projects');
        $html = '<option value="">-- Select Project --</option>';

        foreach ($query->result_array() as $row) {
            $leaders = explode(',', $row['p_leader']);
            if (in_array($leader_id, $leaders)) {
                $html .= '<option value="' . $row['p_id'] . '">' . $row['p_number'] . " - " . $row['p_name'] . '</option>';
            }
        }
        return $html;
    }

    public function get_assigned_projects($leader_id)
    {
        $this->db->select('p_id, p_name,p_number, p_leader');
        $this->db->from('aa_projects');
        $query = $this->db->get();
        $result = $query->result_array();

        $assigned_projects = [];
        foreach ($result as $row) {
            $leaders = explode(',', $row['p_leader']);
            if (in_array($leader_id, $leaders)) {
                $assigned_projects[] = [
                    'p_id' => $row['p_id'],
                    'p_name' => $row['p_name'], 
                    'p_number' => $row['p_number']
                ];
            }
        }

        return $assigned_projects;
    }

    public function get_project_leaders_html($p_id, $current_leader_id)
    {
        $html = '<option value="">Select Leader</option>';
        $project = $this->db->get_where('aa_projects', ['p_id' => $p_id])->row_array();
        if ($project && !empty($project['p_leader'])) {
            $leader_ids = array_filter(explode(',', $project['p_leader']));
            $leader_ids = array_diff($leader_ids, [$current_leader_id]);
            if (!empty($leader_ids)) {
                $this->db->where_in('u_id', $leader_ids);
                $this->db->where('u_status', 'Active');
                $users = $this->db->get('aa_users')->result_array();
                foreach ($users as $u) {
                    $html .= '<option value="' . $u['u_id'] . '">' . htmlspecialchars($u['u_name']) . '</option>';
                }
            } else {
                $html .= '<option value="">No other leaders found</option>';
            }
        } else {
            $html .= '<option value="">No leaders assigned to this project</option>';
        }

        $this->db->where_in('u_type', ['Master Admin', 'Bim Head']);
        $this->db->where('u_status', 'Active');
        $master_bim_users = $this->db->get('aa_users')->result_array();

        if (!empty($master_bim_users)) {
            $html .= '<optgroup label="Master Admin & BIM Head">';
            foreach ($master_bim_users as $u) {
                $html .= '<option value="' . $u['u_id'] . '">' . htmlspecialchars($u['u_name']) . ' (' . $u['u_type'] . ')</option>';
            }
            $html .= '</optgroup>';
        }

        return $html;
    }
    public function get_weekly_work_by_id($w_id)
    {
        return $this->db
            ->select('*')
            ->from('aa_weekly_work')
            ->where('w_id', $w_id)
            ->get()
            ->row_array();
    }
    public function get_all()
    {
        $this->db->select('
        w.*,
        p.p_name AS project_name,
        u.u_name AS leader_name,
        -- count of employees assigned under this leader
        (SELECT COUNT(*) FROM aa_users WHERE u_leader = w.leader_id) AS team_assigned,
        -- count of projects where leader_id is in p_leader string
        (SELECT COUNT(*) FROM aa_projects WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,
        -- dependency (if Internal, get leader names; else dependency text)
        CASE 
            WHEN w.dependency_type = "Internal" THEN (
                SELECT GROUP_CONCAT(u2.u_name SEPARATOR ", ")
                FROM aa_users u2
                WHERE FIND_IN_SET(u2.u_id, w.dep_leader_ids)
            )
            ELSE w.dependency_text
        END AS dependency_details
    ');
        $this->db->from('aa_weekly_work w');
        $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
        $this->db->join('aa_users u', 'u.u_id = w.leader_id', 'left');
        $this->db->order_by('w.w_id', 'DESC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getWeeklyWork($leader_id = null, $from_date = null, $to_date = null, $page_size = 5, $page_no = 0, $order = 'DESC', $project_Id = null,$filter_status = null)
    {
        //     $this->db->select('
        //     w.*,
        //     p.p_name AS project_name,
        //     u.u_name AS leader_name,
        //     (SELECT COUNT(*) FROM aa_users WHERE u_leader = w.leader_id) AS team_assigned,
        //     (SELECT COUNT(*) FROM aa_projects WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,
        //     CASE 
        //         WHEN w.dependency_type = "Internal" THEN (
        //             SELECT GROUP_CONCAT(u2.u_name SEPARATOR ", ")
        //             FROM aa_users u2
        //             WHERE FIND_IN_SET(u2.u_id, w.dep_leader_ids)
        //         )
        //         ELSE w.dependency_text
        //     END AS dependency_details,
        //     (
        //         SELECT COUNT(*) 
        //         FROM aa_weekly_work_dependency d 
        //         WHERE d.w_id = w.w_id AND d.status != "Completed"
        //     ) AS incomplete_deps
        // ');
        $this->db->select('
    w.*,
    p.p_name AS project_name,
    u.u_name AS leader_name,

    (SELECT COUNT(*) 
     FROM aa_users 
     WHERE u_leader = w.leader_id) AS team_assigned,

    (
    SELECT GROUP_CONCAT(u2.u_name ORDER BY u2.u_name SEPARATOR ", ")
    FROM aa_weekly_work_users wu
    JOIN aa_users u2 ON u2.u_id = wu.u_id
    WHERE wu.weekly_work_id = w.w_id
) AS assigned_users,

    (SELECT COUNT(*) 
     FROM aa_projects 
     WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,

    CASE 
        WHEN w.dependency_type = "Internal" THEN (
            SELECT GROUP_CONCAT(u3.u_name SEPARATOR ", ")
            FROM aa_users u3
            WHERE FIND_IN_SET(u3.u_id, w.dep_leader_ids)
        )
        ELSE w.dependency_text
    END AS dependency_details,

    (
        SELECT COUNT(*) 
        FROM aa_weekly_work_dependency d 
        WHERE d.w_id = w.w_id 
          AND d.status != "Completed"
    ) AS incomplete_deps
');
        $this->db->from('aa_weekly_work w');
        $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
        $this->db->join('aa_users u', 'u.u_id = w.leader_id', 'left');

        if ($leader_id) {
            $this->db->where('w.leader_id', $leader_id);
        }
        if ($project_Id) {
            $this->db->where('w.p_id', $project_Id);
        }

        if ($from_date && $to_date) {
            $from_date = date('Y-m-d', strtotime($from_date));
            $to_date   = date('Y-m-d', strtotime($to_date));

            $this->db->where('w.week_from <=', $to_date);
            $this->db->where('w.week_to >=', $from_date);
        }
        if (!empty($filter_status) && $filter_status != 'All') {
            $this->db->where('w.status', $filter_status);
        }

        $this->db->order_by('u.u_name', $order);
        if ($page_no) {
            $this->db->limit($page_size, $page_no);
            //$this->db->limit($limit);

        }
        return $this->db->get()->result_array();
    }

    public function getWeeklyWorkHome($leader_id = null, $from_date = null, $to_date = null, $page_size = 5, $page_no = 0, $order = 'DESC')
    {
        //     $this->db->select('
        //     w.*,
        //     p.p_name AS project_name,
        //     u.u_name AS leader_name,
        //     (SELECT COUNT(*) FROM aa_users WHERE u_leader = w.leader_id) AS team_assigned,
        //     (SELECT COUNT(*) FROM aa_projects WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,
        //     CASE 
        //         WHEN w.dependency_type = "Internal" THEN (
        //             SELECT GROUP_CONCAT(u2.u_name SEPARATOR ", ")
        //             FROM aa_users u2
        //             WHERE FIND_IN_SET(u2.u_id, w.dep_leader_ids)
        //         )
        //         ELSE w.dependency_text
        //     END AS dependency_details,
        //     (
        //         SELECT COUNT(*) 
        //         FROM aa_weekly_work_dependency d 
        //         WHERE d.w_id = w.w_id AND d.status != "Completed"
        //     ) AS incomplete_deps
        // ');
            $this->db->select('
            w.*,
            p.p_name AS project_name,
            u.u_name AS leader_name,
            (SELECT COUNT(*) FROM aa_users WHERE u_leader = w.leader_id) AS team_assigned,
             (
        SELECT GROUP_CONCAT(u2.u_name ORDER BY u2.u_name SEPARATOR ", ")
        FROM aa_weekly_work_users wu
        JOIN aa_users u2 ON u2.u_id = wu.u_id
        WHERE wu.weekly_work_id = w.w_id
    ) AS assigned_users,

    (SELECT COUNT(*) 
     FROM aa_projects 
     WHERE FIND_IN_SET(w.leader_id, p_leader)) AS no_of_projects,
            CASE 
                WHEN w.dependency_type = "Internal" THEN (
                    SELECT GROUP_CONCAT(u2.u_name SEPARATOR ", ")
                    FROM aa_users u2
                    WHERE FIND_IN_SET(u2.u_id, w.dep_leader_ids)
                )
                ELSE w.dependency_text
            END AS dependency_details,
            (
                SELECT COUNT(*) 
                FROM aa_weekly_work_dependency d 
                WHERE d.w_id = w.w_id AND d.status != "Completed"
            ) AS incomplete_deps
        ');
        $this->db->from('aa_weekly_work w');
        $this->db->join('aa_projects p', 'p.p_id = w.p_id', 'left');
        $this->db->join('aa_users u', 'u.u_id = w.leader_id', 'left');

        if ($leader_id) {
            $this->db->where('w.leader_id', $leader_id);
        }
        if ($from_date && $to_date) {
            $from_date = date('Y-m-d', strtotime($from_date));
            $to_date   = date('Y-m-d', strtotime($to_date));

            $this->db->where('w.week_from <=', $to_date);
            $this->db->where('w.week_to >=', $from_date);
        }

        $this->db->order_by('u.u_name,w.created_at', $order);
        $this->db->limit(5);

        return $this->db->get()->result_array();
    }
}
