<?php
public function messages_post()
        {
                $this->load->model('message_model');
                $act = $this->post('act');
                if ($this->admin_session['u_type'] == 'Master Admin' || $this->admin_session['u_type'] == 'Bim Head') {
                } else {
                        if ($act == "add" || $act == "del") {
                                $this->response(array('status' => 'session', 'message' => 'Your session do not permit this action. Please relogin.'));
                        }
                }
                switch ($act) {
                        case "add":
                                $me_id = $this->post('me_id');
                                $me_datetime = $this->post('me_datetime');
                                $me_text = $this->post('me_text');
                                $data = array(
                                        'me_datetime' => date("Y-m-d H:i:s"),
                                        'me_text' => $me_text,
                                );

                                if ($me_id > 0) {
                                        $data['me_id'] = $me_id;
                                }
                                try {
                                        $admin_id = $this->message_model->save($data);
                                        $this->response(array(
                                                'status' => 'pass',
                                                'message' => 'Message is saved.'
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
                                $me_id = $this->post('me_id');
                                if ($me_id > 0) {
                                        try {
                                                $this->message_model->delete_records(array('me_id' => $me_id));
                                                $this->response(array(
                                                        'status' => "pass",
                                                        'message' => 'Message has been deleted successfully.',
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
                                $me_id = $this->post('me_id');
                                if ($me_id > 0) {
                                        $criteria = array();
                                        $criteria['conditions'] = array(array('me_id' => $me_id));
                                        $record = $this->message_model->get_records($criteria, 'result');
                                        if (isset($record[0])) {
                                                $record[0]['me_datetime'] = convert_db2display($record[0]['me_datetime']);
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

                                        $records = $this->message_model->get_records($criteria, 'result');
                                        $criteria['result_type'] = 'count_records';
                                        $totalFiltered = $totalData = $this->message_model->get_records($criteria);
                                        $result = array();
                                        foreach ($records as $single_record) {
                                                $nestedData = array();
                                                $nestedData[] = date("M d, Y", strtotime($single_record['me_datetime']));
                                                $nestedData[] = $single_record['me_text'];
                                                $anchors = '<a href="javascript://" class="btn btn-success btn-md" onClick="showAddEditForm(\'' . $single_record['me_id'] . '\')"><i class="fa fa-edit"></i><a>&nbsp; ';
                                                $anchors .= '<a href="javascript://" class="btn btn-danger btn-md" onClick="deleteRecord(\'' . $single_record['me_id'] . '\')"><i class="fa fa-trash"></i><a>';
                                                if(in_array($this->admin_session['u_type'],['Master Admin', 'Bim Head']))
                                                        $nestedData[] = $anchors;
                                                else
                                                        $nestedData[] = "";
                                                $result[] = $nestedData;
                                        }

                                        $json_data = array(
                                                "draw" => intval($draw),
                                                "recordsTotal" => intval($totalData),
                                                "recordsFiltered" => intval($totalFiltered),
                                                "data" => $result
                                        );
                                        $this->response($json_data);
                                }

                                break;
                }
        }