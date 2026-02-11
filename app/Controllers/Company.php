<?php

namespace App\Controllers;

class Company extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search = $request->getPost('search') ?? '';

        $this->view_data['page'] = 'company/list';
        $this->view_data['meta_title'] = 'Companies';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['companies'] = [];
        $this->view_data['search'] = $search;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $this->view_data['page'] = 'company/add';
        $this->view_data['meta_title'] = 'Add Company';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = session()->get('token');
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        return redirect()->to('company');
    }

    public function edit($id)
    {
        $this->view_data['page'] = 'company/edit';
        $this->view_data['meta_title'] = 'Edit Company';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['company'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        return redirect()->to('company');
    }

    public function delete($id)
    {
        return redirect()->to('company');
    }
}
