<?php
declare(strict_types=1);

namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\CategoriaModel;
use DI\Container;
use Slim\Views\Twig;
use Respect\Validation\Validator as v;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\Exception as DBALException;
use Respect\Validation\Exceptions\ValidationException;

class CategoriaController
{
    private $categorias;

    public function __construct(CategoriaModel $categorias)
    {
        $this->categorias = $categorias;		
    }
	
	public function index(Request $request, Response $response)
	{
		try {
			$categorias = $this->categorias->All();
			
			$twig = Twig::fromRequest($request);
			return $twig->render($response, 'categorias/index.twig', ['categorias' => $categorias]);	
		} catch (Exception $e) {
            $jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));

			return $jsonResponse;
        }
	}

    public function insert(Request $request, Response $response)// -------------------------------------------------
    {
		$data      = $request->getParsedBody();
		$categoria = $data['categoria'];
		
		$categoriaValidator = v::notEmpty()->noWhiteSpace()->length(4, 30);

		try {
			$categoriaValidator->assert($categoria);
			
			if($this->categorias->Insert($data)) {			
				$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(201);
				$jsonResponse->getBody()->write(json_encode(['success' => true]));

				return $jsonResponse;
			}
		} catch (UniqueConstraintViolationException $e) {
			//echo $e->getMessage();
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);			
			$jsonResponse->getBody()->write(json_encode(['message' => 'Essa categoria ja existe', 'success' => false]));
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
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(400);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			return $jsonResponse;
		} catch (\Exception $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => '???' .$e->getMessage(), 'success' => false]));
			return $jsonResponse;
		}
    }

    public function update(Request $request, Response $response)
    {
		$data      = $request->getParsedBody();
		$id        = $data['id'];
		$categoria = $data['categoria'];
		
		//$categoriaValidator  = v::notEmpty()->noWhiteSpace()->length(5, 30);
		
        // Verifica se a categoria existe
        if (!$this->categorias->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Categoria não encontrada', 'success' => false]));

			return $jsonResponse;
        }
		
		try {
			//$categoriaValidator->assert($categoria);

			if($this->categorias->Update($data)) {			
				$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
				$jsonResponse->getBody()->write(json_encode(['success' => true]));

				return $jsonResponse;
			}
			
			//$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
			//$jsonResponse->getBody()->write(json_encode(['success' => true]));
			//return $jsonResponse;
		} catch (UniqueConstraintViolationException $e) {
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
		
        if (!$this->categorias->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Categoria não encontrada', 'success' => false]));

			return $jsonResponse;
        }

		if($this->categorias->Delete($id)) {
			return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
		}
    }
}
