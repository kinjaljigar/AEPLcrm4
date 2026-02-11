<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'aa_users';
    protected $primaryKey = 'u_id';
    protected $allowedFields = [
        'u_name',
        'u_username',
        'u_password',
        'u_email',
        'u_mobile',
        'u_type',
        'u_status',
        'u_leader',
        'u_department',
        'u_designation',
        'u_salary',
        'u_joining_date',
        'u_leaving_date',
        'u_created',
        'u_modified',
        'is_web_logged_in'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'u_created';
    protected $updatedField = 'u_modified';

    protected $validationRules = [
        'u_name' => 'required|min_length[3]|max_length[100]',
        'u_username' => 'required|is_unique[aa_users.u_username,u_id,{u_id}]',
        'u_email' => 'permit_empty|valid_email',
        'u_type' => 'required|in_list[Super Admin,Master Admin,Bim Head,Project Leader,TaskCoordinator,Employee]'
    ];

    protected $validationMessages = [
        'u_username' => [
            'is_unique' => 'Username already exists.'
        ]
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before insert/update
     *
     * @param array $data
     * @return array
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['u_password']) && !empty($data['data']['u_password'])) {
            $data['data']['u_password'] = md5($data['data']['u_password']);
        }

        return $data;
    }

    /**
     * Save user (insert or update)
     *
     * @param array $data
     * @return int User ID
     * @throws \Exception
     */
    public function saveUser(array $data): int
    {
        if (isset($data['u_id']) && $data['u_id'] > 0) {
            $u_id = $data['u_id'];
            unset($data['u_id']);

            // Check if username exists for other users
            $existing = $this->where('u_username', $data['u_username'])
                             ->where('u_id !=', $u_id)
                             ->first();

            if ($existing) {
                throw new \Exception('Username already exists.');
            }

            $this->update($u_id, $data);
            return $u_id;
        } else {
            // Check if username exists
            $existing = $this->where('u_username', $data['u_username'])->first();

            if ($existing) {
                throw new \Exception('Username already exists.');
            }

            $u_id = $this->insert($data);
            return $u_id;
        }
    }

    /**
     * Get records with advanced filtering
     *
     * @param array $params
     * @return array|int
     */
    public function getRecords(array $params = [])
    {
        $params['sort_by'] = $params['sort_by'] ?? 'u_name';
        $params['sort_type'] = $params['sort_type'] ?? 'ASC';
        $params['page_size'] = $params['page_size'] ?? 0;
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['select_list'] = $params['select_list'] ?? '*';
        $params['conditions'] = $params['conditions'] ?? [];
        $params['or_conditions'] = $params['or_conditions'] ?? [];
        $params['result_type'] = $params['result_type'] ?? 'all_records';
        $params['newcondition'] = $params['newcondition'] ?? '';

        $builder = $this->db->table($this->table);
        $builder->select($params['select_list']);

        // Apply new condition (for complex WHERE clauses)
        if (!empty($params['newcondition'])) {
            if (is_array($params['newcondition'])) {
                $builder->where($params['newcondition']);
            } else {
                $builder->where($params['newcondition'], null, false);
            }
        }

        // Apply conditions
        foreach ($params['conditions'] as $value) {
            $builder->where($value);
        }

        // Apply OR conditions
        foreach ($params['or_conditions'] as $value) {
            $builder->orWhere($value);
        }

        // Apply sorting
        $builder->orderBy($params['sort_by'], $params['sort_type']);

        // Apply pagination
        if ($params['result_type'] != 'count_records' && $params['page_size'] > 0) {
            $offset = ($params['page_no'] - 1) * $params['page_size'];
            $builder->limit($params['page_size'], $offset);
        }

        // Return results based on type
        if ($params['result_type'] == 'all_records') {
            return $builder->get()->getResultArray();
        } else {
            return $builder->countAllResults();
        }
    }

    /**
     * Get all leaders
     *
     * @param string|null $status
     * @return array
     */
    public function getAllLeaders(?string $status = null): array
    {
        $builder = $this->where('u_type', 'Project Leader');

        if ($status) {
            $builder->where('u_status', $status);
        }

        return $builder->orderBy('u_name', 'ASC')->findAll();
    }

    /**
     * Get user by username
     *
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername(string $username): ?array
    {
        return $this->where('u_username', $username)->first();
    }

    /**
     * Get user by ID
     *
     * @param int $u_id
     * @return array|null
     */
    public function getUserById(int $u_id): ?array
    {
        return $this->find($u_id);
    }

    /**
     * Verify user login credentials
     *
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function verifyLogin(string $username, string $password): ?array
    {
        $hashedPassword = md5($password);

        return $this->where('u_username', $username)
                    ->where('u_password', $hashedPassword)
                    ->where('u_status', 'Active')
                    ->first();
    }

    /**
     * Update user login status
     *
     * @param int $u_id
     * @param int $status
     * @return bool
     */
    public function updateLoginStatus(int $u_id, int $status): bool
    {
        return $this->update($u_id, ['is_web_logged_in' => $status]);
    }

    /**
     * Get employees by leader
     *
     * @param int $leader_id
     * @return array
     */
    public function getEmployeesByLeader(int $leader_id): array
    {
        return $this->where('u_leader', $leader_id)
                    ->where('u_status', 'Active')
                    ->orderBy('u_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get all active employees
     *
     * @return array
     */
    public function getActiveEmployees(): array
    {
        return $this->whereIn('u_type', ['Project Leader', 'Employee'])
                    ->where('u_status', 'Active')
                    ->orderBy('u_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get users by department
     *
     * @param string $department
     * @return array
     */
    public function getUsersByDepartment(string $department): array
    {
        return $this->where('u_department', $department)
                    ->where('u_status', 'Active')
                    ->orderBy('u_name', 'ASC')
                    ->findAll();
    }
}
