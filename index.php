<?php

/**
 * CodeIgniter 4 Entry Point
 *
 * This file serves as the main entry point for CI4
 * CI3 backup available in: ci3_backup/index.php.ci3
 */

// Set the working directory to public folder
chdir(__DIR__ . DIRECTORY_SEPARATOR . 'public');

// Require the CI4 front controller
require __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
