# AEPL CRM Architecture - CI3 to CI4 Migration

## Current Setup (After Migration to CI4 Default)

```
┌─────────────────────────────────────────────────────────────┐
│                     User Browser                            │
│              http://localhost/AEPLcrm4/                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    Apache / .htaccess                       │
│         (Rewrites URLs, removes index.php)                  │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                   Root index.php                            │
│         (Redirects to public/index.php)                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              public/index.php (CI4)                         │
│                  Front Controller                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  CodeIgniter 4 Bootstrap                    │
│              (Loads framework & routing)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
          ┌──────────────┴──────────────┐
          ▼                             ▼
┌──────────────────┐         ┌──────────────────┐
│   app/Config/    │         │  app/Controllers/│
│   Routes.php     │────────▶│  YourController  │
│  (URL Routing)   │         │                  │
└──────────────────┘         └────────┬─────────┘
                                      │
                     ┌────────────────┼────────────────┐
                     ▼                ▼                ▼
          ┌─────────────┐   ┌─────────────┐  ┌─────────────┐
          │ app/Models/ │   │app/Libraries│  │ app/Helpers/│
          │  UserModel  │   │Authorization│  │custom_helper│
          │MessageModel │   │             │  │             │
          └──────┬──────┘   └─────────────┘  └─────────────┘
                 │
                 ▼
          ┌─────────────┐
          │  Database   │
          │   (MySQL)   │
          │   aashir    │
          └─────────────┘
```

## Directory Structure Comparison

### Before (CI3 Only)
```
AEPLcrm4/
├── index.php                    ← CI3 Front Controller
├── .htaccess                    ← CI3 Routing
├── application/                 ← CI3 App (ACTIVE)
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── libraries/
│   └── helpers/
└── system/                      ← CI3 Core
```

### After (CI4 Default, CI3 Backup)
```
AEPLcrm4/
├── index.php                    ← CI4 Redirector (NEW)
├── .htaccess                    ← CI4 Routing (UPDATED)
│
├── app/                         ← CI4 App (ACTIVE) ★
│   ├── Config/
│   │   ├── App.php             ← Base URL, Environment
│   │   ├── Database.php        ← DB Connection
│   │   └── Routes.php          ← URL Routing
│   │
│   ├── Controllers/
│   │   ├── BaseController.php  ← With Authentication ✅
│   │   └── Home.php            ← Default (needs custom)
│   │
│   ├── Models/
│   │   ├── UserModel.php       ← User Management ✅
│   │   └── MessageModel.php    ← Messages ✅
│   │
│   ├── Libraries/
│   │   └── Authorization.php   ← Role-Based Access ✅
│   │
│   ├── Helpers/
│   │   └── custom_helper.php   ← Custom Functions ✅
│   │
│   └── Views/                   ← Views (empty, to migrate)
│
├── public/                      ← CI4 Public Assets ★
│   ├── index.php               ← CI4 Front Controller
│   ├── .htaccess
│   ├── assets/                 ← CSS, JS, Images
│   └── uploads/                ← File Uploads
│
├── application/                 ← CI3 App (BACKUP)
│   ├── config/
│   ├── controllers/            ← 15+ controllers to migrate
│   ├── models/                 ← 15+ models to migrate
│   ├── views/                  ← All views to migrate
│   ├── libraries/
│   └── helpers/
│
├── ci3_backup/                  ← CI3 Backups (NEW) ★
│   ├── index.php.ci3
│   ├── .htaccess.ci3
│   └── run_ci3.php
│
├── system/                      ← CI3 Core (kept for reference)
└── vendor/                      ← Composer Dependencies
```

## Request Flow

### CI4 Request Flow (Current)
```
1. User requests: http://localhost/AEPLcrm4/home/login
                         ↓
2. Apache .htaccess intercepts
                         ↓
3. Routes to: index.php/home/login
                         ↓
4. Root index.php redirects to: public/index.php
                         ↓
5. CI4 Front Controller (public/index.php) loads
                         ↓
6. Routes.php maps: home/login → Home::login
                         ↓
7. BaseController::initController() runs
    - Loads helpers (url, custom, form)
    - Initializes session
    - Loads Authorization library
    - Checks authentication
                         ↓
8. Home::login() executes
                         ↓
9. View rendered and returned to user
```

