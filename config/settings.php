<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

return [
	'database' => [
		'dbname'    => $_ENV['DB_DATABASE'],
		'user'      => $_ENV['DB_USERNAME'],
		'password'  => $_ENV['DB_PASSWORD'],
		'host'      => $_ENV['DB_HOST'],
		'port'      => $_ENV['DB_PORT'] ?? '3306',
		'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
		'charset'   => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
		'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci'
	]
];
