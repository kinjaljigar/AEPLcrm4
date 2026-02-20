<?php

namespace App\Controllers;

class Conference extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search  = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'upcoming';

        $endpoint = $search
            ? 'conference/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL
            : 'conference/list?page=1&limit=1000&data=' . $dataURL;

        $result = $this->callExternalApi($endpoint);
        $decoded = json_decode($result['body'], true) ?: [];
        $conferences = $decoded ?? [];

        $this->view_data['page']          = 'conference/list';
        $this->view_data['meta_title']    = 'Conferences';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['conferences']   = $conferences;
        $this->view_data['search']        = $search;
        $this->view_data['dataURL']       = $dataURL;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $db = \Config\Database::connect();
        $timeslots = $db->table('aa_conference_time_slots')
            ->select('id, value')
            ->get()->getResultArray();

        $this->view_data['page']          = 'conference/add';
        $this->view_data['meta_title']    = 'Add Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['timeslots']     = $timeslots;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['cliBaseUrl']    = $this->cliBaseUrl;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        $timeslotIds = $this->request->getPost('timeslot_id');
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'date'        => $this->request->getPost('date'),
            'room_id'     => $this->request->getPost('room_id'),
            'timeslot_id' => is_array($timeslotIds) ? implode(',', $timeslotIds) : $timeslotIds,
        ];

        $result  = $this->callExternalApi('conference/add', 'POST', $data);
        $decoded = json_decode($result['body'], true) ?: [];

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('conference');
        }
        session()->setFlashdata('error_message', $decoded['message'] ?? 'Failed to add conference.');
        return redirect()->to('conference/add');
    }

    public function edit($id)
    {
        $result  = $this->callExternalApi('conference/edit/' . $id);
        $decoded = json_decode($result['body'], true) ?: [];
        $conference = $decoded ?? [];

        // Fetch timeslots for the conference date
        $confData = $decoded['data'] ?? [];
        $date = $confData['date'] ?? date('Y-m-d');
        $tsResult  = $this->callExternalApi('conference/timeslots/' . $date . '/' . ($confData['room_id'] ?? ''));
        $tsDecoded = json_decode($tsResult['body'], true) ?: [];
        $timeslots = $tsDecoded['availableslots'] ?? [];

        $this->view_data['page']          = 'conference/edit';
        $this->view_data['meta_title']    = 'Edit Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['conference']    = $conference;
        $this->view_data['timeslots']     = $timeslots;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['cliBaseUrl']    = $this->cliBaseUrl;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        $timeslotIds = $this->request->getPost('timeslot_id');
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'date'        => $this->request->getPost('date'),
            'room_id'     => $this->request->getPost('room_id'),
            'timeslot_id' => is_array($timeslotIds) ? implode(',', $timeslotIds) : $timeslotIds,
        ];

        $result  = $this->callExternalApi('conference/update/' . $id, 'PUT', $data);
        $decoded = json_decode($result['body'], true) ?: [];

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('conference');
        }
        session()->setFlashdata('error_message', $decoded['message'] ?? 'Failed to update conference.');
        return redirect()->to('conference/edit/' . $id);
    }

    public function delete($id)
    {
        $this->callExternalApi('conference/delete/' . $id, 'DELETE');
        return redirect()->to('conference');
    }

    public function view($id)
    {
        $result  = $this->callExternalApi('conference/edit/' . $id);
        $decoded = json_decode($result['body'], true) ?: [];
        $conference = $decoded ?? [];

        $tsResult  = $this->callExternalApi('conference/timeslots');
        $tsDecoded = json_decode($tsResult['body'], true) ?: [];
        $timeslots = $tsDecoded['availableslots'] ?? [];

        $this->view_data['page']          = 'conference/view';
        $this->view_data['meta_title']    = 'View Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['conference']    = $conference;
        $this->view_data['timeslots']     = $timeslots;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['cliBaseUrl']    = $this->cliBaseUrl;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function debugApiTest()
    {
        while (ob_get_level() > 0) ob_end_clean();
        $testUrl = $this->cliBaseUrl . 'schedule/timeslots/' . date('Y-m-d');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        header('Content-Type: application/json');
        echo json_encode([
            'cliBaseUrl'       => $this->cliBaseUrl,
            'token_set'        => !empty($this->token),
            'token_preview'    => $this->token ? substr($this->token, 0, 30) . '...' : 'EMPTY',
            'test_url'         => $testUrl,
            'http_code'        => $httpCode,
            'curl_error'       => $curlError ?: null,
            'api_raw_response' => $response,
        ]);
        exit;
    }

    public function updateConference()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $date    = $this->request->getPost('date');
        $room_id = $this->request->getPost('room_id');

        $result    = $this->callExternalApi('conference/timeslots/' . $date . '/' . $room_id);
        $apiResponse = json_decode($result['body'], true) ?: [];
        $timeslots = $apiResponse['availableslots'] ?? [];

        header('Content-Type: application/json');
        echo json_encode([
            'status'       => 'success',
            'message'      => 'Conference updated successfully',
            'api_response' => ['timeslots' => $timeslots],
        ]);
        exit;
    }
}
