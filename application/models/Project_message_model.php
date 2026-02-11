<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_message_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    public function save($data = array())
    {
        if (isset($data['pm_id']) && intval($data['pm_id']) > 0) {
            $pm_id = intval($data['pm_id']);
            unset($data['pm_id']);
            $this->db->where('pm_id', $pm_id)->update('aa_project_messages', $data);
            return $pm_id;
        } else {
            $this->db->insert('aa_project_messages', $data);
            return $this->db->insert_id();
        }
    }


    public function get_recipients_by_project($p_id = null)
    {
        $uids = array();

        if (!empty($p_id)) {
            
            $proj = $this->db->select('p_leader, p_name')->where('p_id', $p_id)->get('aa_projects')->row_array();
            if (!empty($proj)) {
                if (!empty($proj['p_leader'])) {
                    $leaders = preg_split('/[,\s]+/', $proj['p_leader']);
                    foreach ($leaders as $l) {
                        $l = trim($l);
                        if ($l !== '') $uids[] = intval($l);
                    }
                }
                if (!empty($proj['p_employee'])) {
                    // handle comma separated values
                    $parts = preg_split('/[,\s]+/', $proj['p_employee']);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') $uids[] = intval($p);
                    }
                }
            }
        } else {
             $all_emps = $this->db->select('u_id')->where_in('u_type', array('Employee','Team Leader', 'Bim Head', 'Master Admin', 'Super Admin'))->get('aa_users')->result_array();
            foreach ($all_emps as $e) $uids[] = intval($e['u_id']);
        }
        
        // $core_users = $this->db->select('u_id')->where('u_type', 'Bim Head')->get('aa_users')->result_array();
        // foreach ($core_users as $c) $uids[] = intval($c['u_id']);

        // $master_users = $this->db->select('u_id')->where('u_type', 'Master Admin')->get('aa_users')->result_array();
        // foreach ($master_users as $m) $uids[] = intval($m['u_id']);

        //$uids = array_filter(array_unique($uids));
        return array_values(array_unique(array_filter($uids)));
        //return $uids;
    }

    public function add_recipients($pm_id, $uids = array())
    {
        if (empty($uids) || empty($pm_id)) return;
        $now = date('Y-m-d H:i:s');
        foreach ($uids as $uid) {
            $uid = intval($uid);
            if ($uid <= 0) continue;
           
            $exists = $this->db->where('pmu_pm_id', $pm_id)->where('pmu_u_id', $uid)->count_all_results('aa_project_message_users');
            if ($exists == 0) {
                $this->db->insert('aa_project_message_users', [
                    'pmu_pm_id' => $pm_id,
                    'pmu_u_id' => $uid,
                    'pmu_read' => 0,
                    'pmu_added_at' => $now
                ]);
            }
        }
    }

    public function add_reply($pm_id, $u_id, $text)
    {
        $this->db->insert('aa_project_message_replies', [
            'pmr_pm_id' => $pm_id,
            'pmr_u_id' => $u_id,
            'pmr_text' => $text,
            'pmr_datetime' => date('Y-m-d H:i:s')
        ]);
        // mark others as unread
        $this->db->where('pmu_pm_id', $pm_id)->where('pmu_u_id !=', $u_id)->update('aa_project_message_users', ['pmu_read' => 0]);
        return $this->db->insert_id();
    }

    public function get_thread($pm_id)
    {
        $message = $this->db->where('pm_id', $pm_id)->get('aa_project_messages')->row_array();
        if (empty($message)) return array();

        $replies = $this->db->select('r.*, u.u_name as user_name, u.u_type')
            ->from('aa_project_message_replies r')
            ->join('aa_users u', 'u.u_id = r.pmr_u_id', 'left')
            ->where('r.pmr_pm_id', $pm_id)
            ->order_by('r.pmr_datetime', 'ASC')
            ->get()->result_array();

        $participants = $this->db->select('pmu_u_id, pmu_read, u.u_name, u.u_type')
            ->from('aa_project_message_users pmu')
            ->join('aa_users u', 'u.u_id = pmu.pmu_u_id', 'left')
            ->where('pmu.pmu_pm_id', $pm_id)
            ->get()->result_array();

        // Add project name if available
        $project = null;
        if (!empty($message['pm_p_id'])) {
            $project = $this->db->select('p_name')->where('p_id', $message['pm_p_id'])->get('aa_projects')->row_array();
            if (!empty($project)) $message['p_name'] = $project['p_name'];
        } else {
            $message['p_name'] = 'General';
        }

        return [
            'message' => $message,
            'replies' => $replies,
            'participants' => $participants
        ];
    }

    public function delete($pm_id, $current_uid = null)
    {
        if (empty($pm_id)) throw new Exception("Invalid message id");

        // check replies
        $cnt = $this->db->where('pmr_pm_id', $pm_id)->count_all_results('aa_project_message_replies');
        if ($cnt > 0) throw new Exception("Cannot delete: replies exist.");

        
        if (!empty($current_uid)) {
            $exists = $this->db->where('pm_id', $pm_id)->where('pm_created_by', $current_uid)->count_all_results('aa_project_messages');
            if ($exists == 0) throw new Exception("You are not authorized to delete this message.");
        }

                $this->db->where('pm_id', $pm_id)->delete('aa_project_messages');
        return true;
    }

    public function get_list($params = array())
    {
        $this->db->select("
        m.*,
        p.p_name,
        u.u_name AS created_by_name,
        COUNT(r.pmr_id) AS reply_count
    ")
            ->from("aa_project_messages m")
            ->join("aa_users u", "u.u_id = m.pm_created_by", "left")
            ->join("aa_projects p", "p.p_id = m.pm_p_id", "left")
            ->join("aa_project_message_replies r", "r.pmr_pm_id = m.pm_id ", "left")
            ->where("m.pm_deleted", 0);

        if (!empty($params['is_leader']) && !empty($params['leader_id'])) {
            //$this->db->where("p.p_leader", $params['leader_id']);
            $this->db->where("FIND_IN_SET(" . (int)$params['leader_id'] . ", p_leader) >", 0, false); // Use raw SQL
        }
        if (!empty($params['u_id'])) {
            $this->db->join("aa_project_message_users pmu", "pmu.pmu_pm_id = m.pm_id", "inner");
            $this->db->where("pmu.pmu_u_id", $params['u_id']);
        }

        if (!empty($params['project_id'])) {
            $this->db->where("m.pm_p_id", $params['project_id']);
        }
        if (!empty($params['filter_date'])) {
            $this->db->where("DATE(m.pm_datetime)", $params['filter_date']);
        }
        if (!empty($params['filter_discipline'])) {
            $this->db->like("m.pm_descipline", $params['filter_discipline']);
        }
        $this->db->group_by("m.pm_id");
        $this->db->order_by("m.pm_datetime", "DESC");

        if (!empty($params['limit'])) {
            $this->db->limit($params['limit'], $params['offset']);
            return $this->db->get()->result_array();
        } else {
            return $this->db->get()->num_rows();
        }
    }
}
