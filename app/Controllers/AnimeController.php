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
use Slim\Psr7\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;

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
	
	function moveUploadedFile(UploadedFile $uploadedFile, $directory)
	{
		$filename = $uploadedFile->getClientFilename();
		$uploadedFile->moveTo($directory . $filename);

		return $filename;
	}

	function salvaranime($nome, $data_lancamento, $nomeImagem)
	{
		$stmt = $db->prepare("INSERT INTO animes (nome, data_lancamento, nome_imagem, temporadas) VALUES (:nome, :data_lancamento, :nomeImagem, :temporadas)");
		$stmt->bindParam(':nome', $nome);
		$stmt->bindParam(':data_lancamento', $data_lancamento);
		$stmt->bindParam(':nomeImagem', $nomeImagem);
		$stmt->bindParam(':temporadas', $temporadas);
		$stmt->execute();

		$anime_id = $db->lastInsertId();

		return $anime_id;
	}
	
    public function insert(Request $request, Response $response)
    {
        $data          = $request->getParsedBody();		
        $uploadedFiles = $request->getUploadedFiles();
        $imagem        = $uploadedFiles['imagem'];		
        $nome          = $data['nome'];	
        $nomeImagem    = '';

        if ($imagem->getError() === UPLOAD_ERR_OK) {
            $nomeImagem     = $imagem->getClientFilename();
            $info           = pathinfo($nomeImagem);
            $extensao       = $info['extension'];
            $imagemTemp     = $imagem->getStream()->getMetadata('uri');
            $caminhoDestino = 'dist/images/animes/' . $nome .'.'. $extensao;

            $imagem = Image::make($imagemTemp);
            $imagem->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $imagem->save($caminhoDestino);	
        }
        
		try {
			$result = $this->animes->Insert($data, $nomeImagem);

			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(201);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Salvo com sucesso!']));

			return $jsonResponse;
		} catch (PDOException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage()]));
			return 	$jsonResponse;
		}	
    }

    public function update(Request $request, Response $response)
    {
	    $data          = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $imagem        = $uploadedFiles['imagem'];
        
        $nomeImagem = '';
        if ($imagem->getError() === UPLOAD_ERR_OK) {
            $nomeImagemAntiga = $this->animes->pegarNomeImagem($data['id']);
            if($nomeImagemAntiga) {
                $caminhoImagem = 'dist/images/animes/'.$nomeImagemAntiga;
                if (file_exists($caminhoImagem)) {
                    unlink($caminhoImagem);
                }
            }			
            $nomeImagem = $imagem->getClientFilename();
            
            $imagemTemp     = $imagem->getStream()->getMetadata('uri');
            $info           = pathinfo($nomeImagem);
            $extensao       = $info['extension'];
            $caminhoDestino = 'dist/images/animes' . $nome .'.'. $extensao;

            $imagem = Image::make($imagemTemp);
            $imagem->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $imagem->save($caminhoDestino);
        }
        
        $data['imagem'] = $nomeImagem;

		try {
			$result = $this->animes->Update($data);

			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Salvo com sucesso!']));

			return $jsonResponse;
		} catch (PDOException $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage()]));
			return 	$jsonResponse;
		}
    }

    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        
        if (!$this->animes->Find($id)) {
            $jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            $jsonResponse->getBody()->write(json_encode(['message' => 'anime não encontrado', 'success' => false]));

            return $jsonResponse;
        }

        if($this->animes->Delete($id)) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
        }
    }
}
