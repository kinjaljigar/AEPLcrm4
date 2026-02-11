<?php
class General
{
    private $CI;
    public function __construct()
    {
        $this->CI = & get_instance();
    }
    // will return false if passed validation., else return error
    //$t_id = Task ID
    //$t_parent if Zero means Top, if -1 then need get from DB else it is actual id
    //$role = User Role Admin Session
    //$type = Validation type, Validate for owner or assignment
    public function ValidateTaskAddEdit($t_id, $t_parent, $role, $owner = true)
    {
        $u_id = $role['u_id'];
        $role = $role['u_type'];
        if(in_array($role, ['Bim Head','Master Admin']))
        {
            return false;
        }
        if(in_array($role, ['Employee']))
        {
            return "You do not have access for this section.e";
        }
        if($t_parent == 0)
        {
            return "You do not have access for this section.1";
        }
        else
        {
            //echo $t_id ." ".$t_parent;
            if($t_id == 0) // This is add (Validated for parent access) in this case parent must be > 0
            {
                $params = array();
                $params['conditions'] = array(array('tu_t_id' => $t_parent), array('tu_u_id' => $u_id), array('tu_removed' => 'No'));
                $task = $this->CI->task_model->get_records_by_assignee($params);
                if(empty($task)) return "You do not have access for this section.21";
                else return false;
            }
            else
            {
                $this->CI->load->model("task_model");
                $params = array();
                $params['conditions'] = array(array('t_id' => $t_id));
                $task = $this->CI->task_model->get_records($params);
                if(empty($task)) return "You do not have access for this section.2";
                else
                {
                    $task = $task[0];
                    if($task['t_parent'] == 0)
                    {
                        return "You do not have access for this section.3";
                    }
                    if($owner)
                    {
                        if($u_id != $task['t_u_id']) return "You do not have access for this section.4";
                    }
                    else
                    {
                        // Add Code Later
                    }
                }
            }
        }
    }
    // will return false if passed validation., else return error
    //$t_id = Task ID
    //$role = User Role Admin Session
    public function ValidateTaskAssignment($t_id, $role)
    {
        $u_id = $role['u_id'];
        $role = $role['u_type'];
        if(in_array($role, ['Bim Head','Master Admin']))
        {
            return true;
        }
        if(in_array($role, ['Employee']))
        {
            //return false;
        }
        if($t_id == 0)
        {
            return false;
        }
        else
        {
            $params = array();
            $params['conditions'] = array(array('t_id' => $t_id), array('t_u_id' => $u_id));
            $task = $this->CI->task_model->get_records($params);
            if(!empty($task)) return true; // Task created by user

            $params = array();
            $params['conditions'] = array(array('tu_t_id' => $t_id), array('tu_u_id' => $u_id));
            $task = $this->CI->task_model->get_records_by_assignee($params);
            if(!empty($task)) return true; // Task is assigned to user

            else{
                $params = array();
                $params['conditions'] = array(array('t_id' => $t_id));
                $task = $this->CI->task_model->get_records($params);
                if(!empty($task))
                {
                    if($task[0]['t_parent'] != 0) return false; // This is sub task
                    else {
                        // Check if any subtask is assigned
                        $params = array();
                        $params['conditions'] = array(array('t_parent' => $t_id), array('tu_u_id' => $u_id));
                        $task = $this->CI->task_model->get_records_by_assignee($params);
                        if(!empty($task)) return true; // subTask is assigned to user
                        else return false;
                    }
                }
                else
                    return false;
            }
        }
    }
}