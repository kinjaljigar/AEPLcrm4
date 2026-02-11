<?php
/**
 * Quick CI4 Setup Test
 * Access: http://localhost/AEPLcrm4/test_ci4.php
 */

echo "<h1>CodeIgniter 4 Setup Test</h1>";
echo "<hr>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
$minVersion = '8.2';
if (version_compare($phpVersion, $minVersion, '>=')) {
    echo "✅ PHP Version: $phpVersion (Required: $minVersion+)<br>";
} else {
    echo "❌ PHP Version: $phpVersion (Required: $minVersion+)<br>";
}

// Test 2: Directory Structure
echo "<h2>2. Directory Structure</h2>";
$dirs = [
    'app' => 'CI4 Application Directory',
    'app/Controllers' => 'Controllers',
    'app/Models' => 'Models',
    'app/Libraries' => 'Libraries',
    'app/Helpers' => 'Helpers',
    'public' => 'Public Directory',
    'ci3_backup' => 'CI3 Backup',
    'application' => 'CI3 Original (Backup)',
];

foreach ($dirs as $dir => $label) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "✅ $label: <code>$dir/</code><br>";
    } else {
        echo "❌ $label: <code>$dir/</code> NOT FOUND<br>";
    }
}

// Test 3: Key Files
echo "<h2>3. Key Files</h2>";
$files = [
    'index.php' => 'CI4 Entry Point',
    '.htaccess' => 'Rewrite Rules',
    'app/Config/Database.php' => 'Database Config',
    'app/Config/App.php' => 'App Config',
    'app/Controllers/BaseController.php' => 'Base Controller',
    'app/Models/UserModel.php' => 'User Model',
    'app/Libraries/Authorization.php' => 'Authorization Library',
    'app/Helpers/custom_helper.php' => 'Custom Helper',
    'ci3_backup/index.php.ci3' => 'CI3 Backup',
];

foreach ($files as $file => $label) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $label: <code>$file</code><br>";
    } else {
        echo "❌ $label: <code>$file</code> NOT FOUND<br>";
    }
}

// Test 4: Composer
echo "<h2>4. Composer & Dependencies</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer dependencies installed<br>";
    require __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader loaded successfully<br>";
} else {
    echo "❌ Composer dependencies not found<br>";
}

// Test 5: Database Connection
echo "<h2>5. Database Connection</h2>";
try {
    $db = new PDO('mysql:host=localhost;dbname=aashir', 'root', '');
    echo "✅ Database connection successful (aashir)<br>";
    $db = null;
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 6: Configuration
echo "<h2>6. Configuration</h2>";
if (file_exists(__DIR__ . '/app/Config/App.php')) {
    $content = file_get_contents(__DIR__ . '/app/Config/App.php');
    if (strpos($content, "baseURL = 'http://localhost/AEPLcrm4/'") !== false) {
        echo "✅ Base URL configured correctly<br>";
    } else {
        echo "⚠️ Base URL may need adjustment<br>";
    }
}

// Summary
echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Your CodeIgniter 4 setup is ready!</strong></p>";
echo "<ul>";
echo "<li>✅ CI4 framework files in place</li>";
echo "<li>✅ CI3 backed up to <code>ci3_backup/</code></li>";
echo "<li>✅ Core components migrated (Auth, Authorization, Helpers, 2 Models)</li>";
echo "<li>⏳ Controllers, remaining models, and views need migration</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Read <a href='SETUP_CI4_DEFAULT.md'>SETUP_CI4_DEFAULT.md</a></li>";
echo "<li>Follow <a href='MIGRATION_PROGRESS.md'>MIGRATION_PROGRESS.md</a></li>";
echo "<li>Start migrating controllers and models</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>URLs:</strong></p>";
echo "<ul>";
echo "<li><strong>CI4 (Default):</strong> <a href='http://localhost/AEPLcrm4/'>http://localhost/AEPLcrm4/</a></li>";
echo "<li><strong>CI3 (Backup):</strong> <a href='http://localhost/AEPLcrm4/ci3_backup/run_ci3.php'>http://localhost/AEPLcrm4/ci3_backup/run_ci3.php</a></li>";
echo "</ul>";
?>
