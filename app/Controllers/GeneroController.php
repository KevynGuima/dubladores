<?php
declare(strict_types=1);

namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\GeneroModel;
use DI\Container;
use Slim\Views\Twig;
use Respect\Validation\Validator as v;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\Exception as DBALException;
use Respect\Validation\Exceptions\ValidationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

class GeneroController
{
    private $generos;

    public function __construct(GeneroModel $generos)
    {
        $this->generos = $generos;		
    }
	
	public function listar(Request $request, Response $response)
	{
		$generos = $this->generos->All();

		$formattedGeneros = [];
		foreach ($generos as $genero) {
			$formattedGeneros[] = [
				'id'   => $genero['id'],
				'text' => $genero['genero'],
			];
		}

		$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		$jsonResponse->getBody()->write(json_encode(['results' => $formattedGeneros]));

		return $jsonResponse;		
	}
	
	public function index(Request $request, Response $response)
	{
		try {
			$generos = $this->generos->All();
			
			$twig = Twig::fromRequest($request);
			return $twig->render($response, 'generos/index.twig', ['generos' => $generos]);	
		} catch (Exception $e) {
            $jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));

			return $jsonResponse;
        }
	}

    public function insert(Request $request, Response $response)
    {
		$data   = $request->getParsedBody();
		$genero = $data['genero'];
		
		$generoValidator = v::notEmpty()->length(4, 30);

		try {
			$generoValidator->assert($genero);
			
			if($this->generos->Insert($data)) {			
				$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(201);
				$jsonResponse->getBody()->write(json_encode(['message' => 'Salvo com sucesso!', 'success' => true]));

				return $jsonResponse;
			}
		} catch (UniqueConstraintViolationException $e) {
			//echo $e->getMessage();
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);			
			$jsonResponse->getBody()->write(json_encode(['message' => 'Essa gênero já existe', 'success' => false]));
			return $jsonResponse;
		} catch (DBALException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		} catch (ORMException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		} catch (ValidationException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
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
		$data   = $request->getParsedBody();
		$id     = $data['id'];
		$genero = $data['genero'];
		
		//$generoValidator  = v::notEmpty()->noWhiteSpace()->length(5, 30);
		
        // Verifica se a genero existe
        if (!$this->generos->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Genero não encontrado', 'success' => false]));

			return $jsonResponse;
        }
		
		try {
			//$generoValidator->assert($genero);

			if($this->generos->Update($data)) {			
				$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
				$jsonResponse->getBody()->write(json_encode(['message' => 'Editado com sucesso!', 'success' => true]));

				return $jsonResponse;
			}
			
			//$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
			//$jsonResponse->getBody()->write(json_encode(['success' => true]));
			//return $jsonResponse;
		} catch (UniqueConstraintViolationException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Essa genero ja existe', 'success' => false]));

			return $jsonResponse;
		} catch (ORMException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));

			return $jsonResponse;
		} catch (ValidationException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));

			return $jsonResponse;
		} catch (\Exception $e) {
			//echo $e->getMessage();
			// Lidar com outras exceções não previstas
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		}		
    }

    public function delete(Request $request, Response $response, $args)
    {
		$id = $args['id'];
		
        if (!$this->generos->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Genero não encontrado', 'success' => false]));
			return $jsonResponse;
        }

		try {
			$this->generos->Delete($id);
			return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
		 } catch (ForeignKeyConstraintViolationException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Não é possível apagar esse gênero']));
			return $jsonResponse;
        } catch (\Exception $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Ocorreu um erro inesperado.']));
			return $jsonResponse;
        }		
    }
}