### CI3 Request Flow (Backup Only)
```
1. User requests: http://localhost/AEPLcrm4/ci3_backup/run_ci3.php
                         ↓
2. run_ci3.php loads: ci3_backup/index.php.ci3
                         ↓
3. CI3 Front Controller loads
                         ↓
4. Routes to: application/controllers/Home.php
                         ↓
5. CI3 Controller executes
                         ↓
6. View loaded from: application/views/
                         ↓
7. Response returned
```

## Authentication Flow (CI4)

```
┌──────────────────────────────────────────────────────────┐
│                    User Visits Page                      │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│          BaseController::initController()                │
│                 (Every Request)                          │
└────────────────────────┬─────────────────────────────────┘
                         │
                    ┌────┴────┐
                    │ Method  │
                    │  Name?  │
                    └────┬────┘
                         │
           ┌─────────────┼─────────────┐
           ▼             ▼             ▼
     ┌──────────┐  ┌──────────┐  ┌──────────┐
     │  Open    │  │ Protected│  │Protected │
     │ Methods  │  │ Methods  │  │ Methods  │
     │(login,   │  │ (normal) │  │ (admin)  │
     │ logout)  │  │          │  │          │
     └────┬─────┘  └────┬─────┘  └────┬─────┘
          │             │               │
          │        ┌────▼────┐     ┌────▼────┐
          │        │ Session │     │ Session │
          │        │ Valid?  │     │ Valid?  │
          │        └────┬────┘     └────┬────┘
          │             │               │
          │         ┌───┴───┐       ┌───┴───┐
          │         │  Yes  │       │  Yes  │
          │         └───┬───┘       └───┬───┘
          │             │               │
          │             ▼               ▼
          │    ┌──────────────┐  ┌──────────────┐
          │    │Authorization │  │Authorization │
          │    │ Check Role   │  │ Check Admin  │
          │    └────┬─────────┘  └────┬─────────┘
          │         │                  │
          │     ┌───┴───┐          ┌───┴───┐
          │     │ Pass  │          │ Pass  │
          │     └───┬───┘          └───┬───┘
          │         │                  │
          └─────────┴──────────────────┴──────────┐
                                                   │
                                                   ▼
                                          ┌──────────────┐
                                          │   Execute    │
                                          │   Method     │
                                          └──────────────┘

If Authorization Fails:
    └──────▶ Redirect to home/tasks or home/login
```

## Role-Based Access Control

```
┌────────────────────────────────────────────────────────────┐
│                     User Roles                             │
│                  (Hierarchy)                               │
└────────────────────────────────────────────────────────────┘

    Super Admin (100)        ← Full Access
         │
         ▼
    Master Admin (90)        ← Admin Functions
         │
         ▼
    Bim Head (80)           ← Department Head
         │
         ▼
    Project Leader (50)      ← Project Management
         │
         ▼
    TaskCoordinator (40)     ← Task Coordination
         │
         ▼
    Employee (10)            ← Basic Access


Access Matrix:

┌─────────────────┬───────┬────────┬─────────┬─────────┬──────────┬──────────┐
│ Feature         │ Super │ Master │ Bim     │ Project │ Task     │ Employee │
│                 │ Admin │ Admin  │ Head    │ Leader  │ Coord    │          │
├─────────────────┼───────┼────────┼─────────┼─────────┼──────────┼──────────┤
│ View Dashboard  │  ✅   │  ✅    │  ✅     │  ✅     │  ✅      │  ✅      │
│ Manage Projects │  ✅   │  ✅    │  ✅     │  ✅     │  ❌      │  ❌      │
│ Approve Leaves  │  ✅   │  ✅    │  ✅     │  ✅     │  ❌      │  ❌      │
│ View All Data   │  ✅   │  ✅    │  ✅     │  ❌     │  ❌      │  ❌      │
│ Salary Reports  │  ✅   │  ✅    │  ❌     │  ❌     │  ❌      │  ❌      │
│ System Settings │  ✅   │  ❌    │  ❌     │  ❌     │  ❌      │  ❌      │
└─────────────────┴───────┴────────┴─────────┴─────────┴──────────┴──────────┘
```

