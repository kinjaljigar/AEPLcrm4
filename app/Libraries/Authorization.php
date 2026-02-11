<?php

namespace App\Libraries;

/**
 * Authorization Library
 * Centralized user access control and permission management for CI4
 */
class Authorization
{
    // Define user role hierarchy (higher number = more privileges)
    private array $role_hierarchy = [
        'Super Admin' => 100,
        'Master Admin' => 90,
        'Bim Head' => 80,
        'Project Leader' => 50,
        'TaskCoordinator' => 40,
        'Employee' => 10
    ];

    // Define page access permissions
    private array $page_permissions = [
        // Pages accessible by Master Admin and above
        'master_pages' => [
            'report_employee_salary',
            'presentlist'
        ],

        // Pages accessible by Project Leaders and above (including Bim Head, Master Admin, Super Admin)
        'project_pages' => [
            'index',
            'projects',
            'employees',
            'report_leave',
            'report_dependency',
            'report_timesheet',
            'report_daily',
            'report_employee',
            'report_estimated_actual',
            'conference',
            'company',
            'companyuser',
            'usertask',
            'project_contacts'
        ],

        // Pages accessible only to specific roles
        'custom_access' => [
            // Add custom rules here
        ]
    ];

    /**
     * Check if user has access to a specific page/method
     *
     * @param string $method - The method/page name to check
     * @param array $user_session - User session data containing u_type
     * @param string $redirect_url - URL to redirect if access denied (default: home/tasks)
     * @return bool - Returns true if has access, redirects if not
     */
    public function check_page_access(string $method, array $user_session, string $redirect_url = 'home/tasks'): bool
    {
        $user_type = $user_session['u_type'] ?? null;

        if (!$user_type) {
            return redirect()->to(base_url($redirect_url));
        }

        // Check master pages
        if (in_array($method, $this->page_permissions['master_pages'])) {
            if (!$this->is_role_allowed($user_type, ['Master Admin', 'Super Admin'])) {
                return redirect()->to(base_url($redirect_url));
            }
        }

        // Check project pages
        if (in_array($method, $this->page_permissions['project_pages'])) {
            // For index method, allow Coordinators too
            if ($method === 'index') {
                if (!$this->is_role_allowed($user_type, ['Super Admin', 'Master Admin', 'Bim Head', 'Project Leader', 'TaskCoordinator'])) {
                    return redirect()->to(base_url($redirect_url));
                }
            } else {
                if (!$this->is_role_allowed($user_type, ['Super Admin', 'Master Admin', 'Bim Head', 'Project Leader'])) {
                    return redirect()->to(base_url($redirect_url));
                }
            }
        }

        return true;
    }

    /**
     * Check if user role is in allowed roles list
     *
     * @param string $user_role - Current user's role
     * @param array $allowed_roles - Array of allowed role names
     * @return bool
     */
    public function is_role_allowed(string $user_role, array $allowed_roles): bool
    {
        return in_array($user_role, $allowed_roles);
    }

    /**
     * Check if user has minimum role level
     *
     * @param string $user_role - Current user's role
     * @param string $minimum_role - Minimum required role
     * @return bool
     */
    public function has_minimum_role(string $user_role, string $minimum_role): bool
    {
        $user_level = $this->role_hierarchy[$user_role] ?? 0;
        $required_level = $this->role_hierarchy[$minimum_role] ?? 0;

        return $user_level >= $required_level;
    }

    /**
     * Check if user is admin (Master Admin or Super Admin)
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function is_admin($user_session_or_type): bool
    {
        $user_type = is_array($user_session_or_type)
            ? ($user_session_or_type['u_type'] ?? null)
            : $user_session_or_type;

        return in_array($user_type, ['Master Admin', 'Super Admin']);
    }

    /**
     * Check if user is super admin
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function is_super_admin($user_session_or_type): bool
    {
        $user_type = is_array($user_session_or_type)
            ? ($user_session_or_type['u_type'] ?? null)
            : $user_session_or_type;

        return $user_type === 'Super Admin';
    }

    /**
     * Check if user is Bim Head or higher
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function is_bim_head_or_higher($user_session_or_type): bool
    {
        $user_type = is_array($user_session_or_type)
            ? ($user_session_or_type['u_type'] ?? null)
            : $user_session_or_type;

        return in_array($user_type, ['Master Admin', 'Bim Head', 'Super Admin']);
    }

    /**
     * Check if user is Bim Head or TaskCoordinator or higher
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function is_bim_head_or_high_taskcoordinator($user_session_or_type): bool
    {
        $user_type = is_array($user_session_or_type)
            ? ($user_session_or_type['u_type'] ?? null)
            : $user_session_or_type;

        return in_array($user_type, ['Master Admin', 'Bim Head', 'Super Admin', 'TaskCoordinator']);
    }

    /**
     * Check if user is Project Leader or higher
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function is_project_leader_or_higher($user_session_or_type): bool
    {
        $user_type = is_array($user_session_or_type)
            ? ($user_session_or_type['u_type'] ?? null)
            : $user_session_or_type;

        return in_array($user_type, ['Master Admin', 'Bim Head', 'Project Leader', 'Super Admin']);
    }

    /**
     * Require specific roles or redirect
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @param array $allowed_roles - Array of allowed role names
     * @param string $redirect_url - URL to redirect if access denied
     * @return bool
     */
    public function require_roles($user_session_or_type, array $allowed_roles, string $redirect_url = 'home/tasks'): bool
    {
        $user_type = is_array($user_session_or_type)
            ? ($user_session_or_type['u_type'] ?? null)
            : $user_session_or_type;

        if (!$this->is_role_allowed($user_type, $allowed_roles)) {
            return redirect()->to(base_url($redirect_url));
        }

        return true;
    }

    /**
     * Get user role level (for comparison)
     *
     * @param string $user_role - User role name
     * @return int - Role level number
     */
    public function get_role_level(string $user_role): int
    {
        return $this->role_hierarchy[$user_role] ?? 0;
    }

    /**
     * Check if user can access project management features
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function can_manage_projects($user_session_or_type): bool
    {
        return $this->is_project_leader_or_higher($user_session_or_type);
    }

    /**
     * Check if user can view all data (not restricted to assigned data)
     *
     * @param array|string $user_session_or_type - User session array or user type string
     * @return bool
     */
    public function can_view_all_data($user_session_or_type): bool
    {
        return $this->is_bim_head_or_higher($user_session_or_type);
    }
}
