<?php

namespace App\Controllers;

class CompanyUser extends BaseController
{
    public function index()
    {
        $request = service('request');
        $search  = $request->getPost('search') ?? '';

        $endpoint = $search
            ? 'company/user/list?search=' . urlencode($search)
            : 'company/user/list';

        $result      = $this->callExternalApi($endpoint);
        $decoded     = json_decode($result['body'], true) ?: [];
        $companyUsers = $decoded ?? [];

        $this->view_data['page']          = 'company/user/list';
        $this->view_data['meta_title']    = 'Associate Users';
        $this->view_data['admin_session'] = $this->admin_session;
        $this->view_data['authorization'] = $this->authorization;
        $this->view_data['companyUsers']  = $companyUsers;
        $this->view_data['search']        = $search;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function add()
    {
        // Fetch companies and projects for dropdowns
        $compResult = $this->callExternalApi('company/list?page=1&limit=1000');
        $compDecoded = json_decode($compResult['body'], true) ?: [];
        $available_companies = $compDecoded['data'] ?? [];

        $projResult = $this->callExternalApi('projectlist?page=1&limit=10000');
        $projDecoded = json_decode($projResult['body'], true) ?: [];
        $available_projects = $projDecoded['data'] ?? [];

        $this->view_data['page']                 = 'company/user/add';
        $this->view_data['meta_title']           = 'Add Company User';
        $this->view_data['admin_session']        = $this->admin_session;
        $this->view_data['authorization']        = $this->authorization;
        $this->view_data['token']                = $this->token ?? '';
        $this->view_data['available_companies']  = $available_companies;
        $this->view_data['available_projects']   = $available_projects;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function addData()
    {
        $projectIds = $this->request->getPost('project_ids') ?? [];
        $data = [
            'name'        => $this->request->getPost('name'),
            'mobile'      => $this->request->getPost('mobile'),
            'username'    => $this->request->getPost('username'),
            'email'       => $this->request->getPost('email'),
            'password'    => $this->request->getPost('password'),
            'status'      => $this->request->getPost('status'),
            'company_id'  => $this->request->getPost('company_id'),
            'project_ids' => is_array($projectIds) ? implode(',', $projectIds) : $projectIds,
        ];

        $result  = $this->callExternalApi('company/add-user', 'POST', $data);
        $decoded = json_decode($result['body'], true) ?: [];

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('companyuser');
        }
        session()->setFlashdata('error_message', $decoded['message'] ?? 'Failed to add company user.');
        return redirect()->to('companyuser/add');
    }

    public function edit($id)
    {
        $result  = $this->callExternalApi('company/edit/user/' . $id);
        $decoded = json_decode($result['body'], true) ?: [];
        $companyUser = $decoded['data'][0] ?? $decoded['data'] ?? [];

        $compResult = $this->callExternalApi('company/list?page=1&limit=1000');
        $compDecoded = json_decode($compResult['body'], true) ?: [];
        $available_companies = $compDecoded['data'] ?? [];

        $projResult = $this->callExternalApi('projectlist?page=1&limit=10000');
        $projDecoded = json_decode($projResult['body'], true) ?: [];
        $available_projects = $projDecoded['data'] ?? [];

        $this->view_data['page']                = 'company/user/edit';
        $this->view_data['meta_title']          = 'Edit Company User';
        $this->view_data['admin_session']       = $this->admin_session;
        $this->view_data['authorization']       = $this->authorization;
        $this->view_data['companyUser']         = $companyUser;
        $this->view_data['available_companies'] = $available_companies;
        $this->view_data['available_projects']  = $available_projects;
        return view('template', ['view_data' => $this->view_data]);
    }

    public function update($id)
    {
        $projectIds = $this->request->getPost('project_ids') ?? [];
        $data = [
            'name'        => $this->request->getPost('name'),
            'mobile'      => $this->request->getPost('mobile'),
            'username'    => $this->request->getPost('username'),
            'email'       => $this->request->getPost('email'),
            'password'    => $this->request->getPost('password'),
            'status'      => $this->request->getPost('status'),
            'company_id'  => $this->request->getPost('company_id'),
            'project_ids' => is_array($projectIds) ? implode(',', $projectIds) : $projectIds,
        ];

        $result  = $this->callExternalApi('company/update/user/' . $id, 'PUT', $data);
        $decoded = json_decode($result['body'], true) ?: [];

        if (($decoded['status'] ?? '') == 200 || $result['code'] == 200) {
            return redirect()->to('companyuser');
        }
        session()->setFlashdata('error_message', $decoded['message'] ?? 'Failed to update company user.');
        return redirect()->to('companyuser/edit/' . $id);
    }

    public function delete($id)
    {
        $this->callExternalApi('company/delete/user/' . $id, 'DELETE');
        return redirect()->to('companyuser');
    }
}
