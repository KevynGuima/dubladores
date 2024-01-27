<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use DI\Container;

class HomeController
{
    //private $logger;
    private $conn;

    public function __construct(Container $container) 
	{
        //$this->logger = $container->get('logger');
		$this->conn = $container->get('db');
    }
	
    public function index(Request $request, Response $response)
    {
		/*$this->logger->debug('uma mensagem de DEBUG');
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
		*/
		
        $result1 = $this->conn->fetchAssociative('SELECT COUNT(*) as total FROM generos');
        $result2 = $this->conn->fetchAssociative('SELECT COUNT(*) as total FROM animes');
        $result3 = $this->conn->fetchAssociative('SELECT COUNT(*) as total FROM filmes');
        $result4 = $this->conn->fetchAssociative('SELECT COUNT(*) as total FROM series');
        $result5 = $this->conn->fetchAssociative('SELECT COUNT(*) as total FROM usuarios');
        $result6 = $this->conn->fetchAssociative('SELECT COUNT(*) as total FROM dubladores');
        $result7 = $this->conn->fetchAssociative("SELECT COUNT(*) as total FROM tarefas where concluido = 'N'");
        $tarefas = $this->conn->fetchAllAssociative("SELECT * FROM tarefas where concluido = 'N'");

		$totais = [
			'generos' => $result1['total'], 
			'animes' => $result2['total'],
			'filmes' => $result3['total'],
			'series' => $result4['total'],
			'usuarios' => $result5['total'],
			'dubladores' => $result6['total'],
			'tarefas' => $result7['total'],
			'tarefasDB' => $tarefas
		];
		
		$twig = Twig::fromRequest($request);        
		return $twig->render($response, 'home/index.twig', ['totais' => $totais]);
    }
	
	public function insert(Request $request, Response $response)
	{
		$data      = $request->getParsedBody();
		$id        = $data['id'];
		$concluido = $data['concluido'];	
		
		$sql    = "UPDATE tarefas SET concluido = :concluido WHERE id = :id";
		$params = ['id' => $id, 'concluido' => $concluido];

		$affectedRows = $this->conn->executeUpdate($sql, $params);

		if ($affectedRows > 0) {
			echo "Atualização realizada com sucesso. Linhas afetadas: $affectedRows";
		} else {
			echo "Nenhuma linha foi atualizada.";
		}		

		$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		$jsonResponse->getBody()->write(json_encode(['success' => true]));
		return $jsonResponse;
	}
	
	public function principal(Request $request, Response $response)
	{		
		$twig = Twig::fromRequest($request);        
		return $twig->render($response, 'home/principal.twig');
	}
}
