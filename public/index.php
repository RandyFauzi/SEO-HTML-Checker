<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine paths based on environment structure (Local vs cPanel)
$is_cpanel = file_exists(__DIR__.'/seo-app/vendor/autoload.php');

$vendor_path = $is_cpanel ? __DIR__.'/seo-app/vendor/autoload.php' : __DIR__.'/../vendor/autoload.php';
$bootstrap_path = $is_cpanel ? __DIR__.'/seo-app/bootstrap/app.php' : __DIR__.'/../bootstrap/app.php';
$maintenance_path = $is_cpanel ? __DIR__.'/seo-app/storage/framework/maintenance.php' : __DIR__.'/../storage/framework/maintenance.php';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $maintenance_path)) {
    require $maintenance;
}

// Register the Composer autoloader...
require $vendor_path;

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $bootstrap_path;

$app->handleRequest(Request::capture());
