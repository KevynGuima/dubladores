<?php
declare(strict_types=1);

namespace App;

use Slim\App;
use App\Controllers\AnimeController;
use App\Controllers\HomeController;
use App\Controllers\GeneroController;
use App\Controllers\DubladorController;
use App\Controllers\FilmeController;
use App\Controllers\SerieController;
use App\Controllers\UsuarioController;

return function (App $app) {
	$app->get('/', [HomeController::class, 'index'])->setName('home');
	
	$app->group('/usuarios', function ($group) {
		$group->get('', [UsuarioController::class, 'index'])->setName('usuarios');
		$group->get('/listar', [UsuarioController::class, 'listar']);
		$group->post('/insert', [UsuarioController::class, 'insert']);
		$group->post('/update', [UsuarioController::class, 'update']);
		$group->delete('/delete/{id}', [UsuarioController::class, 'delete']);
	});

	$app->group('/generos', function ($group) {
		$group->get('', [GeneroController::class, 'index'])->setName('generos');
		$group->get('/listar', [GeneroController::class, 'listar']);
		$group->post('/insert', [GeneroController::class, 'insert']);
		$group->post('/update', [GeneroController::class, 'update']);
		$group->delete('/delete/{id}', [GeneroController::class, 'delete']);
	});
	
	$app->group('/filmes', function ($group) {
		$group->get('', [FilmeController::class, 'index'])->setName('filmes');
		$group->get('/listar', [FilmeController::class, 'listar']);
		$group->post('/insert', [FilmeController::class, 'insert']);
		$group->post('/update', [FilmeController::class, 'update']);
		$group->delete('/delete/{id}', [FilmeController::class, 'delete']);
	});
	
	$app->group('/series', function ($group) {
		$group->get('', [SerieController::class, 'index'])->setName('series');
		$group->get('/listar', [SerieController::class, 'listar']);
		$group->post('/insert', [SerieController::class, 'insert']);
		$group->post('/update', [SerieController::class, 'update']);
		$group->delete('/delete/{id}', [SerieController::class, 'delete']);
	});
	
	$app->group('/animes', function ($group) {
		$group->get('', [AnimeController::class, 'index'])->setName('animes');
		$group->get('/listar', [AnimeController::class, 'listar']);
		$group->post('/insert', [AnimeController::class, 'insert']);
		$group->post('/update', [AnimeController::class, 'update']);
		$group->delete('/delete/{id}', [AnimeController::class, 'delete']);
	});
	
	$app->group('/dubladores', function ($group) {
		$group->get('', [DubladorController::class, 'index'])->setName('dubladores');
		$group->get('/listar', [DubladorController::class, 'listar']);
		$group->post('/insert', [DubladorController::class, 'insert']);
		$group->post('/update', [DubladorController::class, 'update']);
		$group->delete('/delete/{id}', [DubladorController::class, 'delete']);
	});		
	
	//$app->get('/usuarios', [UsuarioController::class, 'index'])->setName('usuarios');
	//$app->get('/usuarios/listar', [UsuarioController::class, 'listar']);
	//$app->post('/usuarios/insert', [UsuarioController::class, 'insert']);
	//$app->post('/usuarios/update', [UsuarioController::class, 'update']);
	//$app->delete('/usuarios/delete/{id}', [UsuarioController::class, 'delete']);
};
