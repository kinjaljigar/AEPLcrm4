<?php
/**
 * CodeIgniter 3 Access Script
 *
 * This script allows you to run the old CI3 version for reference or emergency fallback
 * Access via: http://localhost/AEPLcrm4/ci3_backup/run_ci3.php
 *
 * IMPORTANT: This is for backup/reference only. CI4 is now the default.
 */

// Set CI3 as the active version temporarily
define('CI3_ACTIVE', true);

// Change directory to root
chdir(__DIR__ . '/..');

// Include the backed up CI3 index
require __DIR__ . '/index.php.ci3';
