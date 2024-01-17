<?php
declare(strict_types=1);

namespace App;

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\ContainerBuilder;
use Symfony\Component\Console\Application;
use App\Command\Database;
use App\Command\Table;
use App\Command\Seeder;

require '../vendor/autoload.php';

$dependencies = require '../config/dependencies.php';
$routes       = require '../config/routes.php';

try {
	$containerBuilder = new ContainerBuilder();
	$dependencies($containerBuilder);
	$container = $containerBuilder->build();

	$app = AppFactory::createFromContainer($container);
	//$app->addBodyParsingMiddleware();
	//Debug::enable();
	//$handler = new ErrorHandler();
	//$handler->register();
	$errorMiddleware = $app->addErrorMiddleware(true, true, true);
	
	//$errorMiddleware->setDefaultErrorHandler($handler);

	//$errorMiddleware = $app->addErrorMiddleware(true, true, true);
	//$errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));

	$twig = Twig::create('../app/views', ['cache' => false]);
	if (php_sapi_name() !== 'cli') {
		$twig->getEnvironment()->addGlobal('currentUrl', str_replace('/', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
		$twig->getEnvironment()->addFunction(new \Twig\TwigFunction('existeArquivo', function ($arquivo) {
			if ($arquivo == null or $arquivo == '') {
				return false;
			}
			return file_exists('dist/js/' . $arquivo .'.js');
		}));
		$twig->getEnvironment()->addFunction(new \Twig\TwigFunction('existeImagem', function ($imagem) {
			if ($imagem == null or $imagem == '') {
				return false;
			}
			return file_exists('dist/images/' . $imagem);
		}));		
	}

	$app->add(TwigMiddleware::create($app, $twig));
	
	if (php_sapi_name() === 'cli') {
		$console = new Application();
		$console->add(new Database($container));
		$console->add(new Table($container));
		$console->add(new Seeder($container));	
		$console->run();
	}

	$routes($app);
	$app->run();
} catch (\Exception $e) {
	echo $e->getMessage();
    error_log('Erro não tratado: ' . $e->getMessage());
}