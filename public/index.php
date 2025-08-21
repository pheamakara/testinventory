<?php
// public/index.php
require_once '../app/core/App.php';
require_once '../app/core/Database.php';
require_once '../app/core/Router.php';
require_once '../app/controllers/Controller.php';

$app = new App();
$router = $app->getRouter();

// Authentication routes
$router->add('GET', '/login', 'AuthController@login');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/logout', 'AuthController@logout');

// Dashboard route
$router->add('GET', '/dashboard', 'DashboardController@index');
$router->add('GET', '/', 'DashboardController@index');

// Server routes
$router->add('GET', '/servers', 'ServerController@index');
$router->add('GET', '/servers/create', 'ServerController@create');
$router->add('POST', '/servers/create', 'ServerController@create');
$router->add('GET', '/servers/edit/{id}', 'ServerController@edit');
$router->add('POST', '/servers/edit/{id}', 'ServerController@edit');
$router->add('GET', '/servers/delete/{id}', 'ServerController@delete');

// Import/Export routes
$router->add('GET', '/servers/import', 'ImportExportController@importServers');
$router->add('POST', '/servers/import', 'ImportExportController@importServers');
$router->add('GET', '/servers/export', 'ImportExportController@exportServers');

// Deployment routes
$router->add('GET', '/deployments', 'DeploymentController@index');
$router->add('GET', '/deployments/create', 'DeploymentController@create');
$router->add('POST', '/deployments/create', 'DeploymentController@create');
$router->add('GET', '/deployments/view/{id}', 'DeploymentController@view');
$router->add('POST', '/deployments/update-checklist/{id}', 'DeploymentController@updateChecklist');
$router->add('GET', '/deployments/submit/{id}', 'DeploymentController@submit');
$router->add('POST', '/deployments/approve/{id}/{step}', 'DeploymentController@approve');

// User management routes (admin only)
$router->add('GET', '/users', 'UserController@index');
$router->add('GET', '/users/create', 'UserController@create');
$router->add('POST', '/users/create', 'UserController@create');
$router->add('GET', '/users/edit/{id}', 'UserController@edit');
$router->add('POST', '/users/edit/{id}', 'UserController@edit');
$router->add('GET', '/users/delete/{id}', 'UserController@delete');

$router->add('GET', '/settings', 'SettingsController@index');
$router->add('POST', '/settings/update', 'SettingsController@update');
$router->add('POST', '/settings/test-ldap', 'SettingsController@testLdap');
$router->add('POST', '/settings/test-email', 'SettingsController@testEmail');
$router->add('GET', '/profile', 'AuthController@profile');
$router->add('POST', '/profile', 'AuthController@profile');

$router->add('GET', '/api/notifications', 'ApiController@notifications');

$app->run();