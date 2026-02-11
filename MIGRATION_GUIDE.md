# CodeIgniter 3 to CodeIgniter 4 Migration Guide - AEPL CRM

## Overview
This document outlines the migration process from CodeIgniter 3 to CodeIgniter 4 for the AEPL CRM system.

## System Requirements
- PHP 8.2+  (Currently using PHP 8.2.12)
- MySQL Database: aashir
- Composer

## Key Changes from CI3 to CI4

### 1. Namespace Support
CI4 uses namespaces. All classes should be namespaced under `App\`.

### 2. Controllers
**CI3:**
```php
class Home extends CI_Controller {
    public function index() {
        $this->load->view('home');
    }
}
```

**CI4:**
```php
namespace App\Controllers;

class Home extends BaseController {
    public function index() {
        return view('home');
    }
}
```

### 3. Models
**CI3:**
```php
class User_model extends CI_Model {
    public function get_users() {
        return $this->db->get('users')->result_array();
    }
}
```

**CI4:**
```php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model {
    protected $table = 'aa_users';
    protected $primaryKey = 'u_id';

    public function getUsers() {
        return $this->findAll();
    }
}
```

### 4. Loading Libraries/Models
**CI3:**
```php
$this->load->model('user_model');
$this->load->library('session');
```

**CI4:**
```php
$userModel = new \App\Models\UserModel();
$session = session(); // or service('session')
```

### 5. Input Class
**CI3:**
```php
$name = $this->input->post('name');
$id = $this->input->get('id');
```

**CI4:**
```php
$request = $this->request;
$name = $request->getPost('name');
$id = $request->getGet('id');
```

### 6. Database Queries
**CI3:**
```php
$this->db->where('u_id', $id);
$this->db->get('users');
```

**CI4:**
```php
$db = \Config\Database::connect();
$builder = $db->table('users');
$builder->where('u_id', $id);
$builder->get();
```

### 7. Sessions
**CI3:**
```php
$this->session->userdata('admin_session');
$this->session->set_userdata(['key' => 'value']);
```

**CI4:**
```php
$session = session();
$session->get('admin_session');
$session->set(['key' => 'value']);
```

### 8. URL Helpers
**CI3:**
```php
redirect(base_url('home/login'));
```

**CI4:**
```php
return redirect()->to(base_url('home/login'));
```

### 9. Validation
**CI3:**
```php
$this->load->library('form_validation');
$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
if ($this->form_validation->run()) {
    // validated
}
```

**CI4:**
```php
$validation = \Config\Services::validation();
$validation->setRules([
    'email' => 'required|valid_email'
]);
if ($validation->run($data)) {
    // validated
}
```

### 10. Routes
**CI3 (application/config/routes.php):**
```php
$route['conference'] = 'ConferenceController/listView';
$route['conference/edit/(:num)'] = 'ConferenceController/edit/$1';
```

**CI4 (app/Config/Routes.php):**
```php
$routes->get('conference', 'ConferenceController::listView');
$routes->get('conference/edit/(:num)', 'ConferenceController::edit/$1');
```

## Migration Steps

### Phase 1: Setup (COMPLETED)
- [x] Install CI4 via Composer
- [x] Configure database settings
- [x] Set up environment

### Phase 2: Core Components
- [ ] Migrate custom libraries (Authorization, General)
- [ ] Migrate custom helpers (custom_helper, JwtHelper)
- [ ] Create BaseController with authentication logic

### Phase 3: Controllers Migration
Controllers to migrate:
1. Home.php - Main dashboard controller
2. Api.php - REST API controller (uses JWT)
3. ConferenceController.php
4. ScheduleController.php
5. CompanyController.php
6. TicketController.php
7. TicketCategoryController.php
8. TicketMessageController.php
9. AssociateUserTaskController.php

### Phase 4: Models Migration
Models to migrate:
1. User_model.php → UserModel.php
2. Project_model.php → ProjectModel.php
3. Task_model.php → TaskModel.php
4. Leave_model.php → LeaveModel.php
5. Holiday_model.php → HolidayModel.php
6. Timesheet_model.php → TimesheetModel.php
7. Message_model.php → MessageModel.php
8. Dependency_model.php → DependencyModel.php
9. Weeklywork_model.php → WeeklyworkModel.php
10. Settings_model.php → SettingsModel.php
11. TicketModel.php
12. Ticketcategory_model.php → TicketCategoryModel.php
13. TicketMessageModel.php
14. ProjectTaskModel.php
15. Project_message_model.php → ProjectMessageModel.php

### Phase 5: Views Migration
- Migrate all views to app/Views/
- Update view syntax for CI4

### Phase 6: Routes Migration
- Migrate all routes from application/config/routes.php to app/Config/Routes.php

### Phase 7: Testing
- Test all functionality
- Fix any bugs or compatibility issues

## Important Notes

### REST API
The current application uses `chriskacerguis/codeigniter-restserver` for CI3. For CI4, we'll need to:
1. Use CI4's native API response features
2. Migrate JWT authentication
3. Create custom API response traits

### Custom Libraries
- **Authorization**: Handles role-based access control
- **General**: Contains utility methods

These will be migrated to CI4 libraries or services.

### Session Management
CI4 handles sessions differently. The admin_session logic needs to be adapted.

### Database Table Prefix
Tables use the `aa_` prefix (e.g., aa_users, aa_projects, aa_tasks)

## Next Steps
1. Migrate Authorization and General libraries
2. Create BaseController with authentication
3. Migrate one controller as a template (e.g., Home)
4. Use the template to migrate remaining controllers
5. Migrate models
6. Migrate views
7. Test thoroughly

## Compatibility Notes
- CI4 requires PHP 8.2+ (we're using 8.2.12 - OK)
- CI4 uses strict types and return type declarations
- CI4 uses PSR-4 autoloading
- Model class names should use PascalCase without underscores
