# CodeIgniter 3 to CodeIgniter 4 Quick Reference

## Common Code Conversions

### Loading Models

#### CI3:
```php
$this->load->model('user_model');
$users = $this->user_model->get_records();
```

#### CI4:
```php
$userModel = new \App\Models\UserModel();
$users = $userModel->getRecords();
```

---

### Loading Libraries

#### CI3:
```php
$this->load->library('session');
$this->load->library('authorization');
```

#### CI4:
```php
$session = session(); // or service('session')
$authorization = new \App\Libraries\Authorization();
```

---

### Session

#### CI3:
```php
$this->session->userdata('key');
$this->session->set_userdata(['key' => 'value']);
$this->session->unset_userdata('key');
```

#### CI4:
```php
$session = session();
$session->get('key');
$session->set(['key' => 'value']);
$session->remove('key');
```

---

### Input (POST/GET)

#### CI3:
```php
$name = $this->input->post('name');
$id = $this->input->get('id');
$ip = $this->input->ip_address();
$method = $this->input->method();
```

#### CI4:
```php
$request = $this->request;
$name = $request->getPost('name');
$id = $request->getGet('id');
$ip = $request->getIPAddress();
$method = $request->getMethod();
```

---

### Database Queries

#### CI3:
```php
// Select
$this->db->select('*');
$this->db->from('aa_users');
$this->db->where('u_id', $id);
$query = $this->db->get();
$results = $query->result_array();

// Insert
$this->db->insert('aa_users', $data);
$insert_id = $this->db->insert_id();

// Update
$this->db->where('u_id', $id);
$this->db->update('aa_users', $data);

// Delete
$this->db->where('u_id', $id);
$this->db->delete('aa_users');
```

#### CI4:
```php
// Using Query Builder
$db = \Config\Database::connect();
$builder = $db->table('aa_users');

// Select
$builder->select('*');
$builder->where('u_id', $id);
$query = $builder->get();
$results = $query->getResultArray();

// Insert
$builder->insert($data);
$insert_id = $db->insertID();

// Update
$builder->where('u_id', $id);
$builder->update($data);

// Delete
$builder->where('u_id', $id);
$builder->delete();

// Or using Model (Recommended)
$userModel = new \App\Models\UserModel();
$user = $userModel->find($id);
$userModel->insert($data);
$userModel->update($id, $data);
$userModel->delete($id);
```

---

### Loading Views

#### CI3:
```php
$data['title'] = 'My Title';
$this->load->view('template', $data);
```

#### CI4:
```php
$data['title'] = 'My Title';
return view('template', $data);
```

---

### Redirects

#### CI3:
```php
redirect(base_url('home/login'));
```

#### CI4:
```php
return redirect()->to(base_url('home/login'));
// or
return redirect()->to('home/login');
```

---

### Form Validation

#### CI3:
```php
$this->load->library('form_validation');
$this->form_validation->set_rules('email', 'Email', 'required|valid_email');

if ($this->form_validation->run()) {
    // Valid
} else {
    // Invalid
    echo validation_errors();
}
```

#### CI4:
```php
$validation = \Config\Services::validation();
$validation->setRules([
    'email' => 'required|valid_email'
]);

if ($validation->run($data)) {
    // Valid
} else {
    // Invalid
    $errors = $validation->getErrors();
}
```

---

### Config Items

#### CI3:
```php
$config_value = $this->config->item('base_url');
$config_value = config_item('base_url');
```

#### CI4:
```php
$config = config('App');
$config_value = $config->baseURL;
```

---

### Helpers

#### CI3:
```php
$this->load->helper('url');
$this->load->helper('custom');
```

#### CI4:
```php
helper('url');
helper('custom');
// Or load in BaseController
```

---

### URL Helper Functions

#### CI3:
```php
base_url('path');
site_url('path');
current_url();
uri_string();
```

#### CI4:
```php
base_url('path');
site_url('path');
current_url();
uri_string();
// Same functions work in CI4!
```

---

### File Uploads

#### CI3:
```php
$config['upload_path'] = './uploads/';
$config['allowed_types'] = 'gif|jpg|png';
$this->load->library('upload', $config);

if ($this->upload->do_upload('userfile')) {
    $data = $this->upload->data();
} else {
    $error = $this->upload->display_errors();
}
```

#### CI4:
```php
$file = $this->request->getFile('userfile');

if ($file->isValid() && !$file->hasMoved()) {
    $newName = $file->getRandomName();
    $file->move(WRITEPATH . 'uploads', $newName);
} else {
    $error = $file->getErrorString();
}
```

---

### JSON Response

#### CI3:
```php
$this->output
    ->set_content_type('application/json')
    ->set_output(json_encode($data));
```

#### CI4:
```php
return $this->response->setJSON($data);
```

---

### Email

#### CI3:
```php
$this->load->library('email');
$this->email->from('from@example.com');
$this->email->to('to@example.com');
$this->email->subject('Subject');
$this->email->message('Message');
$this->email->send();
```

#### CI4:
```php
$email = \Config\Services::email();
$email->setFrom('from@example.com');
$email->setTo('to@example.com');
$email->setSubject('Subject');
$email->setMessage('Message');
$email->send();
```

---

### Pagination

