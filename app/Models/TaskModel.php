<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'aa_tasks';
    protected $primaryKey = 't_id';
    protected $allowedFields = [
        't_name', 't_description', 't_p_id', 't_parent', 't_priority',
        't_status', 't_start_date', 't_end_date', 't_created', 't_modified'
    ];

    protected $useTimestamps = false;

    public function getRecords(array $params = [])
    {
        $params['sort_by'] = $params['sort_by'] ?? 't_created';
        $params['sort_type'] = $params['sort_type'] ?? 'DESC';
        $params['conditions'] = $params['conditions'] ?? [];

        $builder = $this->db->table($this->table);
        $builder->select('*');

        foreach ($params['conditions'] as $value) {
            $builder->where($value);
        }

        $builder->orderBy($params['sort_by'], $params['sort_type']);

        return $builder->get()->getResultArray();
    }
}
