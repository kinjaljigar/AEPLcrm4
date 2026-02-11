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
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $this->view_data['page'] = 'schedule/add';
        $this->view_data['meta_title'] = 'Add Schedule';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
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
}
