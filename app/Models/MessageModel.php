<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'aa_messages';
    protected $primaryKey = 'm_id';
    protected $allowedFields = [
        'm_from_u_id',
        'm_to_u_id',
        'm_message',
        'm_created',
        'mu_read',
        'mu_read_on'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'm_created';

    /**
     * Get records with advanced filtering
     *
     * @param array $params
     * @return array|int
     */
    public function getRecords(array $params = [])
    {
        $params['sort_by'] = $params['sort_by'] ?? 'm_created';
        $params['sort_type'] = $params['sort_type'] ?? 'DESC';
        $params['page_size'] = $params['page_size'] ?? 0;
        $params['page_no'] = $params['page_no'] ?? 1;
        $params['select_list'] = $params['select_list'] ?? '*';
        $params['conditions'] = $params['conditions'] ?? [];
        $params['u_id'] = $params['u_id'] ?? null;

        $builder = $this->db->table($this->table);
        $builder->select($params['select_list']);

        // Filter by user ID if provided
        if ($params['u_id']) {
            $builder->where('m_to_u_id', $params['u_id']);
        }

        // Apply conditions
        foreach ($params['conditions'] as $value) {
            $builder->where($value);
        }

        // Apply sorting
        $builder->orderBy($params['sort_by'], $params['sort_type']);

        // Apply pagination
        if ($params['page_size'] > 0) {
            $offset = ($params['page_no'] - 1) * $params['page_size'];
            $builder->limit($params['page_size'], $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get unread message count for user
     *
     * @param int $u_id
     * @return int
     */
    public function getUnreadCount(int $u_id): int
    {
        return $this->where('m_to_u_id', $u_id)
                    ->where('mu_read', 0)
                    ->countAllResults();
    }

    /**
     * Mark message as read
     *
     * @param int $m_id
     * @return bool
     */
    public function markAsRead(int $m_id): bool
    {
        return $this->update($m_id, [
            'mu_read' => 1,
            'mu_read_on' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send message
     *
     * @param array $data
     * @return int Message ID
     */
    public function sendMessage(array $data): int
    {
        $data['m_created'] = date('Y-m-d H:i:s');
        $data['mu_read'] = 0;

        return $this->insert($data);
    }

    /**
     * Get messages between two users
     *
     * @param int $from_u_id
     * @param int $to_u_id
     * @param int $limit
     * @return array
     */
    public function getConversation(int $from_u_id, int $to_u_id, int $limit = 50): array
    {
        return $this->groupStart()
                        ->where('m_from_u_id', $from_u_id)
                        ->where('m_to_u_id', $to_u_id)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('m_from_u_id', $to_u_id)
                        ->where('m_to_u_id', $from_u_id)
                    ->groupEnd()
                    ->orderBy('m_created', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
