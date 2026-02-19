<?php

namespace App\Controllers;

class Company extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search  = $request->getPost('search') ?? '';

        $endpoint = $search
            ? 'company/list?search=' . urlencode($search) . '&page=1&limit=1000'
            : 'company/list?page=1&limit=1000';

        $result    = $this->callExternalApi($endpoint);
        $decoded   = json_decode($result['body'], true);
        $companies = $decoded ?? [];

        $this->view_data['page']          = 'company/list';
        $this->view_data['meta_title']    = 'Companies';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['companies']     = $companies;
        $this->view_data['search']        = $search;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        $this->view_data['page']          = 'company/add';
        $this->view_data['meta_title']    = 'Add Company';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['token']         = $this->token ?? '';
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'address'      => $this->request->getPost('address'),
            'status'       => $this->request->getPost('status'),
        ];

        $result  = $this->callExternalApi('company/add', 'POST', $data);
        $decoded = json_decode($result['body'], true);

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('company');
        }
        session()->setFlashdata('error', $decoded['message'] ?? 'Failed to add company.');
        return redirect()->to('company/add');
    }

    public function edit($id)
    {
        $result  = $this->callExternalApi('company/edit/' . $id);
        $decoded = json_decode($result['body'], true);
        $company = $decoded ?? [];

        $this->view_data['page']          = 'company/edit';
        $this->view_data['meta_title']    = 'Edit Company';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['company']       = $company;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'address'      => $this->request->getPost('address'),
            'status'       => $this->request->getPost('status'),
        ];

        $result  = $this->callExternalApi('company/update/' . $id, 'PUT', $data);
        $decoded = json_decode($result['body'], true);

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('company');
        }
        session()->setFlashdata('error', $decoded['message'] ?? 'Failed to update company.');
        return redirect()->to('company/edit/' . $id);
    }

    public function delete($id)
    {
        $this->callExternalApi('company/delete/' . $id, 'DELETE');
        return redirect()->to('company');
    }
}
