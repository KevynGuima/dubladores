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
		$imagem          = $data['imagem'];
		$nome            = $data['nome'];
		$sexo            = $data['sexo'];
		$dataNascimento  = $data['dataNascimento'];
		$dataFalecimento = $data['dataFalecimento'];
		
		$nomeValidator  = v::notEmpty()->length(5, 50);
		$senhaValidator = v::notEmpty()->noWhiteSpace()->length(6, 30);
		$dataValidator  = v::date('Y-m-d');
		
		try {
			$nomeValidator->assert($nome);
			$emailValidator->assert($email);
			if($modo == 'new') {
				$senhaValidator->assert($senha);
			}
			$dataValidator->assert($dataNascimento);
			
			return true;
		} catch (ValidationException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
			return $jsonResponse;		
		} catch (\Exception $e) {
			return false;
		}
		
		return false;
	}

	public function insert(Request $request, Response $response)
	{
		$data = $request->getParsedBody();

		$this->validar($data);
		
		$nome           = $data['nome'];
		$email          = $data['email'];
		$senha          = $data['senha'];
		$dataNascimento = $data['dataNascimento'];		
		
		try {			
			if($this->usuarios->Insert($data)) {			
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
		
		if (!$this->usuarios->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Usuário não encontrado', 'success' => false]));

			return $jsonResponse;
		}
		
		try {
			if($this->usuarios->Update($data)) {			
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
