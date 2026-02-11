<?php

class Timesheet_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data, $utype = '')
    {
        if ($utype != 'Bim Head') {
            if (!$this->validateDate($data['at_date'])) {
                throw new Exception('You can only add/edit records for today and 7 days behind.');
            }
        }
        if (!$this->validateTimeLimit($data['at_start'], $data['at_end'])) {
            throw new Exception('You can choose TO time is more then FROM time.');
        }
        if (isset($data['at_id']) && $data['at_id'] > 0) {
            $at_id = $data['at_id'];
            $at_u_id = $data['at_u_id'];
            unset($data['at_id']);
            unset($data['at_u_id']);

            $this->db->select("*")
                ->from('aa_attendance')
                ->where(array('at_id' => $at_id, 'at_u_id' => $at_u_id));
            $query = $this->db->get();

            if ($results = $query->result_array()) {
                $results = $results[0];
                if (!$this->validateDate($results['at_date'])) {
                    throw new Exception('You can only add/edit records for today and 7 days behind.');
                } else {
                    $this->db->where('at_id', $at_id)->update('aa_attendance', $data);
                    $this->update_task_hours($results['at_t_id']);
                }
            } else {
                throw new Exception('Record does not exists.');
            }
        } else {

            $at_id = $this->db->insert('aa_attendance', $data);
            $this->update_task_hours($data['at_t_id']);
        }
        return $at_id;
    }
    public function delete($data)
    {
        $this->db->select("*")->from('aa_attendance')->where($data);
        $query = $this->db->get();
        if ($results = $query->result_array()) {
            $results = $results[0];
            if (!$this->validateDate($results['at_date'])) {
                throw new Exception('You can only delete records for today and 7 days behind.'); // 2 replace with 7
            } else {
                $this->db->delete('aa_attendance', $data);
                $this->update_task_hours($results['at_t_id']);
            }
        } else {
            throw new Exception('Record does not exists.');
        }
    }

    public function get_records($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'p_name';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'ASC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'A.*, P.p_name, T.t_title';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'

        $this->db->select($params['select_list'])
            ->from('aa_attendance A')
            ->join('aa_projects P', 'P.p_id = A.at_p_id', 'left')
            ->join('aa_tasks T', 'T.t_id = A.at_t_id', 'left')
            ->order_by($params['sort_by'], $params['sort_type']);

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
    public function validateDate($in_date)
    {
        $at_days_back = $this->config->item('at_days_back');
        $in_date = new DateTime($in_date);
        $startdate = new DateTime("today");
        $enddate = new DateTime("today");
        $startdate->modify('-' . $at_days_back . ' day');

        if ($in_date >= $startdate && $in_date <= $enddate) return true;
        else return false;
    }
    public function validateTimeLimit($startime, $endtime)
    {
        if ($startime <= $endtime) return true;
        else return false;
    }
    public function get_all_empl_hrs($rs, $re, $at_u_id)
    {
        $sql = "SELECT u_name, u_id, SUM(whours) as work_hours  FROM (SELECT u_name, u_id, ((at_end - at_start) / 60) as whours FROM aa_users U INNER JOIN aa_attendance ATT ON U.u_id = ATT.at_u_id AND ATT.at_date BETWEEN '{$rs}' AND '{$re}' where u_id = '{$at_u_id}') AS DB2 GROUP BY u_id";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_report_all($rs, $re, $txt_search = null)
    {
        if ($txt_search != '')
            $sql = "SELECT u_name, u_id, SUM(whours) as work_hours  FROM (SELECT u_name, u_id, ((at_end - at_start) / 60) as whours FROM aa_users U INNER JOIN aa_attendance ATT ON U.u_id = ATT.at_u_id AND ATT.at_date BETWEEN '{$rs}' AND '{$re}' and U.u_name like '%$txt_search%') AS DB2 GROUP BY u_id  ORDER BY u_name ASC ";
        else
            $sql = "SELECT u_name, u_id, SUM(whours) as work_hours  FROM (SELECT u_name, u_id, ((at_end - at_start) / 60) as whours FROM aa_users U INNER JOIN aa_attendance ATT ON U.u_id = ATT.at_u_id AND ATT.at_date BETWEEN '{$rs}' AND '{$re}') AS DB2 GROUP BY u_id  ORDER BY u_name ASC ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_report_allleaderemployee($rs, $re, $txt_search = null, $leader_id)
    {
        $leader_id = (int)$leader_id;
        
        if ($txt_search != '') {

            $sql = "
            SELECT 
                u_name,
                u_id,
                SUM(whours) AS work_hours
            FROM (
                SELECT 
                    U.u_name,
                    U.u_id,
                    ((ATT.at_end - ATT.at_start) / 60) AS whours
                FROM aa_users U
                INNER JOIN aa_attendance ATT 
                    ON U.u_id = ATT.at_u_id
                WHERE 
                    ATT.at_date BETWEEN '{$rs}' AND '{$re}'
                    AND U.u_leader = {$leader_id}
                    AND U.u_name LIKE '%{$txt_search}%'
            ) AS DB2
            GROUP BY u_id
            ORDER BY u_name ASC
        ";
        } else {

            $sql = "
            SELECT 
                u_name,
                u_id,
                SUM(whours) AS work_hours
            FROM (
                SELECT 
                    U.u_name,
                    U.u_id,
                    ((ATT.at_end - ATT.at_start) / 60) AS whours
                FROM aa_users U
                INNER JOIN aa_attendance ATT 
                    ON U.u_id = ATT.at_u_id
                WHERE 
                    ATT.at_date BETWEEN '{$rs}' AND '{$re}'
                    AND U.u_leader = {$leader_id}
            ) AS DB2
            GROUP BY u_id
            ORDER BY u_name ASC
        ";
        }
       
        return $this->db->query($sql)->result_array();
    }
    private function update_task_hours($task_id)
    {
        $tasks_ids = array($task_id);
        $tasks = $this->db->select("t_parent")->from('aa_tasks')->where(array('t_id' => $task_id))->get()->result_array();
        //$this->db->last_query();
        if (!empty($tasks)) {
            if ($tasks[0]['t_parent'] == 0) // Main Task
            {
                // get all subtasks
                $sub_tasks = $this->db->select("t_id")->from('aa_tasks')->where(array('t_parent' => $task_id))->get()->result_array();
                foreach ($sub_tasks as $val) {
                    $tasks_ids[] = $val['t_id'];
                }
            }
        }
        $total = 0;
        if (!empty($tasks_ids[0])) {
            // Get all Subtasks
            $sql = "SELECT SUM(whours) as work_hours FROM (SELECT ((at_end - at_start) / 60) as whours FROM aa_attendance WHERE at_t_id IN (" . implode(",", $tasks_ids) . ")) AS DB2";
            $query = $this->db->query($sql);
            $total = $query->result_array();
            if (empty($total)) {
                $total = 0;
            } else {
                if (empty($total[0])) {
                    $total = 0;
                } else {
                    $total = $total[0]['work_hours'];
                }
            }
        }
        $this->db->where('t_id', $task_id)->update('aa_tasks', array('t_hours_total' => $total));
        if (!empty($tasks)) {
            if ($tasks[0]['t_parent'] > 0) // Main Task
            {
                $this->update_task_hours($tasks[0]['t_parent']);
            }
        }
    }
    public function get_monthly_attendance($month, $year, $txt_search = '')
    {
        $this->db->select('u_id, u_name, u_department');
        $this->db->from('aa_users');
        $this->db->where_in('u_type', ['Employee', 'Project Leader']);
        $this->db->where('u_status', 'Active');
        $this->db->order_by('u_name', 'ASC');
        if (!empty($txt_search)) {
            $this->db->like('u_name', $txt_search);
        }
        $users = $this->db->get()->result_array();

        $result = [];
        foreach ($users as $user) {
            $u_id = $user['u_id'];

            // Fetch raw attendance rows
            $this->db->select('at_date, at_start, at_end');
            $this->db->from('aa_attendance');
            $this->db->where('at_u_id', $u_id);
            $this->db->where('MONTH(at_date)', $month);
            $this->db->where('YEAR(at_date)', $year);
            $attendance = $this->db->get()->result_array();

            // Group by day and sum multiple records
            $attendance_map = [];
            foreach ($attendance as $a) {
                $day = (int)date('j', strtotime($a['at_date']));
                $hours = ($a['at_end'] - $a['at_start']) / 60; // raw hours
                if (isset($attendance_map[$day])) {
                    $attendance_map[$day] += $hours;
                } else {
                    $attendance_map[$day] = $hours;
                }
            }

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $days = [];
            $totalHoursRaw = 0; // keep raw sum for total

            for ($d = 1; $d <= $daysInMonth; $d++) {
                if (isset($attendance_map[$d])) {
                    $dayRaw = $attendance_map[$d]; // raw value for total
                    $totalHoursRaw += $dayRaw;

                    // convert only for display
                    $whole = floor($dayRaw);
                    $fraction = $dayRaw - $whole;
                    if ($fraction == 0.75)
                        $fractionConv = 0.45;
                    else if ($fraction == 0.25)
                        $fractionConv = 0.15;
                    else if ($fraction == 0.50)
                        $fractionConv = 0.30;
                    else
                        $fractionConv = $fraction;

                    $convertedDay = number_format($whole + $fractionConv, 2);

                    $days[$d] = $convertedDay; // only display formatted value
                } else {
                    $days[$d] = 'A';
                }
            }

            // Now format total separately (on raw sum)
            $whole = floor($totalHoursRaw);
            $fraction = $totalHoursRaw - $whole;
            if ($fraction == 0.75)
                $fractionConv = 0.45;
            else if ($fraction == 0.25)
                $fractionConv = 0.15;
            else if ($fraction == 0.50)
                $fractionConv = 0.30;
            else
                $fractionConv = $fraction;

            $formattedTotal = number_format($whole + $fractionConv, 2);

            $result[] = [
                'u_id'        => $u_id,
                'u_name'      => $user['u_name'],
                'u_department' => $user['u_department'],
                'days'        => $days,
                'total_hours' => $formattedTotal,
            ];
        }

        return $result;
    }
}
