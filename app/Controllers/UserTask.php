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
}
