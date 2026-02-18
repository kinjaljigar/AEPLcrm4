<?php

namespace App\Controllers;

class Schedule extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'upcoming';

        $this->view_data['page'] = 'schedule/list';
        $this->view_data['meta_title'] = 'Schedules';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['schedules'] = [];
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        $this->view_data['type'] = $request->getPost('type') ?? 'mydata';
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $this->view_data['page'] = 'schedule/add';
        $this->view_data['meta_title'] = 'Add Schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['timeslots'] = [];
        $this->view_data['token'] = session()->get('token') ?? '';
        $this->view_data['cliBaseUrl'] = '';
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        return redirect()->to('schedule');
    }

    public function edit($id)
    {
        $this->view_data['page'] = 'schedule/edit';
        $this->view_data['meta_title'] = 'Edit Schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['schedule'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        return redirect()->to('schedule');
    }

    public function delete($id)
    {
        return redirect()->to('schedule');
    }

    public function updateSchedule()
    {
        ob_end_clean();
        $date = $this->request->getPost('date');

        $url = 'schedule/timeslots/' . $date;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cliBaseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
        ]);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            log_message('error', 'cURL error: ' . curl_error($ch));
            $response = json_encode([]);
        }
        curl_close($ch);

        $apiResponse = json_decode($response, true);
        $timeslots = $apiResponse['availableslots'] ?? [];

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'schedule updated successfully',
            'api_response' => ['timeslots' => $timeslots]
        ]);
        exit;
    }
}
