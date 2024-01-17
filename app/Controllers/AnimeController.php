<?php
declare(strict_types=1);

namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\AnimeModel;
use DI\Container;
use Slim\Views\Twig;
use Respect\Validation\Validator as v;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\Exception as DBALException;
use Respect\Validation\Exceptions\ValidationException;

class AnimeController
{
    private $animes;

    public function __construct(AnimeModel $animes)
    {
        $this->animes = $animes;
    }
	
	public function index(Request $request, Response $response)
	{
		try {
			$animes = $this->animes->All();
			
			$twig = Twig::fromRequest($request);
			return $twig->render($response, 'animes/index.twig', ['animes' => $animes]);	
		} catch (\Exception $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
			return 	$jsonResponse;
        }
	}

    public function insert($data)
    {
        // Implemente a lógica para inserir um usuário usando $this->entityManager
    }

    public function update($id, $data)
    {
        // Implemente a lógica para atualizar um usuário usando $this->entityManager
    }

    public function delete($id)
    {
        // Implemente a lógica para excluir um usuário usando $this->entityManager
    }

    public function find($id)
    {
        // Implemente a lógica para encontrar um usuário usando $this->entityManager
    }

    public function findAll()
    {
        // Implemente a lógica para obter todos os usuários usando $this->entityManager
    }	

    public function getUsers(Request $request, Response $response)
    {
        // Recupera os usuários do banco de dados
        $users = $this->db->fetchAll('SELECT * FROM usuarios');

        // Exibe os usuários
        foreach ($users as $user) {
            $response->getBody()->write('ID: ' . $user['id'] . ', Nome: ' . $user['nome'] . '<br>');
        }

        return $response;
    }
}