#### CI3:
```php
$this->load->library('pagination');
$config['base_url'] = base_url('users/index');
$config['total_rows'] = 200;
$config['per_page'] = 20;
$this->pagination->initialize($config);
echo $this->pagination->create_links();
```

#### CI4:
```php
$pager = \Config\Services::pager();
$data = $userModel->paginate(20);
echo $pager->links();
```

---

### Logging

#### CI3:
```php
log_message('error', 'Some error message');
log_message('info', 'Some info message');
```

#### CI4:
```php
log_message('error', 'Some error message');
log_message('info', 'Some info message');
// Same in CI4!
```

---

### Error Handling

#### CI3:
```php
show_error('Error message', 500);
show_404();
```

#### CI4:
```php
throw new \CodeIgniter\Exceptions\PageNotFoundException('Page not found');
throw new \RuntimeException('Error message');
```

---

### Controller Structure

#### CI3:
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function index()
    {
        $data['users'] = $this->user_model->get_records();
        $this->load->view('home', $data);
    }
}
```

#### CI4:
```php
<?php

namespace App\Controllers;

use App\Models\UserModel;

class Home extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['users'] = $this->userModel->getRecords();
        return view('home', $data);
    }
}
```

---

### Model Structure

#### CI3:
```php
<?php
class User_model extends CI_Model
{
    public function get_users()
    {
        return $this->db->get('aa_users')->result_array();
    }

    public function get_user($id)
    {
        return $this->db->get_where('aa_users', ['u_id' => $id])->row_array();
    }

    public function insert_user($data)
    {
        $this->db->insert('aa_users', $data);
        return $this->db->insert_id();
    }
}
```

#### CI4:
```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'aa_users';
    protected $primaryKey = 'u_id';
    protected $allowedFields = ['u_name', 'u_email', 'u_type'];

    public function getUsers()
    {
        return $this->findAll();
    }

    public function getUser($id)
    {
        return $this->find($id);
    }

    public function insertUser($data)
    {
        return $this->insert($data);
    }
}
```

---

### Routes

#### CI3 (application/config/routes.php):
```php
$route['default_controller'] = 'home';
$route['404_override'] = '';

$route['users'] = 'user/index';
$route['users/view/(:num)'] = 'user/view/$1';
$route['users/edit/(:num)'] = 'user/edit/$1';
```

#### CI4 (app/Config/Routes.php):
```php
$routes->get('/', 'Home::index');

$routes->get('users', 'User::index');
$routes->get('users/view/(:num)', 'User::view/$1');
$routes->get('users/edit/(:num)', 'User::edit/$1');
$routes->post('users/update/(:num)', 'User::update/$1');

// Or grouped
$routes->group('users', function($routes) {
    $routes->get('/', 'User::index');
    $routes->get('view/(:num)', 'User::view/$1');
    $routes->get('edit/(:num)', 'User::edit/$1');
    $routes->post('update/(:num)', 'User::update/$1');
});
```

---

## Naming Conventions Changes

| CI3 | CI4 |
|-----|-----|
| `User_model.php` | `UserModel.php` |
| `class User_model` | `class UserModel` |
| `$this->user_model` | `$userModel` or `$this->userModel` |
| Underscores in class names | PascalCase (no underscores) |
| Method names (any case) | camelCase preferred |

---

## Important Differences

### 1. Namespaces
CI4 uses namespaces. All classes must be namespaced:
```php
namespace App\Controllers;
namespace App\Models;
namespace App\Libraries;
```

### 2. Return Values
Controllers in CI4 should **return** views, not echo them:
```php
// CI3
$this->load->view('home');

// CI4
return view('home');
```

### 3. Autoloading
CI3 uses `application/config/autoload.php`.
CI4 uses PSR-4 autoloading via Composer.

### 4. Request/Response
CI4 has HTTP\Request and HTTP\Response objects:
```php
$this->request->getPost('name');
$this->response->setJSON($data);
```

### 5. Filters (replaces Hooks)
CI4 uses Filters instead of Hooks for before/after request processing.

---

## Common Pitfalls

1. **Forgetting `return`** - Controllers must return responses
2. **Class naming** - Must use PascalCase without underscores
3. **Namespace** - Must include proper namespace
4. **$this->db** - Use model methods or query builder
5. **result_array()** - Now `getResultArray()`
6. **row_array()** - Now `getRowArray()`
7. **num_rows()** - Now `getNumRows()` or `countAllResults()`

---

## Migration Checklist for Each File

### For Models:
- [ ] Rename file: `User_model.php` → `UserModel.php`
- [ ] Add namespace: `namespace App\Models;`
- [ ] Extend `CodeIgniter\Model`
- [ ] Define `$table`, `$primaryKey`, `$allowedFields`
- [ ] Convert method names to camelCase
- [ ] Use CI4 query builder methods

### For Controllers:
- [ ] Add namespace: `namespace App\Controllers;`
- [ ] Extend `BaseController`
- [ ] Remove `$this->load->` calls
- [ ] Use `new ModelName()` instead
- [ ] Return views instead of loading them
- [ ] Use `$this->request` for input
- [ ] Return redirects

### For Views:
- [ ] Move to `app/Views/`
- [ ] Update `<?php echo` to `<?=` (optional)
- [ ] Check helper function calls
- [ ] Update form_open() if using

---

This quick reference should help speed up your migration process!
