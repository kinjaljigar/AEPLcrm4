<?php

namespace App\Controllers;

class UserTask extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search  = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'pending';

        $endpoint = $search
            ? 'task/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL
            : 'task/list?page=1&limit=1000&data=' . $dataURL;

        $result  = $this->callExternalApi($endpoint);
        $decoded = json_decode($result['body'], true);
        $tasks   = $decoded ?? [];

        $this->view_data['page']          = 'company/user/task/list';
        $this->view_data['meta_title']    = 'Aashir Connect';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['tasks']         = $tasks;
        $this->view_data['search']        = $search;
        $this->view_data['dataURL']       = $dataURL;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function view($id)
    {
        $result  = $this->callExternalApi('task/edit/' . $id);
        $decoded = json_decode($result['body'], true);
        $task    = $decoded['task'] ?? $decoded['data'] ?? [];

        // Ensure attachment URLs are absolute (prepend aashirmob base if relative)
        if (!empty($task['attachments']) && is_array($task['attachments'])) {
            $assetBase = str_replace('v1/', '', config('App')->cliBaseUrl);
            foreach ($task['attachments'] as &$file) {
                if (strpos($file, 'http') !== 0) {
                    $file = $assetBase . $file;
                }
            }
            unset($file);
        }

        $this->view_data['page']          = 'company/user/task/view';
        $this->view_data['meta_title']    = 'View Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['task']          = $task;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        // Fetch company users for assignment dropdown
        $result  = $this->callExternalApi('company/user/list?page=1&limit=10000');
        $decoded = json_decode($result['body'], true);
        $allusers = array_merge(
            $decoded['allusers'] ?? [],
            $decoded['adminallusers'] ?? []
        );

        $this->view_data['page']          = 'company/user/task/add';
        $this->view_data['meta_title']    = 'Add Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['allusers']      = $allusers;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        $files = $this->request->getFileMultiple('attachments') ?? [];

        $userIds = $this->request->getPost('user_ids') ?? [];
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'date'        => $this->request->getPost('date'),
            'time'        => $this->request->getPost('time'),
            'user_ids'    => is_array($userIds) ? implode(',', $userIds) : $userIds,
        ];

        // Add file attachments as CURLFile objects
        if (!empty($files)) {
            foreach ($files as $index => $file) {
                if (is_object($file) && $file->isValid() && !$file->hasMoved()) {
                    $data['attachments[' . $index . ']'] = new \CURLFile(
                        $file->getTempName(),
                        $file->getMimeType(),
                        $file->getName()
                    );
                }
            }
        }

        $result  = $this->callExternalApi('task/add', 'POST', $data);
        $decoded = json_decode($result['body'], true);

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('usertask');
        }
        session()->setFlashdata('error_message', $decoded['message'] ?? 'Failed to add task.');
        return redirect()->to('usertask/add');
    }

    public function edit($id)
    {
        $result  = $this->callExternalApi('task/edit/' . $id);
        $decoded = json_decode($result['body'], true);
        $task    = $decoded['task'] ?? $decoded['data'] ?? [];

        // Ensure attachment URLs are absolute (prepend aashirmob base if relative)
        if (!empty($task['attachments']) && is_array($task['attachments'])) {
            $assetBase = str_replace('v1/', '', config('App')->cliBaseUrl);
            foreach ($task['attachments'] as &$file) {
                if (strpos($file, 'http') !== 0) {
                    $file = $assetBase . $file;
                }
            }
            unset($file);
        }

        // Fetch company users for assignment dropdown
        $usersResult  = $this->callExternalApi('company/user/list?page=1&limit=1000');
        $usersDecoded = json_decode($usersResult['body'], true);
        $allusers = array_merge(
            $usersDecoded['allusers'] ?? [],
            $usersDecoded['adminallusers'] ?? []
        );

        $this->view_data['page']          = 'company/user/task/edit';
        $this->view_data['meta_title']    = 'Edit Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token']         = $this->token ?? '';
        $this->view_data['task']          = $task;
        $this->view_data['allusers']      = $allusers;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        $files = $this->request->getFileMultiple('attachments') ?? [];

        $userIds = $this->request->getPost('user_ids') ?? [];
        $deleteAttachments = $this->request->getPost('delete_attachments') ?? [];
        $data = [
            'title'              => $this->request->getPost('title'),
            'description'        => $this->request->getPost('description'),
            'date'               => $this->request->getPost('date'),
            'time'               => $this->request->getPost('time'),
            'user_ids'           => is_array($userIds) ? implode(',', $userIds) : $userIds,
            'delete_attachments' => is_array($deleteAttachments) ? implode(',', $deleteAttachments) : $deleteAttachments,
        ];

        if (!empty($files)) {
            foreach ($files as $index => $file) {
                if (is_object($file) && $file->isValid() && !$file->hasMoved()) {
                    $data['attachments[' . $index . ']'] = new \CURLFile(
                        $file->getTempName(),
                        $file->getMimeType(),
                        $file->getName()
                    );
                }
            }
        }

        $result  = $this->callExternalApi('task/update/' . $id, 'POST', $data);
        $decoded = json_decode($result['body'], true);

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('usertask');
        }
        session()->setFlashdata('error_message', $decoded['message'] ?? 'Failed to update task.');
        return redirect()->to('usertask/edit/' . $id);
    }

    public function delete($id)
    {
        $this->callExternalApi('task/delete/' . $id, 'DELETE');
        return redirect()->to('usertask');
    }

    public function fetchTasks()
    {
        $request = service('request');
        $search  = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'pending';

        $endpoint = $search
            ? 'task/list?search=' . urlencode($search) . '&page=1&limit=1000&data=' . $dataURL
            : 'task/list?page=1&limit=1000&data=' . $dataURL;

        $result  = $this->callExternalApi($endpoint);
        $decoded = json_decode($result['body'], true);

        $view_data = [
            'tasks'         => $decoded ?? [],
            'admin_session' => $this->admin_session,
            'dataURL'       => $dataURL,
        ];

        return view('company/user/task/task_table_partial', ['view_data' => $view_data]);
    }

    public function status($id)
    {
        $request = service('request');

        if ($request->getMethod() === 'post') {
            $comment = $request->getPost('task_reason') ?? $request->getPost('t_comment') ?? '';

            $data = [
                'task_completed' => '1',
                'task_reason'    => $comment,
                'completed_at'   => date('Y-m-d H:i:s'),
            ];

            $result  = $this->callExternalApi('task/statusupdate/' . $id, 'PUT', $data);
            $decoded = json_decode($result['body'], true);

            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => ($decoded['status'] ?? '') == 200 || $result['code'] == 200 ? 'pass' : 'fail',
                'message' => $decoded['message'] ?? 'Status updated.',
            ]);
            exit;
        }

        // GET - return task data from API
        $result  = $this->callExternalApi('task/edit/' . $id);
        $decoded = json_decode($result['body'], true);

        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'pass', 'data' => $decoded['task'] ?? $decoded['data'] ?? []]);
        exit;
    }
}
