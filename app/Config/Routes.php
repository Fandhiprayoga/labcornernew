<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Auth Routes (Shield)
// ---------------------------------------------------------------
service('auth')->routes($routes);

// ---------------------------------------------------------------
// Public Routes
// ---------------------------------------------------------------
$routes->get('/', 'AuthController::login');
$routes->get('maintenance', static function () {
    return view('errors/maintenance');
});

// ---------------------------------------------------------------
// Protected Routes (require login)
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'session'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Switch Active Group
    $routes->post('switch-group', 'GroupSwitchController::switch');

    // Profile
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update', 'ProfileController::update');

    // ---------------------------------------------------------------
    // Admin Routes (require admin.access permission)
    // ---------------------------------------------------------------
    $routes->group('admin', ['filter' => 'permission:admin.access'], static function ($routes) {

        // User Management
        $routes->group('users', static function ($routes) {
            $routes->get('/', 'UserController::index', ['filter' => 'permission:users.list']);
            $routes->get('create', 'UserController::create', ['filter' => 'permission:users.create']);
            $routes->post('store', 'UserController::store', ['filter' => 'permission:users.create']);
            $routes->get('edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:users.edit']);
            $routes->post('update/(:num)', 'UserController::update/$1', ['filter' => 'permission:users.edit']);
            $routes->post('delete/(:num)', 'UserController::delete/$1', ['filter' => 'permission:users.delete']);
            $routes->post('assign-role/(:num)', 'UserController::assignRole/$1', ['filter' => 'permission:users.manage-roles']);
        });

        // Room Management
        $routes->group('rooms', static function ($routes) {
            $routes->get('/', 'RoomController::index', ['filter' => 'permission:rooms.list']);
            $routes->get('create', 'RoomController::create', ['filter' => 'permission:rooms.create']);
            $routes->post('store', 'RoomController::store', ['filter' => 'permission:rooms.create']);
            $routes->get('edit/(:num)', 'RoomController::edit/$1', ['filter' => 'permission:rooms.edit']);
            $routes->post('update/(:num)', 'RoomController::update/$1', ['filter' => 'permission:rooms.edit']);
            $routes->post('delete/(:num)', 'RoomController::delete/$1', ['filter' => 'permission:rooms.delete']);
        });

        // Faculty Management
        $routes->group('faculties', static function ($routes) {
            $routes->get('/', 'FacultyController::index', ['filter' => 'permission:faculties.list']);
            $routes->get('create', 'FacultyController::create', ['filter' => 'permission:faculties.create']);
            $routes->post('store', 'FacultyController::store', ['filter' => 'permission:faculties.create']);
            $routes->get('edit/(:segment)', 'FacultyController::edit/$1', ['filter' => 'permission:faculties.edit']);
            $routes->post('update/(:segment)', 'FacultyController::update/$1', ['filter' => 'permission:faculties.edit']);
            $routes->post('delete/(:segment)', 'FacultyController::delete/$1', ['filter' => 'permission:faculties.delete']);
        });

        // Role Management (superadmin only)
        $routes->group('roles', ['filter' => 'role:superadmin'], static function ($routes) {
            $routes->get('/', 'RoleController::index');
            $routes->get('permissions', 'RoleController::permissions');
        });

        // Settings
        $routes->group('settings', ['filter' => 'permission:admin.settings'], static function ($routes) {
            $routes->get('/', 'SettingController::index');
            $routes->post('update/general', 'SettingController::updateGeneral');
            $routes->post('update/branding', 'SettingController::updateBranding');
            $routes->post('update/appearance', 'SettingController::updateAppearance');
            $routes->post('update/auth', 'SettingController::updateAuth');
            $routes->post('update/mail', 'SettingController::updateMail');
            $routes->post('test-email', 'SettingController::testEmail');
            $routes->post('reset', 'SettingController::resetDefaults');
        });
    });
});
