<?php

namespace App\Controllers;

class Schedule extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search  = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'upcoming';
        $type    = $request->getPost('type') ?? 'mydata';

        $endpoint = 'schedule/list?page=1&limit=1000&data=' . $dataURL . '&type=' . $type;
        if ($search !== '') {
            $endpoint = 'schedule/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL . '&type=' . $type;
        }

        $result    = $this->callExternalApi($endpoint);
        $decoded   = json_decode($result['body'], true);
        $schedules = $decoded['data'] ?? [];

        $this->view_data['page']          = 'schedule/list';
        $this->view_data['meta_title']    = 'Schedules';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['schedules']     = $schedules;
        $this->view_data['search']        = $search;
        $this->view_data['dataURL']       = $dataURL;
        $this->view_data['type']          = $type;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $db = \Config\Database::connect();
        $timeslots = $db->table('aa_shedule_time_slots')
            ->select('id, value')
            ->get()->getResultArray();

        $this->view_data['page']          = 'schedule/add';
        $this->view_data['meta_title']    = 'Add Schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['timeslots']     = $timeslots;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['cliBaseUrl']    = $this->cliBaseUrl;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        $data = [
            'title'        => $this->request->getPost('title'),
            'description'  => $this->request->getPost('description'),
            'date'         => $this->request->getPost('date'),
            'shedule_type' => $this->request->getPost('shedule_type'),
            'timeslot_id'  => $this->request->getPost('timeslot_id'),
        ];

        $result  = $this->callExternalApi('schedule/add', 'POST', $data);
        $decoded = json_decode($result['body'], true);

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('schedule');
        }
        session()->setFlashdata('error', $decoded['message'] ?? 'Failed to add schedule.');
        return redirect()->to('schedule/add');
    }

    public function edit($id)
    {
        $result  = $this->callExternalApi('schedule/edit/' . $id);
        $decoded = json_decode($result['body'], true);
        $schedule = $decoded['data'] ?? [];

        // Fetch timeslots for the schedule date
        $date = $schedule['date'] ?? date('Y-m-d');
        $tsResult  = $this->callExternalApi('schedule/timeslots/' . $date);
        $tsDecoded = json_decode($tsResult['body'], true);
        $timeslots = $tsDecoded['availableslots'] ?? [];

        $this->view_data['page']          = 'schedule/edit';
        $this->view_data['meta_title']    = 'Edit Schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['schedule']      = $schedule;
        $this->view_data['timeslots']     = $timeslots;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['cliBaseUrl']    = $this->cliBaseUrl;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        $data = [
            'title'        => $this->request->getPost('title'),
            'description'  => $this->request->getPost('description'),
            'date'         => $this->request->getPost('date'),
            'shedule_type' => $this->request->getPost('shedule_type'),
            'timeslot_id'  => $this->request->getPost('timeslot_id'),
        ];

        $result  = $this->callExternalApi('schedule/update/' . $id, 'PUT', $data);
        $decoded = json_decode($result['body'], true);

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('schedule');
        }
        session()->setFlashdata('error', $decoded['message'] ?? 'Failed to update schedule.');
        return redirect()->to('schedule/edit/' . $id);
    }

    public function delete($id)
    {
        $this->callExternalApi('schedule/delete/' . $id, 'DELETE');
        return redirect()->to('schedule');
    }

    public function updateSchedule()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $date = $this->request->getPost('date');

        $result      = $this->callExternalApi('schedule/timeslots/' . $date);
        $apiResponse = json_decode($result['body'], true);
        $timeslots   = $apiResponse['availableslots'] ?? [];

        header('Content-Type: application/json');
        echo json_encode([
            'status'       => 'success',
            'message'      => 'schedule updated successfully',
            'api_response' => ['timeslots' => $timeslots],
        ]);
        exit;
    }
}
