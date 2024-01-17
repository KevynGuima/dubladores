<?php
declare(strict_types=1);

namespace App\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
//use Psr\Http\Message\ResponseInterface as Response;
//use Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Psr7\Request;
//use Slim\Psr7\Response;
//use Slim\Psr7\Factory\StreamFactory; 
use App\Models\FilmeModel;
use DI\Container;
use Slim\Views\Twig;
use Respect\Validation\Validator as v;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\Exception as DBALException;
use Respect\Validation\Exceptions\ValidationException;
use Slim\Psr7\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;

class FilmeController
{
    private $filmes;

    public function __construct(FilmeModel $filmes)
    {
        $this->filmes = $filmes;
    }
	
	public function index(Request $request, Response $response)
	{
		try {
			$filmes = $this->filmes->All();
			
			$twig = Twig::fromRequest($request);
			return $twig->render($response, 'filmes/index.twig', ['filmes' => $filmes]);	
		} catch (\Exception $e) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			$jsonResponse->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
			return 	$jsonResponse;
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

	/*public function moveUploadedFile($directory, UploadedFile $uploadedFile) 
	{
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		//$basename  = bin2hex(random_bytes(8));
		//$filename  = sprintf('%s.%0.8s', $basename, $extension);
		$filename  = $uploadedFile->getClientFilename();

		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . str_replace(' ', '_', strtolower($filename)));

		return $extension;
	}*/
	
	function moveUploadedFile(UploadedFile $uploadedFile, $directory)
	{
		//$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		//$basename = bin2hex(random_bytes(8)); // Gera um nome de arquivo aleatório
		$filename = $uploadedFile->getClientFilename();//sprintf('%s.%0.8s', $basename, $extension);
		$uploadedFile->moveTo($directory . $filename);

		return $filename;
	}

	// Função para salvar filme
	function salvarFilme($nome, $dataLancamento, $nomeImagem)
	{
		// Suponha que você tenha uma instância de PDO $db
		$stmt = $db->prepare("INSERT INTO filmes (nome, data_lancamento, nome_imagem) VALUES (:nome, :dataLancamento, :nomeImagem)");
		$stmt->bindParam(':nome', $nome);
		$stmt->bindParam(':dataLancamento', $dataLancamento);
		$stmt->bindParam(':nomeImagem', $nomeImagem);
		$stmt->execute();

		// Obtenha o último ID inserido (filme_id)
		$filme_id = $db->lastInsertId();

		return $filme_id;
	}
	
    public function insert(Request $request, Response $response)
    {
	    $data           = $request->getParsedBody();		
		$uploadedFiles  = $request->getUploadedFiles();
		$imagem         = $uploadedFiles['imagem'];		
		$nome           = $data['nome'];
		//$dataLancamento = $dadosDoFormulario['dataLancamento'];		

		$nomeImagem = '';
		if ($imagem->getError() === UPLOAD_ERR_OK) {
			$nomeImagem     = $imagem->getClientFilename();
		    $info           = pathinfo($nomeImagem);
			$extensao       = $info['extension'];
			$imagemTemp     = $imagem->getStream()->getMetadata('uri');
			$caminhoDestino = 'dist/images/' . $nome .'.'. $extensao;

			$imagem = Image::make($imagemTemp);
			$imagem->resize(500, null, function ($constraint) {
				$constraint->aspectRatio();
			});
			$imagem->save($caminhoDestino);
			
			//$imagem = $manager->make($imagemTemp);
			//$imagem->resize(350, null, function ($constraint) {
				//$constraint->aspectRatio();
			//});
			//$imagem->save($caminhoDestino);
			
			
			// Mova o arquivo para o diretório desejado e obtenha o nome original
			//$nomeImagem = moveUploadedFile($imagem, '/caminho/do/seu/diretorio/');
			
			//$imagem->moveTo('dist/images/' . $nomeImagem);
			// Salve o filme e obtenha o filme_id
			// Obtenha os gêneros selecionados
			//$generos = $dadosDoFormulario['generos'];

			// Salve os relacionamentos filme-gênero na tabela filme_genero
			///salvarRelacionamentosFilmeGenero($filme_id, $generosSelecionados);			
		}
		
		$result = $this->filmes->Insert($data, $nomeImagem);

		$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(201);
		$jsonResponse->getBody()->write(json_encode(['success' => true, 'message' => 'Salvo com sucesso!']));

		return $jsonResponse;
		

		//$this->validar($data);
		
		//$data['imagem'] = str_replace(' ', '_', strtolower($data['nome'])).'.'.$ext;		
    }

    public function update(Request $request, Response $response)
    {
	    $data          = $request->getParsedBody();
		$uploadedFiles = $request->getUploadedFiles();
		$imagem        = $uploadedFiles['imagem'];
		
		$nomeImagem = '';
		if ($imagem->getError() === UPLOAD_ERR_OK) {
			$nomeImagemAntiga = $this->filmes->pegarNomeImagem($data['id']);
			if($nomeImagemAntiga) {
				$caminhoImagem = 'dist/images/'.$nomeImagemAntiga;
				if (file_exists($caminhoImagem)) {
					unlink($caminhoImagem);
				}
			}			
			$nomeImagem = $imagem->getClientFilename();
			
			$imagemTemp     = $imagem->getStream()->getMetadata('uri');
		    $info           = pathinfo($nomeImagem);
			$extensao       = $info['extension'];
			$caminhoDestino = 'dist/images/' . $nome .'.'. $extensao;

			$imagem = Image::make($imagemTemp);
			$imagem->resize(500, null, function ($constraint) {
				$constraint->aspectRatio();
			});
			$imagem->save($caminhoDestino);
			
			//$imagem->moveTo('dist/images/' . $nomeImagem);		
		}
		
		$data['imagem'] = $nomeImagem;
		
		$result = $this->filmes->Update($data);
		//echo "result = ". $result;
		//return true;		
		
        //if (!$this->usuarios->Find($id)) {
		$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		$jsonResponse->getBody()->write(json_encode(['success' => true, 'message' => 'Editado com sucesso!']));

		return $jsonResponse;
        //}
		
		//try {
			//if($this->usuarios->Update($data)) {			
				//$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(200);
				//$jsonResponse->getBody()->write(json_encode(['success' => true, 'message' => 'Editado com sucesso!']));

				//return $jsonResponse;
			//}
		//} catch (\PDOException $e) {
			//$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			//$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			//return $jsonResponse;
		//} catch (\Exception $e) {
			//$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(500);
			//$jsonResponse->getBody()->write(json_encode(['message' => $e->getMessage(), 'success' => false]));
			//return $jsonResponse;
		//}		
    }

    public function delete(Request $request, Response $response, $args)
    {
		$id = $args['id'];
		
        if (!$this->filmes->Find($id)) {
			$jsonResponse = $response->withHeader('Content-Type', 'application/json')->withStatus(404);
			$jsonResponse->getBody()->write(json_encode(['message' => 'Filme não encontrado', 'success' => false]));

			return $jsonResponse;
        }

		if($this->filmes->Delete($id)) {
			return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
		}
    }
}
