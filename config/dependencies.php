<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Respect\Validation\Validator as v;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Doctrine\DBAL\Logging\FileSQLLogger;
use DI\Container;
use App\Helpers;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'db' => function () {
            $config = require '../config/settings.php';
			
			return DriverManager::getConnection($config['database']);
        },
        'validator' => function () {
            return v::create();
        },
		'logger' => function () {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler('../logs/app.log', Logger::DEBUG));

            return $logger;
        },
		'entityManager' => function (Container $container) {
            // Configurações do Doctrine
            $isDevMode = true;
            $config = Setup::createAnnotationMetadataConfiguration(
                [__DIR__.'/../app/Entity'],
                $isDevMode,
                null,
                null,
                false
            );
			//$config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());			

			// Configure o logger do Doctrine para registrar em um arquivo
			//$config->setSQLLogger(new FileSQLLogger('/../logs/doctrine.log'));			

			$connection = $container->get('db');
            //$configDB   = require '../config/settings.php';			
			//$connection = DriverManager::getConnection($configDB['database']);
            return EntityManager::create($connection, $config);
        },
		'Helpers' => function () {
            $helper = new Helpers();

            return $helper;
        }
    ]);
};
