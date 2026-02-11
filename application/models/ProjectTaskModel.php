<?php
class ProjectTaskModel extends CI_Model
{
    protected $table = 'aa_project_tasks';

    public function __construct()
    {
        parent::__construct();
    }

    public function GetAllTasks($u_id, $searchText = '', $limit = 1000, $offset = 0, $u_type = '', $data = 'upcoming')
    {
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = date('Y-m-d H:i:s');

        $this->db->select([
            'aa_project_tasks.*',
            'task_creator.u_name as creator_name',
            'GROUP_CONCAT(DISTINCT aa_project_task_users.u_id) as user_ids',
            'GROUP_CONCAT(DISTINCT aa_users.u_name) as user_names',
            'GROUP_CONCAT(DISTINCT aa_users.u_type) as user_types',
            '(CASE WHEN aa_project_tasks.u_id = ' . (int)$u_id . ' THEN 1 ELSE 0 END) as edit',
            '(CASE WHEN aa_project_tasks.u_id = ' . (int)$u_id . ' THEN 1 ELSE 0 END) as `delete`'
        ]);

        $this->db->from('aa_project_tasks');
        $this->db->join('aa_project_task_users', 'aa_project_tasks.id = aa_project_task_users.task_id', 'left');
        $this->db->join('aa_users', 'aa_project_task_users.u_id = aa_users.u_id', 'left');
        $this->db->join('aa_users as task_creator', 'aa_project_tasks.u_id = task_creator.u_id', 'left');

        if ($data === 'upcoming') {
            $this->db->where("CONCAT(aa_project_tasks.date, ' ', aa_project_tasks.time) >=", $currentDateTime);
        } elseif ($data === 'archived') {
            $this->db->where("CONCAT(aa_project_tasks.date, ' ', aa_project_tasks.time) <", $currentDateTime);
        }

        if (in_array($u_type, ['Super Admin', 'Master Admin', 'Bim Head', 'Project Leader'])) {
            $this->db->group_start();
            $this->db->where('aa_project_tasks.u_id', $u_id);
            $this->db->or_where('aa_project_task_users.u_id', $u_id);
            $this->db->group_end();
        } else {
            $this->db->where('aa_project_task_users.u_id', $u_id);
        }

        if (!empty($searchText)) {
            $this->db->group_start();
            $this->db->like('aa_project_tasks.title', $searchText);
            $this->db->or_like('aa_project_tasks.description', $searchText);
            $this->db->or_like('aa_project_tasks.date', $searchText);
            $this->db->or_like('aa_project_tasks.time', $searchText);
            $this->db->or_like('task_creator.u_name', $searchText);
            $this->db->group_end();
        }

        $this->db->group_by('aa_project_tasks.id');
        $this->db->order_by('aa_project_tasks.date', 'DESC');
        $this->db->order_by('aa_project_tasks.time', 'DESC');

        $query = $this->db->get('', $limit, $offset);
        return $query->result_array();
    }

    public function CheckTaskexist($TaskId, $u_type = null)
    {
        return $this->db
            ->select('aa_project_tasks.*, aa_projects.p_name, aa_projects.p_number, task_creator.u_name as creator_name,
            GROUP_CONCAT(aa_project_task_users.u_id) as user_ids, 
            GROUP_CONCAT(aa_users.u_name) as user_names,
             GROUP_CONCAT(aa_project_task_users.task_completed) as completedtasks,
            GROUP_CONCAT(IFNULL(aa_project_task_users.task_reason, "")) as completed_reasons,
            GROUP_CONCAT(IFNULL(aa_project_task_users.completed_at, "")) as completed_at,
            GROUP_CONCAT(aa_users.u_type) as user_types')
            ->from('aa_project_tasks')
            ->join('aa_project_task_users', 'aa_project_tasks.id = aa_project_task_users.task_id', 'left')
            ->join('aa_users', 'aa_project_task_users.u_id = aa_users.u_id', 'left')
            ->join('aa_projects', 'aa_project_tasks.project_id = aa_projects.p_id', 'left')
            ->join('aa_users as task_creator', 'aa_project_tasks.u_id = task_creator.u_id', 'left')
            ->where('aa_project_tasks.id', $TaskId)
            ->group_by('aa_project_tasks.id')
            ->get()
            ->row_array(); // or ->row() if you prefer object
    }

    public function updateTask($task_id, $data, $u_id)
    {
        $this->db->where('task_id', $task_id);
        $this->db->where('u_id', $u_id);
        return $this->db->update('aa_project_task_users', $data);
    }
}
