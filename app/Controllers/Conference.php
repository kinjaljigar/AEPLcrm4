<?php

namespace App\Controllers;

class Conference extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search = $request->getPost('search') ?? '';
        $dataURL = $request->getPost('data') ?? 'upcoming';

        $this->view_data['page'] = 'conference/list';
        $this->view_data['meta_title'] = 'Conferences';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['conferences'] = [];
        $this->view_data['search'] = $search;
        $this->view_data['dataURL'] = $dataURL;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $this->view_data['page'] = 'conference/add';
        $this->view_data['meta_title'] = 'Add Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['timeslots'] = [];
        $this->view_data['token'] = session()->get('token');
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        return redirect()->to('conference');
    }

    public function edit($id)
    {
        $this->view_data['page'] = 'conference/edit';
        $this->view_data['meta_title'] = 'Edit Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['conference'] = [];
        $this->view_data['timeslots'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        return redirect()->to('conference');
    }

    public function delete($id)
    {
        return redirect()->to('conference');
    }

    public function view($id)
    {
        $this->view_data['page'] = 'conference/view';
        $this->view_data['meta_title'] = 'View Conference';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['conference'] = [];
        $this->view_data['timeslots'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }
}
