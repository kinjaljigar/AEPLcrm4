<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table = 'aa_projects';
    protected $primaryKey = 'p_id';
    protected $allowedFields = [
        'p_name', 'p_number', 'p_leader', 'p_status', 'p_cat', 'p_start_date',
        'p_end_date', 'p_description', 'p_created', 'p_modified'
    ];

    protected $useTimestamps = false;

    public function getRecords(array $params = [])
    {
        $params['sort_by'] = $params['sort_by'] ?? 'p_name';
        $params['sort_type'] = $params['sort_type'] ?? 'ASC';
        $params['page_size'] = $params['page_size'] ?? 0;
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['select_list'] = $params['select_list'] ?? '*';
        $params['conditions'] = $params['conditions'] ?? [];
        $params['newcondition'] = $params['newcondition'] ?? '';

        $builder = $this->db->table($this->table);
        $builder->select($params['select_list']);

        if (!empty($params['newcondition'])) {
            if (is_array($params['newcondition'])) {
                $builder->where($params['newcondition']);
            } else {
                $builder->where($params['newcondition'], null, false);
            }
        }

        foreach ($params['conditions'] as $value) {
            $builder->where($value);
        }

        $builder->orderBy($params['sort_by'], $params['sort_type']);

        if ($params['page_size'] > 0) {
            $offset = ($params['page_no'] - 1) * $params['page_size'];
            $builder->limit($params['page_size'], $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getProjectsByUser(int $u_id)
    {
        $db = \Config\Database::connect();
        $query = "SELECT DISTINCT(p_id), P.p_name, P.p_number, u_id
                  FROM aa_users U
                  LEFT OUTER JOIN aa_task2user TU ON U.u_id = TU.tu_u_id
                  LEFT OUTER JOIN aa_projects P ON TU.tu_p_id = P.p_id
                  WHERE TU.tu_removed = 'No'
                  AND (P.p_status = 'Active' OR P.p_status = 'New')
                  AND u_id = '{$u_id}'";

        return $db->query($query)->getResultArray();
    }
}
