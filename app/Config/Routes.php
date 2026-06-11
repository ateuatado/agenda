<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// -------------------------------------------------------------------------
// Shield auth routes (login, logout, register)
// -------------------------------------------------------------------------
service('auth')->routes($routes);

// -------------------------------------------------------------------------
// CLIENT — Portal público de agendamento
// -------------------------------------------------------------------------
$routes->group('', ['namespace' => 'App\Controllers\Client'], static function ($routes) {
    $routes->get('/',                        'BookingController::index',         ['as' => 'client.home']);
    $routes->get('disponibilidade',          'BookingController::availability',  ['as' => 'client.availability']);
    $routes->get('agendar/(:num)',           'BookingController::book/$1',       ['as' => 'client.book']);
    $routes->post('agendar/(:num)',          'BookingController::store/$1',      ['as' => 'client.book.store']);
    $routes->get('acesso',                   'AuthController::requestAccess',    ['as' => 'client.access']);
    $routes->post('acesso',                  'AuthController::sendMagicLink',    ['as' => 'client.access.send']);
    $routes->get('confirmar/(:alphanum)',     'AuthController::confirm/$1',       ['as' => 'client.confirm']);
    $routes->get('minha-agenda',             'BookingController::myBookings',    ['as' => 'client.my_bookings', 'filter' => 'customer-auth']);
    $routes->post('cancelar/(:num)',         'BookingController::cancel/$1',     ['as' => 'client.cancel',      'filter' => 'customer-auth']);
    $routes->post('interesse/(:num)',        'BookingController::interest/$1',   ['as' => 'client.interest']);
    $routes->get('logout-cliente',           'AuthController::logout',           ['as' => 'client.logout']);
});

// -------------------------------------------------------------------------
// ADMIN — Painel de administração (protegido pelo Shield)
// -------------------------------------------------------------------------
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'session'], static function ($routes) {

    // Dashboard
    $routes->get('/',              'DashboardController::index', ['as' => 'admin.dashboard']);

    // Tipos de sessão
    $routes->get('session-types',           'SessionTypeController::index',    ['as' => 'admin.session_types']);
    $routes->get('session-types/new',       'SessionTypeController::new',      ['as' => 'admin.session_types.new']);
    $routes->post('session-types',          'SessionTypeController::create',   ['as' => 'admin.session_types.create']);
    $routes->get('session-types/(:num)',    'SessionTypeController::edit/$1',  ['as' => 'admin.session_types.edit']);
    $routes->post('session-types/(:num)',   'SessionTypeController::update/$1',['as' => 'admin.session_types.update']);
    $routes->post('session-types/(:num)/delete', 'SessionTypeController::delete/$1', ['as' => 'admin.session_types.delete']);

    // Slots da agenda
    $routes->get('slots',                  'SlotController::index',      ['as' => 'admin.slots']);
    $routes->get('slots/new',              'SlotController::new',        ['as' => 'admin.slots.new']);
    $routes->post('slots',                 'SlotController::create',     ['as' => 'admin.slots.create']);
    $routes->get('slots/batch',            'SlotController::batch',      ['as' => 'admin.slots.batch']);
    $routes->post('slots/batch',           'SlotController::storeBatch', ['as' => 'admin.slots.batch.store']);
    $routes->get('slots/(:num)',           'SlotController::edit/$1',    ['as' => 'admin.slots.edit']);
    $routes->post('slots/(:num)',          'SlotController::update/$1',  ['as' => 'admin.slots.update']);
    $routes->post('slots/(:num)/delete',   'SlotController::delete/$1',  ['as' => 'admin.slots.delete']);
    $routes->post('slots/(:num)/hold',     'SlotController::hold/$1',    ['as' => 'admin.slots.hold']);
    $routes->post('slots/(:num)/release',  'SlotController::release/$1', ['as' => 'admin.slots.release']);

    // Agendamentos
    $routes->get('bookings',               'BookingController::index',         ['as' => 'admin.bookings']);
    $routes->get('bookings/(:num)',        'BookingController::show/$1',       ['as' => 'admin.bookings.show']);
    $routes->post('bookings/(:num)/status','BookingController::updateStatus/$1',['as' => 'admin.bookings.status']);

    // Clientes
    $routes->get('customers',             'CustomerController::index',   ['as' => 'admin.customers']);
    $routes->get('customers/(:num)',      'CustomerController::show/$1', ['as' => 'admin.customers.show']);

    // Configurações
    $routes->get('settings',              'SettingsController::index',  ['as' => 'admin.settings']);
    $routes->post('settings',             'SettingsController::update', ['as' => 'admin.settings.update']);

    // Documentação da API
    $routes->get('api-docs',              'ApiDocsController::index',   ['as' => 'admin.api_docs']);

});

// -------------------------------------------------------------------------
// API v1 — REST endpoints
// -------------------------------------------------------------------------
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], static function ($routes) {

    // Public endpoints
    $routes->get('availability',            'AvailabilityController::index',  ['as' => 'api.availability']);
    $routes->get('availability/(:segment)', 'AvailabilityController::byDate/$1', ['as' => 'api.availability.date']);
    $routes->post('book',                   'BookingController::book',         ['as' => 'api.book']);
    $routes->post('interest',               'InterestController::store',       ['as' => 'api.interest']);

    // Customer auth (magic link)
    $routes->post('auth/request-access',    'AuthController::requestAccess',  ['as' => 'api.auth.request']);
    $routes->post('auth/verify',            'AuthController::verify',          ['as' => 'api.auth.verify']);

    // Authenticated customer endpoints
    $routes->group('', ['filter' => 'api-auth'], static function ($routes) {
        $routes->get('my-bookings',             'BookingController::myBookings',   ['as' => 'api.my_bookings']);
        $routes->delete('bookings/(:num)',       'BookingController::cancel/$1',    ['as' => 'api.cancel']);
        $routes->get('interests',               'InterestController::myInterests', ['as' => 'api.interests']);
    });

    // Admin API endpoints
    $routes->group('admin', ['filter' => 'session'], static function ($routes) {
        $routes->get('slots',               'SlotController::index',       ['as' => 'api.admin.slots']);
        $routes->post('slots',              'SlotController::create',      ['as' => 'api.admin.slots.create']);
        $routes->post('slots/batch',        'SlotController::batch',       ['as' => 'api.admin.slots.batch']);
        $routes->put('slots/(:num)',        'SlotController::update/$1',   ['as' => 'api.admin.slots.update']);
        $routes->delete('slots/(:num)',     'SlotController::delete/$1',   ['as' => 'api.admin.slots.delete']);
        $routes->get('bookings',            'BookingController::index',    ['as' => 'api.admin.bookings']);
        $routes->put('bookings/(:num)',     'BookingController::update/$1',['as' => 'api.admin.bookings.update']);
        $routes->get('customers',           'CustomerController::index',   ['as' => 'api.admin.customers']);
    });
});