## Database Schema (Simplified)

```
┌──────────────────┐
│    aa_users      │
├──────────────────┤
│ u_id (PK)        │
│ u_name           │
│ u_username       │
│ u_password       │◄─────── MD5 (⚠️ Upgrade to bcrypt recommended)
│ u_type           │◄─────── Role (Super Admin, Master Admin, etc.)
│ u_status         │
│ u_leader         │
│ u_department     │
└────────┬─────────┘
         │
         │ 1:N
         ▼
┌──────────────────┐
│   aa_messages    │
├──────────────────┤
│ m_id (PK)        │
│ m_from_u_id (FK) │
│ m_to_u_id (FK)   │
│ m_message        │
│ mu_read          │
└──────────────────┘

┌──────────────────┐
│  aa_projects     │
├──────────────────┤
│ p_id (PK)        │
│ p_name           │
│ p_leader         │
│ p_status         │
└──────────────────┘

┌──────────────────┐
│   aa_tasks       │
├──────────────────┤
│ t_id (PK)        │
│ t_p_id (FK)      │
│ t_name           │
│ t_status         │
└──────────────────┘

... and 10+ more tables
```

## File Organization

### CI4 Namespace Structure
```
App\
├── Controllers\
│   └── Home.php           → namespace App\Controllers
├── Models\
│   ├── UserModel.php      → namespace App\Models
│   └── MessageModel.php   → namespace App\Models
├── Libraries\
│   └── Authorization.php  → namespace App\Libraries
└── Helpers\
    └── custom_helper.php  → Functions (no namespace)
```

### Autoloading (PSR-4)
```
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Config\\": "app/Config/"
    }
}
```

## Migration Status Visual

```
CodeIgniter 3                   CodeIgniter 4
(Original/Backup)               (Active/Default)

┌─────────────────┐            ┌─────────────────┐
│ 15+ Controllers │─ ─ ─ ─ ─ ─▶│  1 Controller   │
│   ✅ Working    │  Migrate    │  ✅ Base        │
│                 │  Required   │  ⏳ Others      │
└─────────────────┘            └─────────────────┘

┌─────────────────┐            ┌─────────────────┐
│  15+ Models     │─ ─ ─ ─ ─ ─▶│  2 Models       │
│   ✅ Working    │  Migrate    │  ✅ User        │
│                 │  Required   │  ✅ Message     │
└─────────────────┘            └─────────────────┘

┌─────────────────┐            ┌─────────────────┐
│  All Views      │─ ─ ─ ─ ─ ─▶│  No Views       │
│   ✅ Working    │  Migrate    │  ⏳ Pending     │
│                 │  Required   │                 │
└─────────────────┘            └─────────────────┘

┌─────────────────┐            ┌─────────────────┐
│  Routes Config  │─ ─ ─ ─ ─ ─▶│  Default Only   │
│   ✅ Working    │  Configure  │  ⏳ Pending     │
│                 │  Required   │                 │
└─────────────────┘            └─────────────────┘

┌─────────────────┐            ┌─────────────────┐
│  Libraries      │────────────▶│  Libraries      │
│   ✅ Working    │  ✅ Done    │  ✅ Migrated    │
└─────────────────┘            └─────────────────┘

┌─────────────────┐            ┌─────────────────┐
│  Helpers        │────────────▶│  Helpers        │
│   ✅ Working    │  ✅ Done    │  ✅ Migrated    │
└─────────────────┘            └─────────────────┘
```

---

**Current Status: CI4 is Default, ~25% Migration Complete**
**Next Steps: See MIGRATION_PROGRESS.md**
