<?php
class Tapasho extends CI_Controller { 

    public function index()
    {
        return false;
        $this->load->model("task_model");
        $this->task_model->adjustTaskHours(15,"del");
    }
} 
?>