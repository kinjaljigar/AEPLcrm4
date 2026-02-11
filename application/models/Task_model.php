<?php

class Task_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        if (isset($data['t_id']) && $data['t_id'] > 0) {
            /*
            / [PEDNING] Think for checks
            */
            $t_id = $data['t_id'];
            unset($data['t_id']);
            $this->db->where('t_id', $t_id)->update('aa_tasks', $data);
            $this->adjustTaskHours($t_id, "save");
        } else {
            $this->db->insert('aa_tasks', $data);
            $t_id = $this->db->insert_id();
            $this->adjustTaskHours($t_id, "save");
        }
        return $t_id;
    }
    public function delete_records($data)
    {
        $sql = "SELECT at_id FROM aa_attendance WHERE at_t_id = '{$data['t_id']}'";
        $query = $this->db->query($sql);
        $records = $query->result_array();
        if (!empty($records)) throw new Exception('Cannot delete Task, Attendance is added for this task.');

        $sql = "SELECT * FROM aa_task2user WHERE tu_t_id = '{$data['t_id']}' AND tu_removed = 'No'";
        $query = $this->db->query($sql);
        $records = $query->result_array();
        if (!empty($records)) throw new Exception('Cannot delete Task, Task was assigned to user.');

        $sql = "SELECT * FROM aa_tasks WHERE t_parent = '{$data['t_id']}'";
        $query = $this->db->query($sql);
        $records = $query->result_array();
        if (!empty($records)) throw new Exception('Cannot delete Task, Task has sub tasks.');

        $this->adjustTaskHours($data['t_id'], "delete");
        $this->db->delete('aa_tasks', $data);
        $this->db->delete('aa_task2user', array('tu_t_id' => $data['t_id']));
        // [PENDING] Task Files need to deleted. TAsk messages need to be delete.
    }
    public function adjustTaskHours($task_id, $type = "save")
    {
        $records = $this->db->query("SELECT t_id, t_parent FROM aa_tasks WHERE t_id = '{$task_id}'")->result_array();
        if (!empty($records[0])) {
            $records = $records[0];
            if ($records['t_parent'] <= 0) // This is main task
            {
                if ($type == "delete") return;
                else {
                    $records = $this->db->query("SELECT t_parent, SUM(t_hours) as total FROM aa_tasks WHERE t_parent = '{$task_id}'")->result_array();
                    if (!empty($records[0]['t_parent'])) {
                        $this->db->where('t_id', $task_id)->update('aa_tasks', array('t_hours' => $records[0]['total']));
                    }
                }
            } else // This is sub task
            {
                $task_id = $records['t_parent'];

                if ($type == "delete")
                    $records = $this->db->query("SELECT t_parent, SUM(t_hours) as total FROM aa_tasks WHERE t_parent = '{$task_id}' AND t_id <> '{$records['t_id']}'")->result_array();
                else
                    $records = $this->db->query("SELECT t_parent, SUM(t_hours) as total FROM aa_tasks WHERE t_parent = '{$task_id}'")->result_array();

                if (!empty($records[0]['t_parent'])) {
                    $this->db->where('t_id', $task_id)->update('aa_tasks', array('t_hours' => $records[0]['total']));
                }
            }
        }
    }
    public function get_records($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 't_title';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'T.*, U.u_name, P.p_name';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        // $this->db->select($params['select_list'])
        //     ->from('aa_tasks T')
        //     ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
        //     ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
        //     ->order_by($params['sort_by'], $params['sort_type']);
        $this->db->select($params['select_list'])
            ->from('aa_tasks T')
            ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
            ->join('aa_users U', 'U.u_id = T.t_u_id', 'left');
        if (!empty($params['user_id'])) {
            $this->db->join('aa_task2user TU', 'TU.tu_t_id = T.t_id AND TU.tu_removed = "No"', 'inner');
            $this->db->where('TU.tu_u_id', $params['user_id']);
        }
        $this->db->order_by($params['sort_by'], $params['sort_type']);

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
    public function get_records_projects($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'P.p_name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'P.p_name , P.*';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $params['newcondition'] = isset($params['newcondition']) ? $params['newcondition'] : '';



        $this->db->select($params['select_list'])
            ->from('aa_tasks T')
            ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
            ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
            ->distinct('P.p_name')
            ->order_by($params['sort_by'], $params['sort_type']);

        if ($params['newcondition'] != '') {
            $this->db->where($params['newcondition']);
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
    public function get_project_tasks($projectid)
    {
        $this->db->select('T.*, U.u_name, P.p_name')
            ->from('aa_tasks T')
            ->join('aa_projects P', 'P.p_id = T.t_p_id', 'left')
            ->join('aa_users U', 'U.u_id = T.t_u_id', 'left')
            ->where('T.t_p_id', $projectid)
            ->order_by('t_title', 'ASC');
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }
    public function get_records_by_assignee($params)
    {
        $this->db->select("T.*")
            ->from('aa_tasks T')
            ->join('aa_task2user TU', 'TU.tu_t_id = T.t_id', 'left')
            ->order_by("tu_removed", "DESC")
            ->order_by("t_title", "ASC");
        foreach ($params['conditions'] as $value) {
            $this->db->where($value);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }
    public function assign($tu_t_id, $t_p_id, $t_assign)
    {
        $data = array('tu_removed' => 'No', 'tu_datetime' => date("Y-m-d H:i:s"));
        $data2 = array('tu_removed' => 'No', 'tu_datetime' => date("Y-m-d H:i:s"));
        if (is_array($t_assign)) {
            foreach ($t_assign as $tu_u_id) {
                $this->db->where(array('tu_u_id' => $tu_u_id, 'tu_t_id' => $tu_t_id))->update('aa_task2user', $data);
                $data2['tu_u_id'] = $tu_u_id;
                $data2['tu_t_id'] = $tu_t_id;
                $data2['tu_p_id'] = $t_p_id;
                $this->db->insert('aa_task2user', $data2);
            }
        }
    }
    public function get_assigns($t_id, $t_p_id = 0, $all = false, $txt_employee = null)
    {

        $this->db->select("TU.*, U.u_name , U.u_id")
            ->from("aa_task2user TU")
            ->join("aa_users U", "TU.tu_u_id = U.u_id")
            ->where(array('tu_t_id' => $t_id));
        if ($all == false) {
            $this->db->where('tu_removed', 'No');
        }
        if ($txt_employee != null) {
            $this->db->where(array('tu_u_id' => $txt_employee));
        }
        return $this->db->get()->result_array();
    }
    public function assign_update($t_id, $t_assign, $act_sub, $t_p_id)
    {
        if ($act_sub == "remove") {
            $this->db->where(array('tu_t_id' => $t_id, 'tu_u_id' => $t_assign))->update('aa_task2user', array('tu_removed' => 'Yes'));
        } else if ($act_sub == "add") {
            $this->db->select("*")
                ->from("aa_task2user")
                ->where(array('tu_t_id' => $t_id, 'tu_u_id' => $t_assign));
            $results = $this->db->get()->result_array();
            if (count($results)) {
                $this->db->where(array('tu_t_id' => $t_id, 'tu_u_id' => $t_assign))->update('aa_task2user', array('tu_removed' => 'No'));
            } else {
                $data2 = array('tu_removed' => 'No', 'tu_datetime' => date("Y-m-d H:i:s"));
                $data2['tu_u_id'] = $t_assign;
                $data2['tu_t_id'] = $t_id;
                $data2['tu_p_id'] = $t_p_id;
                $this->db->insert('aa_task2user', $data2);
            }
        }
    }
    public function save_file($data)
    {
        $this->db->insert('aa_task_files', $data);
        return $this->db->insert_id();
    }
    public function get_files($data)
    {
        $this->db->select("*")->from("aa_task_files")->where($data);
        return $this->db->get()->result_array();
    }
    public function remove_files($data)
    {
        $this->db->where($data)->delete("aa_task_files");
    }
    public function save_taskmessage($data)
    {
        $this->db->insert('aa_task_message', $data);
        return $this->db->insert_id();
    }
    public function update_taskmessage($tm_id, $data)
    {
        $this->db->where(array('tm_id' => $tm_id))->update("aa_task_message", $data);
    }
    public function get_task_message($t_id, $offset = 0, $limit = -99)
    {
        $this->db->select("tm.*, u.u_name")
            ->from("aa_task_message tm")
            ->join("aa_users u", "u.u_id = tm.tm_u_id")
            ->where('tm_t_id', $t_id);
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        if ($limit == -1) $limit = 1;
        if ($limit > 0) {
            return $this->db->get()->num_rows();
        } else {
            return $this->db->get()->result_array();
        }
    }
    public function get_task_message_single($tm_id)
    {
        $this->db->select("tm.*")
            ->from("aa_task_message tm")
            ->where('tm_id', $tm_id);
        return $this->db->get()->result_array();
    }
    public function get_records_for_task($params)
    {
        if ($params['user_id'] != '')
            $this->db->select("u_name, A.*")
                ->from('aa_attendance A')
                ->join('aa_users U', 'U.u_id = A.at_u_id', 'left')
                ->where(array("at_t_id" => $params['task_id'], 'u_id' => $params['user_id']))
                ->order_by("at_date", "DESC");
        else
            $this->db->select("u_name, A.*")
                ->from('aa_attendance A')
                ->join('aa_users U', 'U.u_id = A.at_u_id', 'left')
                ->where(array("at_t_id" => $params['task_id']))
                ->order_by("at_date", "DESC");


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
    public function get_report_by_project($p_status, $txt_search = null)
    {
        if ($txt_search == '')
            $sql = "SELECT p_id, p_name, SUM(T.t_hours) as t_hours , SUM(T.t_hours_planned) as t_hours_planned , SUM(T.t_hours_total) as t_hours_total FROM aa_projects P LEFT OUTER JOIN aa_tasks T ON P.p_id = T.t_p_id WHERE p_status = '{$p_status}' GROUP BY p_id";
        else
            $sql = "SELECT p_id, p_name, SUM(T.t_hours) as t_hours , SUM(T.t_hours_planned) as t_hours_planned , SUM(T.t_hours_total) as t_hours_total FROM aa_projects P LEFT OUTER JOIN aa_tasks T ON P.p_id = T.t_p_id WHERE p_status = '{$p_status}' and p_name like '%$txt_search%' GROUP BY p_id";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_report_by_project_detail($p_id)
    {
        //      $sql = "SELECT u_id, u_name, SUM(T.t_hours) as t_hours, SUM(T.t_hours_total) as t_hours_total FROM aa_users U LEFT OUTER JOIN aa_tasks T ON U.u_id = T.t_u_id WHERE t_p_id = '{$p_id}' GROUP BY u_id";
        //echo $sql = "SELECT u_id, u_name, SUM(T.t_hours) as t_hours, SUM(T.t_hours_total) as t_hours_total FROM aa_users U LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id LEFT OUTER JOIN aa_tasks T ON TU.tu_t_id = T.t_id WHERE t_p_id = '{$p_id}' GROUP BY u_id";
        $sql = "SELECT total_hrs as t_hours , u_id , u_name  FROM (SELECT (SUM((at_end - at_start) / 60)) as total_hrs,U.u_id as u_id,U.u_username as u_name FROM aa_attendance A INNER JOIN aa_users US  ON A.at_u_id = US.u_id , aa_users as U WHERE at_p_id = '{$p_id}' and U.u_id = US.u_id group by U.u_id) as FinnalDB";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_attendance_for_task()
    {
        $sql = "SELECT at_id FROM aa_attendance WHERE at_t_id = '{$data['t_id']}'";
        $query = $this->db->query($sql);
    }
    public function getmaintaskname($t_id)
    {
        $sql = "SELECT t_title FROM aa_tasks WHERE t_id = '{$t_id}'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
