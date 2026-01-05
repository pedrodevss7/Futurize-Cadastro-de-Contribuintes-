<?php

use App\Controllers\Home;
use App\Controllers\AuthController;
use App\Controllers\ContribuinteController;
use App\Controllers\AdminController;
use App\Controllers\ServidorController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 🔹 Rota padrão
$routes->get('/', [Home::class, 'index']);

// 🔹 Autenticação
$routes->group('auth', function ($routes) {
    $routes->post('login', [AuthController::class, 'login']);
    $routes->get('logout', [AuthController::class, 'logout']);
    $routes->post('request-reset', [AuthController::class, 'requestPasswordReset']);
    $routes->post('reset-password', [AuthController::class, 'resetPassword']);
    $routes->get('refresh-csrf', [AuthController::class, 'refreshCsrf']);
});

// 🔹 Contribuintes (view principal)


// 🔹 API de contribuintes
$routes->group('api/contribuintes', function ($routes) {
    $routes->get('listar', [ContribuinteController::class, 'listar']);
    $routes->get('obter/(:num)', [ContribuinteController::class, 'obter/$1']);
    $routes->get('obter/(:num)', [ContribuinteController::class, 'obter/$1']); // 🔹 ADICIONE ESTA
    $routes->post('cadastrar', [ContribuinteController::class, 'cadastrar']);
    $routes->put('editar/(:num)', [ContribuinteController::class, 'editar/$1']);
    $routes->delete('excluir/(:num)', [ContribuinteController::class, 'excluir/$1']);
    $routes->get('atividades', [ContribuinteController::class, 'getAtividades']);
    $routes->get('cnaes', [ContribuinteController::class, 'cnaes']); // 🔹 ADICIONE ESTA
});

// 🔹 Áreas protegidas
$routes->group('admin', function ($routes) {
    $routes->get('dashboard', [AdminController::class, 'dashboard']);
});

$routes->group('servidor', function ($routes) {
    $routes->get('dashboard', [ServidorController::class, 'dashboard']);
});

// 🔹 Teste rápido
$routes->get('test', fn() => json_encode(['status' => 'success', 'message' => 'API funcionando']));

$routes->get('contribuintes/atividades', 'ContribuinteController::getAtividades');



