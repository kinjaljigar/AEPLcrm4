# AEPL CRM - CodeIgniter 4 Migration Project

## Overview

This project is in the process of migrating from **CodeIgniter 3** to **CodeIgniter 4**. This document serves as the main entry point for understanding the migration status and next steps.

---

## Current Status

**Migration Progress: ~25% Complete**

### What's Done:
- ✅ CI4 Framework installed and configured
- ✅ Database connection configured
- ✅ Authentication system migrated
- ✅ Authorization library migrated
- ✅ Custom helpers migrated
- ✅ BaseController with auth logic created
- ✅ Sample models created (User, Message)
- ✅ Comprehensive documentation created

### What's Remaining:
- ⏳ 13+ models to migrate
- ⏳ 9+ controllers to migrate
- ⏳ All views to migrate
- ⏳ Routes configuration
- ⏳ REST API adapter
- ⏳ Testing

---

## Documentation

### Start Here:
1. **[MIGRATION_PROGRESS.md](MIGRATION_PROGRESS.md)** - Overall progress, next steps, and recommendations
2. **[MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)** - Detailed CI3 to CI4 migration guide with examples
3. **[CI3_TO_CI4_QUICK_REFERENCE.md](CI3_TO_CI4_QUICK_REFERENCE.md)** - Quick lookup for common code conversions

---

## Directory Structure

### CodeIgniter 3 (Current Production):
```
application/
├── config/          → Configuration files
├── controllers/     → 15+ controllers
├── models/          → 15+ models
├── views/           → All view files
├── libraries/       → Custom libraries (Authorization, General)
└── helpers/         → Custom helpers (custom_helper, JwtHelper)
```

### CodeIgniter 4 (Migration In Progress):
```
app/
├── Config/          → Configuration files
│   ├── Database.php     ✅ Configured
│   ├── App.php          ✅ Updated with cliBaseUrl
│   └── Routes.php       ⏳ To be configured
├── Controllers/     → Controllers
│   ├── BaseController.php   ✅ With authentication
│   └── Home.php             ✅ Default (needs customization)
├── Models/          → Models
│   ├── UserModel.php        ✅ Completed
│   └── MessageModel.php     ✅ Completed
├── Views/           → View files (⏳ to be migrated)
├── Libraries/       → Custom libraries
│   └── Authorization.php    ✅ Completed
└── Helpers/         → Custom helpers
    └── custom_helper.php    ✅ Completed
```

---

## System Requirements

- **PHP:** 8.2+ (Currently using 8.2.12) ✅
- **Database:** MySQL (localhost/aashir) ✅
- **Composer:** Latest version ✅
- **Web Server:** Apache with mod_rewrite ✅

---

## Quick Start

### Running CI4 Development Server:
```bash
cd e:\xampp\htdocs\AEPLcrm4
php spark serve
```
Then visit: http://localhost:8080

### Testing CI4 Application:
```bash
# List all routes
php spark routes

# Clear cache
php spark cache:clear

# Run database migrations (when available)
php spark migrate
```

---

## Key Files Migrated

### Libraries:
| CI3 | CI4 | Status |
|-----|-----|--------|
| [application/libraries/Authorization.php](application/libraries/Authorization.php) | [app/Libraries/Authorization.php](app/Libraries/Authorization.php) | ✅ Complete |

### Helpers:
| CI3 | CI4 | Status |
|-----|-----|--------|
| [application/helpers/custom_helper.php](application/helpers/custom_helper.php) | [app/Helpers/custom_helper.php](app/Helpers/custom_helper.php) | ✅ Complete |

### Models:
| CI3 | CI4 | Status |
|-----|-----|--------|
| [application/models/User_model.php](application/models/User_model.php) | [app/Models/UserModel.php](app/Models/UserModel.php) | ✅ Complete |
| [application/models/Message_model.php](application/models/Message_model.php) | [app/Models/MessageModel.php](app/Models/MessageModel.php) | ✅ Complete |
| application/models/Project_model.php | app/Models/ProjectModel.php | ⏳ Pending |
| application/models/Task_model.php | app/Models/TaskModel.php | ⏳ Pending |
| application/models/Leave_model.php | app/Models/LeaveModel.php | ⏳ Pending |
| *...and 10+ more* | | ⏳ Pending |

### Controllers:
| CI3 | CI4 | Status |
|-----|-----|--------|
| - | [app/Controllers/BaseController.php](app/Controllers/BaseController.php) | ✅ Complete |
| [application/controllers/Home.php](application/controllers/Home.php) | app/Controllers/Home.php | ⏳ Pending |
| [application/controllers/Api.php](application/controllers/Api.php) | app/Controllers/Api.php | ⏳ Pending |
| *...and 7+ more* | | ⏳ Pending |

