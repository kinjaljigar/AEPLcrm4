<?php 
        public function timesheet_post()
        {
                $this->load->model('timesheet_model');
                $act = $this->post('act');
                switch ($act) {
                        case "add":
                                $at_id = $this->post('at_id');
                                $at_p_id = $this->post('at_p_id');
                                $at_t_id = $this->post('at_t_id');
                                $at_date = $this->post('at_date');
                                $at_start = $this->post('at_start');
                                $at_end = $this->post('at_end');
                                $data = array(
                                        'at_p_id' => $at_p_id,
                                        'at_t_id' => $at_t_id,
                                        'at_u_id' => $at_u_id, // Change this later
                                        'at_date' => $at_date,
                                        'at_start' => $at_start,
                                        'at_end' => $at_end,
                                );

                                if ($at_id > 0) {
                                        $data['at_id'] = $at_id;
                                }
                                try {
                                        $admin_id = $this->timesheet_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Timesheet data is saved.'
                                        ));
                                } catch (Exception $ex) {
                                        $this->response(array(
                                                'status' => 'fail',
                                                'type' => 'popup',
                                                'message' => $ex->getMessage()
                                        ));
                                }
                                break;
                        case "del":
                                $at_id = $this->post('at_id');
                                $at_p_id = $this->post('at_p_id');
                                if ($at_id > 0) {
                                        try {
                                                $this->timesheet_model->delete(array('at_id' => $at_id, 'at_p_id' => $at_p_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Timesheet data has been deleted successfully.',
                                                ));
                                        } catch (Exception $ex) {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => $ex->getMessage()
                                                ));
                                        }
                                }
                                break;
                        case "list":
                                $at_id = $this->post('at_id');
                                $at_p_id = $this->post('at_p_id');
                                if ($at_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('at_id' => $at_id, 'at_p_id' => $at_p_id));
                                        $record = $this->timesheet_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $this->response(array(
                                                        'status' => 'pass',
                                                        'data' => $record[0]
                                                ));
                                        } else {
                                                $this->response(array(
                                                        'status' => 'fail',
                                                        'message' => 'Selected record is not available.'
                                                ));
                                        }
                                } else {
                                        $draw = $this->post('draw');
                                        $sort = $this->post('order');
                                        $search = $this->post('search');
                                        $offset = $this->post('start');
                                        $limit = $this->post('length');


                                        $criteria = array();
                                        $criteria['page_no'] = $offset;
                                        $criteria['page_size'] = $limit;
                                        $criteria['conditions'] = array();
                                        $criteria['conditions'][] = array("at_p_id" => $at_p_id);

                                        $records = $this->timesheet_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->timesheet_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = $single_record['at_t_id'];
                                                $nestedData[] = $single_record['at_end'];
                                                $nestedData[] = $single_record['at_start'];
                                                $nestedData[] = $single_record['at_date'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['at_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['at_id'] . '\')"><i class="fa fa-trash"></i><a>&nbsp; ';
                                                $nestedData[] = $anchors;
                                                $result[] = $nestedData;
                                        }
                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result,
                                        );
                                        $this->response($json_data);
                                }
                                break;
                }
        }
