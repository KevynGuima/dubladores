<?php
declare(strict_types=1);

namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\UsuarioModel;
use DI\Container;
use Slim\Views\Twig;
use Respect\Validation\Validator as v;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\Exception as DBALException;
use Respect\Validation\Exceptions\ValidationException;

class UsuarioController
{
    private $usuarios;

    public function __construct(UsuarioModel $usuarios)
    {
        $this->usuarios = $usuarios;
    }
	
	public function index(Request $request, Response $response)
	{
		try {
			$usuarios = $this->usuarios->All();
			
			$twig = Twig::fromRequest($request);
			return $twig->render($response, 'usuarios/index.twig', ['usuarios' => $usuarios]);	
		} catch (Exception $e) {        
            return $response->getBody()->write($e->getMessage())->withStatus(500);
        }
	}

	public function validar(array $data, $modo = 'new') : bool
	{
		$nome           = $data['nome'];
		$email          = $data['email'];
		$senha          = $data['senha'];
		$dataNascimento = $data['dataNascimento'];
		
		$nomeValidator  = v::notEmpty()->length(5, 50);
		$emailValidator = v::notEmpty()->noWhiteSpace()->email()->length(10, 64);
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
		}  catch (UniqueConstraintViolationException $e) {
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
