<?php
declare(strict_types=1);

namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\DubladorModel;
use DI\Container;
use Slim\Views\Twig;
use Respect\Validation\Validator as v;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\Exception as DBALException;
use Respect\Validation\Exceptions\ValidationException;

class DubladorController
{
	private $dubladores;

	public function __construct(DubladorModel $dubladores)
	{
		$this->dubladores = $dubladores;
	}

	public function listar(Request $request, Response $response)
	{
		$data             = $request->getParsedBody();
		$draw             = $data['draw'];
		$start            = $data['start'];
		$length           = $data['length'];
		$searchValue      = $data['search']['value'];
		$orderColumnIndex = $data['order'][0]['column'];
		$orderDirection   = $data['order'][0]['dir'];

		$data = $this->dubladores->All();

		// Filtrar dados se houver uma pesquisa
		if (!empty($searchValue)) {
			$data = array_filter($data, function ($row) use ($searchValue) {
				return stripos($row['nome'], $searchValue) !== false
					|| stripos((string)$row['idade'], $searchValue) !== false;
				// Adicione mais campos conforme necessário
			});
		}

		// Responder ao DataTables no formato esperado
		$dataT = [
			'draw' => (int)$draw,
			'recordsTotal' => count($data),
			'recordsFiltered' => count($data),
			'data' => $data,
		];

		$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		$jsonResponse->getBody()->write(json_encode($dataT));
		return $jsonResponse;
	}
	
	public function index(Request $request, Response $response)
	{
		try {
			$dubladores = $this->dubladores->All();
			
			$twig = Twig::fromRequest($request);
			return $twig->render($response, 'dubladores/index.twig', ['dubladores' => $dubladores]);	
		} catch (Exception $e) {        
      return $response->getBody()->write($e->getMessage())->withStatus(500);
    }
	}

	public function validar(array $data, $modo = 'new') : bool
	{
		$nome             = $data['nome'];
		$data_nascimento  = $data['data_nascimento'];
		$data_falecimento = $data['data_falecimento'];
		
		$nomeValidator  = v::notEmpty()->length(5, 50);
		$dataValidator  = v::date('Y-m-d');
		
		try {
			$nomeValidator->assert($nome);
			$dataValidator->assert($data_nascimento);
			$dataValidator->assert($data_falecimento);

			return true;
		} catch (\Exception $e) {
			return false;
		}

		return false;
	}

	public function insert(Request $request, Response $response)
	{
		$data = $request->getParsedBody();

		$this->validar($data);

		$nome             = $data['nome'];
		$sexo             = $data['sexo'];
		$data_nascimento  = $data['data_nascimento'];	
		$data_falecimento = $data['data_falecimento'];

		try {			
			if($this->dubladores->Insert($data)) {			
				$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(201);
				$jsonResponse->getBody()->write(json_encode(['success' => true, 'message' => 'Salvo com sucesso!']));

				return $jsonResponse;
			}
		} catch (UniqueConstraintViolationException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['success' => false, 'message' => 'Esse email ja existe']));
			return $jsonResponse;
		} catch (\PDOException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		} catch (\Exception $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		}
  }

	public function update(Request $request, Response $response)
	{
		$data = $request->getParsedBody();
		$this->validar($data, 'edicao');
		
		$id             = $data['id'];
		$nome           = $data['nome'];
		$email          = $data['email'];
		$senha          = $data['senha'];
		$dataNascimento = $data['dataNascimento'];		
		
        if (!$this->dubladores->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Dublador não encontrado', 'success' => false]));

			return $jsonResponse;
        }
		
		try {
			if($this->dubladores->Update($data)) {			
				$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
				$jsonResponse->getBody()->write(json_encode(['success' => true, 'message' => 'Editado com sucesso!']));

				return $jsonResponse;
			}
		} catch (\PDOException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		} catch (\Exception $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		}
    }

	public function delete(Request $request, Response $response, $args)
	{
		$id = $args['id'];
		
		if (!$this->usuarios->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Usuário não encontrado', 'success' => false]));

			return $jsonResponse;
		}

		if($this->usuarios->Delete($id)) {
			return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
		}
	}
}
