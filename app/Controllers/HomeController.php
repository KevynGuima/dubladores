<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
//use Respect\Validation\Validator as v;
use DI\Container;

class HomeController
{
    private $logger;

    public function __construct(Container $container) 
	{
        $this->logger = $container->get('logger');
    }
	
    public function index(Request $request, Response $response)
    {
		$this->logger->debug('uma mensagem de DEBUG');
		$this->logger->info('uma mensagem de info');
		$this->logger->notice('uma mensagem de notice');
		$this->logger->warning('Uma mensagem de aviso');
		$this->logger->error('Uma mensagem de erro');   
		$this->logger->critical('Uma mensagem critica');    
		$this->logger->alert('Uma mensagem de alerta');    
		$this->logger->emergency('Uma mensagem de emergencia');
		
		// Exemplo de registro de log com contexto e extras
		$user = ['id' => 123, 'name' => 'John Doe'];
		// Mensagem de log com contexto (context)
		$this->logger->warning('Usuário não autenticado', ['user' => $user]);

		// Mensagem de log com extras
		$this->logger->warning('Operação suspeita', $user);
		
		$name = 'Jose';
		$twig = Twig::fromRequest($request);
        //return $twig->render($response, 'home/index.twig');
		return $twig->render($response, 'home/index.twig', ['page' => 'home']);
    }
}