---

## Configuration Changes

### Database Configuration:
- **File:** [app/Config/Database.php](app/Config/Database.php)
- **Database:** aashir
- **Host:** localhost
- **User:** root
- **Password:** (empty)
- **Driver:** MySQLi

### App Configuration:
- **File:** [app/Config/App.php](app/Config/App.php)
- **Base URL:** http://localhost/AEPLcrm4/public/
- **CLI Base URL:** http://localhost/AEPLcrm4/public/v1/
- **Timezone:** Asia/Kolkata

---

## Migration Workflow

### For Each Model:

1. **Copy template:**
   ```bash
   cp app/Models/UserModel.php app/Models/NewModel.php
   ```

2. **Update class name and table:**
   ```php
   class NewModel extends Model
   {
       protected $table = 'aa_table_name';
       protected $primaryKey = 'id_field';
       protected $allowedFields = ['field1', 'field2', ...];
   }
   ```

3. **Migrate methods:**
   - Convert CI3 query builder to CI4
   - Use model methods where possible
   - Update method names to camelCase

### For Each Controller:

1. **Create controller file:**
   ```bash
   touch app/Controllers/ControllerName.php
   ```

2. **Add namespace and extend BaseController:**
   ```php
   <?php
   namespace App\Controllers;

   class ControllerName extends BaseController
   {
       // ...
   }
   ```

3. **Update methods:**
   - Remove `$this->load->` calls
   - Use `new ModelName()` for models
   - Use `$this->request` for input
   - Return views: `return view('name', $data);`
   - Return redirects: `return redirect()->to('path');`

### For Routes:

In [app/Config/Routes.php](app/Config/Routes.php):
```php
$routes->get('path', 'Controller::method');
$routes->post('path', 'Controller::method');

// Or grouped:
$routes->group('prefix', function($routes) {
    $routes->get('/', 'Controller::index');
    $routes->get('view/(:num)', 'Controller::view/$1');
});
```

---

## Important Notes

### Security:
1. **Password Hashing:** Currently using MD5
   - ⚠️ **CRITICAL:** MD5 is NOT secure!
   - **TODO:** Migrate to `password_hash()` with bcrypt
   - Plan a gradual migration strategy

2. **CSRF Protection:** Currently disabled
   - Consider enabling for better security

### API:
- Current: Uses `chriskacerguis/codeigniter-restserver`
- CI4 Plan: Create custom API response trait or use native features

### File Uploads:
- Task files path: `uploads/task_files/`
- Task message files: `uploads/task_message_files/`
- Logos: `assets/logos/`

---

## Next Immediate Steps

1. **Migrate ProjectModel** ([application/models/Project_model.php](application/models/Project_model.php))
   - Critical for the application
   - Use UserModel as template

2. **Migrate TaskModel** ([application/models/Task_model.php](application/models/Task_model.php))
   - Core functionality
   - Includes file handling

3. **Migrate Home Controller** ([application/controllers/Home.php](application/controllers/Home.php))
   - Main dashboard
   - Multiple views and methods

4. **Configure Routes** ([app/Config/Routes.php](app/Config/Routes.php))
   - Copy from [application/config/routes.php](application/config/routes.php)
   - Convert to CI4 format

---

## Testing Strategy

1. **Unit Tests:** Create tests for models
2. **Integration Tests:** Test controller methods
3. **Manual Testing:** Test UI and workflows
4. **Parallel Running:** Keep CI3 running while testing CI4

---

## Resources

### CodeIgniter 4 Documentation:
- Official Docs: https://codeigniter.com/user_guide/
- Forum: https://forum.codeigniter.com/
- GitHub: https://github.com/codeigniter4/CodeIgniter4

### Project Documentation:
- [MIGRATION_PROGRESS.md](MIGRATION_PROGRESS.md) - Detailed progress and next steps
- [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) - Comprehensive migration guide
- [CI3_TO_CI4_QUICK_REFERENCE.md](CI3_TO_CI4_QUICK_REFERENCE.md) - Quick reference guide

---

## Questions?

If you have questions about the migration:
1. Check the documentation files above
2. Refer to CI4 official documentation
3. Look at the migrated files for examples:
   - [app/Models/UserModel.php](app/Models/UserModel.php)
   - [app/Controllers/BaseController.php](app/Controllers/BaseController.php)
   - [app/Libraries/Authorization.php](app/Libraries/Authorization.php)

---

## Contributors

- Migration started: 2026-02-06
- PHP Version: 8.2.12
- CI4 Version: 4.7

---

**Ready to continue? Start with [MIGRATION_PROGRESS.md](MIGRATION_PROGRESS.md) for the next steps!**
