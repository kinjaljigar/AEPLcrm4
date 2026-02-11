<?php

class Leave_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        if (isset($data['l_id']) && $data['l_id'] > 0) {
            $l_id = $data['l_id'];
            unset($data['l_id']);

            $this->db->where(array('l_id' => $l_id))
                ->update('aa_leaves', $data); // [IMPROVE] can put more validation for approved can not be updated again

        } else {
            $this->db->insert('aa_leaves', $data);
        }
    }
    public function update($data, $conditions)
    {
        $this->db->where($conditions)->update('aa_leaves', $data);
    }
    public function delete_records($data)
    {
        $this->db->delete('aa_leaves', $data);
    }

    public function get_records($params)
    {
        $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : 'l_create_date';
        $params['sort_type'] = isset($params['sort_type']) ? $params['sort_type'] : 'DESC';
        $params['page_size'] = (isset($params['page_size']) && is_numeric($params['page_no'])) ? $params['page_size'] : 0;
        $params['page_no'] = (isset($params['page_no']) && is_numeric($params['page_no'])) ? $params['page_no'] : 1;
        $params['select_list'] = isset($params['select_list']) ? $params['select_list'] : 'L.*, U.u_name';
        $params['conditions'] = isset($params['conditions']) ? $params['conditions'] : array();
        $params['or_conditions'] = isset($params['or_conditions']) ? $params['or_conditions'] : array();
        $params['where_in'] = isset($params['where_in']) ? $params['where_in'] : "";
        $params['result_type'] = isset($params['result_type']) ? $params['result_type'] : 'all_records'; // 'count_records' , 'count_filtered'
        $params['dateseletion'] = isset($params['dateseletion']) ? $params['dateseletion'] : '';
        $this->db->select($params['select_list'])
            ->from('aa_leaves L')
            ->join('aa_users U', 'U.u_id = L.l_u_id')
            //->order_by($params['sort_by'], $params['sort_type']);
            ->order_by('l_create_date DESC,l_status ASC');

        foreach ($params['conditions'] as $value) {
            //print_r($value);
            $this->db->where($value);
        }

        if ($params['where_in'] != '') {
            $this->db->where_in('l_u_id', $params['where_in']);
        }
        // foreach ($params['where_in'] as $value) {
        //     print_r($value);
        //     $this->db->where_in('l_u_id', $value);
        // }

        if ($params['dateseletion'] != '') {
            $rs = $params['dateseletion'][0];
            $re = $params['dateseletion'][1];
            $Sql_1 = "(
                      ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) OR
                      (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date)) OR
                      (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) OR
                      ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))
                      )";
            //print $Sql_1;
            $this->db->where($Sql_1);
        }
        foreach ($params['or_conditions'] as $value) {
            $this->db->or_where($value);
        }
        //print_r($this->db->last_query());
        if ($params['result_type'] != 'count_records') {
            if ($params['page_size'] > 0) {
                $this->db->limit($params['page_size'], $params['page_no']);
            }
        }
        if ($params['result_type'] == 'all_records') {
            $query = $this->db->get();
            return $query->result_array();
        } else {
            $query = $this->db->get();
            return $query->num_rows();
        }
    }
    public function get_reports($rs, $re, $txt_search = null)
    {

        // $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))";
        // $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE l_status = 'Approved' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))";
        // $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE l_status = 'Approved' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))";
        // $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))";
        // $sql = "SELECT u_name, SUM(total_days) as final_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4})) as FinalTb GROUP BY l_u_id";
        $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO'and l_is_hourly = 'No' and u_name like '%$txt_search%' ";
        $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='NO' and l_is_hourly = 'No'  and u_name like '%$txt_search%' ";
        $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='NO' and l_is_hourly = 'No'  and u_name like '%$txt_search%' ";
        $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='NO' and l_is_hourly = 'No' and u_name like '%$txt_search%' ";

        $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_is_hourly = 'No' and u_name like '%$txt_search%' ";
        $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'No' and u_name like '%$txt_search%' ";
        $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_is_hourly = 'No' and u_name like '%$txt_search%' ";
        $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'No' and u_name like '%$txt_search%' ";
        //$sql = "SELECT u_name, SUM(total_days) as final_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4})) as FinalTb GROUP BY l_u_id";
        $sql = "SELECT l_u_id,u_name, SUM(total_days) as final_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";
        //echo "<br/>" . $sql;
        $query = $this->db->query($sql);

        return $query->result_array();
    }
    public function get_reports_hourly($rs, $re, $txt_search = null)
    {

        // $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO'and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";
        // $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='NO' and l_is_hourly = 'Yes'  and u_name like '%$txt_search%' ";
        // $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='NO' and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";
        // $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='NO' and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";

        // $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";
        // $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";
        // $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";
        // $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'Yes' and u_name like '%$txt_search%' ";
        // $sql = "SELECT l_u_id,u_name, SUM(total_days) as final_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";

        $sql = $this->db->select("l_u_id, u_name, SUM(
        TIME_TO_SEC(STR_TO_DATE(l_hourly_time_hour, '%H.%i'))
    ) / 3600 AS final_leave", false);
        $this->db->from("aa_leaves L");
        $this->db->join("aa_users U", "L.l_u_id = U.u_id", "inner");
        $this->db->where("l_is_hourly", "Yes");
        $this->db->where("(
        (l_from_date BETWEEN '$rs' AND '$re') 
        OR (l_to_date BETWEEN '$rs' AND '$re')
    )", null, false);

        if (!empty($txt_search)) {
            $this->db->like("u_name", $txt_search);
        }

        $this->db->group_by("l_u_id, u_name");
        $query = $this->db->get();
        return $query->result_array();
        //         $sql = "SELECT l_u_id, 
        //     u_name, 
        //     SEC_TO_TIME(SUM(TIME_TO_SEC(STR_TO_DATE(l_hourly_time_hour, '%H.%i')))) AS final_leave 
        // FROM aa_leaves L 
        // INNER JOIN aa_users U ON L.l_u_id = U.u_id 
        // WHERE 
        //     ((l_from_date BETWEEN '{$rs}' AND '{$re}') 
        //     OR (l_to_date BETWEEN '{$rs}' AND '{$re}')) 
        //     AND l_is_hourly = 'Yes' 
        //     AND u_name LIKE '%$txt_search%' 
        // GROUP BY l_u_id, u_name";



        //echo "<br/>" . $sql;
        //$query = $this->db->query($sql);

        //return $query->result_array();
    }
    public function get_reports_approved($rs, $re, $l_u_id)
    {

        $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'No'";
        $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'No'";
        $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'No'";
        $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'No'";

        $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql = "SELECT l_u_id,u_name, SUM(total_days) as approved_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_reports_declined($rs, $re, $l_u_id)
    {

        $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'No'";
        $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'No'";
        $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'No'";
        $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'No'";

        $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'No'";
        $sql = "SELECT l_u_id,u_name, SUM(total_days) as declined_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";
        $query = $this->db->query($sql);

        return $query->result_array();
    }
    public function get_reports_approved_total($rs, $re)
    {
        $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_status = 'Approved'";
        $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='NO' and l_status = 'Approved'";
        $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='NO' and l_status = 'Approved'";
        $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='NO' and l_status = 'Approved'";

        $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_status = 'Approved'";
        $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_status = 'Approved'";
        $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_status = 'Approved'";
        $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_status = 'Approved'";
        //$sql = "SELECT u_name, SUM(total_days) as final_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4})) as FinalTb GROUP BY l_u_id";
        echo $sql = "SELECT l_u_id,u_name, SUM(total_days) as approved_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_reports_approved_hourly($rs, $re, $l_u_id)
    {

        // $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'Yes'";
        // $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'Yes'";
        // $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'Yes'";
        // $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'Yes'";

        // $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Approved' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql = "SELECT l_u_id,u_name, SUM(total_days) as approved_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";
        // $query = $this->db->query($sql);
        // return $query->result_array();
        $sql = "SELECT 
                L.l_u_id, 
                U.u_name, 
                 CONCAT(
        FLOOR(SUM(FLOOR(L.l_hourly_time_hour) * 60 + ROUND((L.l_hourly_time_hour - FLOOR(L.l_hourly_time_hour)) * 100)) / 60), 
        '.', 
        LPAD(MOD(SUM(FLOOR(L.l_hourly_time_hour) * 60 + ROUND((L.l_hourly_time_hour - FLOOR(L.l_hourly_time_hour)) * 100)), 60), 2, '0')
    ) AS approved_leave
            FROM aa_leaves L
            INNER JOIN aa_users U ON L.l_u_id = U.u_id
            WHERE 
                L.l_is_hourly = 'Yes' AND L.l_u_id = '{$l_u_id}'
                AND L.l_status = 'Approved'
                AND (
                    (L.l_from_date BETWEEN '{$rs}' AND '{$re}') 
                    OR (L.l_to_date BETWEEN '{$rs}' AND '{$re}')
                    OR ('{$rs}' BETWEEN L.l_from_date AND L.l_to_date)
                    OR ('{$re}' BETWEEN L.l_from_date AND L.l_to_date)
                )";

        if (!empty($txt_search)) {
            $sql .= " AND U.u_name LIKE '%{$txt_search}%'";
        }

        $sql .= " GROUP BY L.l_u_id, U.u_name";

        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_reports_declined_hourly($rs, $re, $l_u_id)
    {

        // $sql_1 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'Yes'";
        // $sql_2 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'Yes'";
        // $sql_3 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='NO' and l_is_hourly = 'Yes'";
        // $sql_4 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + 1 as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date)) and l_is_halfday='NO' and l_is_hourly = 'Yes'";

        // $sql_5 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND (l_to_date BETWEEN '{$rs}' AND '{$re}')) and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql_6 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql_7 = "SELECT l_id, l_u_id, u_name, DATEDIFF(l_to_date, '{$rs}') + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND  (('{$rs}' BETWEEN l_from_date AND l_to_date) AND (l_to_date BETWEEN '{$rs}' AND '{$re}'))  and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql_8 = "SELECT l_id, l_u_id, u_name, DATEDIFF('{$re}', l_from_date) + (0.5) as total_days FROM aa_leaves L INNER JOIN aa_users U ON L.l_u_id = U.u_id WHERE L.l_u_id = '{$l_u_id}' and l_status = 'Declined' AND ((l_from_date BETWEEN '{$rs}' AND '{$re}') AND ('{$re}' BETWEEN l_from_date AND l_to_date))  and l_is_halfday='Yes' and l_is_hourly = 'Yes'";
        // $sql = "SELECT l_u_id,u_name, SUM(total_days) as declined_leave FROM (({$sql_1}) UNION ({$sql_2}) UNION ({$sql_3}) UNION ({$sql_4}) UNION ({$sql_5}) UNION ({$sql_6}) UNION ({$sql_7}) UNION ({$sql_8}))   as FinalTb GROUP BY l_u_id,u_name";
        // $query = $this->db->query($sql);

        // return $query->result_array();
        $sql = "SELECT 
                L.l_u_id, 
                U.u_name, 
                 CONCAT(
        FLOOR(SUM(FLOOR(L.l_hourly_time_hour) * 60 + ROUND((L.l_hourly_time_hour - FLOOR(L.l_hourly_time_hour)) * 100)) / 60), 
        '.', 
        LPAD(MOD(SUM(FLOOR(L.l_hourly_time_hour) * 60 + ROUND((L.l_hourly_time_hour - FLOOR(L.l_hourly_time_hour)) * 100)), 60), 2, '0')
    ) AS declined_leave
            FROM aa_leaves L
            INNER JOIN aa_users U ON L.l_u_id = U.u_id
            WHERE 
                L.l_is_hourly = 'Yes' AND L.l_u_id = '{$l_u_id}'
                AND L.l_status = 'Declined'
                AND (
                    (L.l_from_date BETWEEN '{$rs}' AND '{$re}') 
                    OR (L.l_to_date BETWEEN '{$rs}' AND '{$re}')
                    OR ('{$rs}' BETWEEN L.l_from_date AND L.l_to_date)
                    OR ('{$re}' BETWEEN L.l_from_date AND L.l_to_date)
                )";

        if (!empty($txt_search)) {
            $sql .= " AND U.u_name LIKE '%{$txt_search}%'";
        }

        $sql .= " GROUP BY L.l_u_id, U.u_name";

        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function get_reports_total_hourly($rs, $re, $l_u_id)
    {

        $sql = "SELECT 
                L.l_u_id, 
                U.u_name, 
                 CONCAT(
        FLOOR(SUM(FLOOR(L.l_hourly_time_hour) * 60 + ROUND((L.l_hourly_time_hour - FLOOR(L.l_hourly_time_hour)) * 100)) / 60), 
        '.', 
        LPAD(MOD(SUM(FLOOR(L.l_hourly_time_hour) * 60 + ROUND((L.l_hourly_time_hour - FLOOR(L.l_hourly_time_hour)) * 100)), 60), 2, '0')
    ) AS total_leave
            FROM aa_leaves L
            INNER JOIN aa_users U ON L.l_u_id = U.u_id
            WHERE 
                L.l_is_hourly = 'Yes' AND L.l_u_id = '{$l_u_id}'
               AND (
                    (L.l_from_date BETWEEN '{$rs}' AND '{$re}') 
                    OR (L.l_to_date BETWEEN '{$rs}' AND '{$re}')
                    OR ('{$rs}' BETWEEN L.l_from_date AND L.l_to_date)
                    OR ('{$re}' BETWEEN L.l_from_date AND L.l_to_date)
                )";

        if (!empty($txt_search)) {
            $sql .= " AND U.u_name LIKE '%{$txt_search}%'";
        }

        $sql .= " GROUP BY L.l_u_id, U.u_name";

        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
