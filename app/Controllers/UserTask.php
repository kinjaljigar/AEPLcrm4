<?php

namespace App\Controllers;

class UserTask extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'upcoming';

        $this->view_data['page'] = 'company/user/task/list';
        $this->view_data['meta_title'] = 'Aashir Connect';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        $this->view_data['tasks'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function view($id)
    {
        $this->view_data['page'] = 'company/user/task/view';
        $this->view_data['meta_title'] = 'View Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['task'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $db = \Config\Database::connect();
        $allusers = $db->table('aa_users')
            ->where('u_status', 'Active')
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        $this->view_data['page'] = 'company/user/task/add';
        $this->view_data['meta_title'] = 'Add Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = session()->get('token') ?? '';
        $this->view_data['allusers'] = $allusers;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        return redirect()->to('usertask');
    }

    public function edit($id)
    {
        $db = \Config\Database::connect();
        $allusers = $db->table('aa_users')
            ->where('u_status', 'Active')
            ->orderBy('u_name', 'ASC')
            ->get()->getResultArray();

        $this->view_data['page'] = 'company/user/task/edit';
        $this->view_data['meta_title'] = 'Edit Task';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = session()->get('token') ?? '';
        $this->view_data['allusers'] = $allusers;
        $this->view_data['task'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        return redirect()->to('usertask');
    }

    public function delete($id)
    {
        return redirect()->to('usertask');
    }

    public function fetchTasks()
    {
        return redirect()->to('usertask');
    }

    public function status($id)
    {
        $request = service('request');
        if ($request->getMethod() === 'post') {
            // Handle task completion
            $db = \Config\Database::connect();
            $comment = $request->getPost('t_comment') ?? '';
            $u_id = $this->admin_session['u_id'];

            $db->table('aa_project_task_users')
                ->where('task_id', $id)
                ->where('u_id', $u_id)
                ->update(['task_completed' => 1, 'comment' => $comment]);

            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'pass', 'message' => 'Task completed.']);
            exit;
        }

        // GET - return task data
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'pass', 'data' => ['task_id' => $id]]);
        exit;
    }
}
