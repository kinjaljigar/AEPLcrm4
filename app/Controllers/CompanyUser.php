<?php

namespace App\Controllers;

class CompanyUser extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search = $request->getPost('search') ?? '';

        $this->view_data['page'] = 'company/user/list';
        $this->view_data['meta_title'] = 'Associate Users';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['companyUsers'] = [];
        $this->view_data['search'] = $search;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $this->view_data['page'] = 'company/user/add';
        $this->view_data['meta_title'] = 'Add Company User';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token'] = session()->get('token');
        $this->view_data['available_companies'] = [];
        $this->view_data['available_projects'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        return redirect()->to('companyuser');
    }

    public function edit($id)
    {
        $this->view_data['page'] = 'company/user/edit';
        $this->view_data['meta_title'] = 'Edit Company User';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['companyUser'] = [];
        $this->view_data['available_companies'] = [];
        $this->view_data['available_projects'] = [];
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        return redirect()->to('companyuser');
    }

    public function delete($id)
    {
        return redirect()->to('companyuser');
    }
}
