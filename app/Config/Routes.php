<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->addPlaceholder('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}');

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

    // Notifications
    $routes->get('notifications', 'NotificationController::index');
    $routes->get('notifications/read/(:num)', 'NotificationController::read/$1');
    $routes->post('notifications/mark-all-read', 'NotificationController::markAllRead');
    $routes->post('notifications/delete/(:num)', 'NotificationController::delete/$1');

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

        // Laboratory Management
        $routes->group('laboratories', static function ($routes) {
            $routes->get('/', 'LaboratoryController::index', ['filter' => 'permission:laboratories.list']);
            $routes->get('create', 'LaboratoryController::create', ['filter' => 'permission:laboratories.create']);
            $routes->post('store', 'LaboratoryController::store', ['filter' => 'permission:laboratories.create']);
            $routes->get('edit/(:uuid)', 'LaboratoryController::edit/$1', ['filter' => 'permission:laboratories.edit']);
            $routes->post('update/(:uuid)', 'LaboratoryController::update/$1', ['filter' => 'permission:laboratories.edit']);
            $routes->post('delete/(:uuid)', 'LaboratoryController::delete/$1', ['filter' => 'permission:laboratories.delete']);
        });

        // Asset Management
        $routes->group('assets', static function ($routes) {
            $routes->get('/', 'AssetController::index', ['filter' => 'permission:assets.list']);
            $routes->get('create', 'AssetController::create', ['filter' => 'permission:assets.create']);
            $routes->post('store', 'AssetController::store', ['filter' => 'permission:assets.create']);
            $routes->get('edit/(:uuid)', 'AssetController::edit/$1', ['filter' => 'permission:assets.edit']);
            $routes->post('update/(:uuid)', 'AssetController::update/$1', ['filter' => 'permission:assets.edit']);
            $routes->post('delete/(:uuid)', 'AssetController::delete/$1', ['filter' => 'permission:assets.delete']);
        });

        // Laboratory Laboran Assignment
        $routes->group('laboratory-laborans', static function ($routes) {
            $routes->get('/', 'LaboratoryLaboranController::index', ['filter' => 'permission:laboratory-laborans.list']);
            $routes->get('create', 'LaboratoryLaboranController::create', ['filter' => 'permission:laboratory-laborans.create']);
            $routes->post('store', 'LaboratoryLaboranController::store', ['filter' => 'permission:laboratory-laborans.create']);
            $routes->get('edit/(:num)', 'LaboratoryLaboranController::edit/$1', ['filter' => 'permission:laboratory-laborans.edit']);
            $routes->post('update/(:num)', 'LaboratoryLaboranController::update/$1', ['filter' => 'permission:laboratory-laborans.edit']);
            $routes->post('delete/(:num)', 'LaboratoryLaboranController::delete/$1', ['filter' => 'permission:laboratory-laborans.delete']);
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

        // Study Program Management
        $routes->group('study-programs', static function ($routes) {
            $routes->get('/', 'StudyProgramController::index', ['filter' => 'permission:study-programs.list']);
            $routes->get('create', 'StudyProgramController::create', ['filter' => 'permission:study-programs.create']);
            $routes->post('store', 'StudyProgramController::store', ['filter' => 'permission:study-programs.create']);
            $routes->get('edit/(:segment)', 'StudyProgramController::edit/$1', ['filter' => 'permission:study-programs.edit']);
            $routes->post('update/(:segment)', 'StudyProgramController::update/$1', ['filter' => 'permission:study-programs.edit']);
            $routes->post('delete/(:segment)', 'StudyProgramController::delete/$1', ['filter' => 'permission:study-programs.delete']);
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
