<?php
declare(strict_types=1);

namespace App\Models;

use DI\Container;
use App\Helpers;

class AnimeModel
{
	private $db;
	private $helper;

	public function __construct(Container $container) 
	{
		$this->db     = $container->get('db');
		$this->helper = $container->get('Helpers');
  }	

	public function All()
	{
		$sql = "SELECT
		animes.id, animes.nome,	animes.imagem, animes.temporadas, animes.data_lancamento,
		CONCAT(COALESCE(GROUP_CONCAT(generos.genero SEPARATOR ', '), ''), ' ') AS generos,
		GROUP_CONCAT(generos.id) AS genero_id
		FROM
			animes
		LEFT JOIN
		  anime_genero ON animes.id = anime_genero.anime_id
		LEFT JOIN
			generos ON anime_genero.genero_id = generos.id
		GROUP BY
			animes.id, animes.nome, animes.imagem
		ORDER BY
			animes.nome ASC;";

		$stmt   = $this->db->query($sql);
		$result = $stmt->fetchAll();

		if ($stmt->rowCount() > 0) {
			return $result;
		} else {
			return [];
		}
	}
	
    public function Insert(array $data, $nomeImagem) : bool
    {
		$nome           = $this->helper->MB_CASE_TITLE_BR($data['nome']);
		$dataLancamento = $data['dataLancamento'];
		$temporadas     = $data['temporadas'];
	
		$stmt = $this->db->prepare("INSERT INTO animes (nome, data_lancamento, imagem, temporadas) VALUES (:nome, :dataLancamento, :nomeImagem, :temporadas)");
		$stmt->bindParam(':nome', $nome);
		$stmt->bindParam(':dataLancamento', $dataLancamento);
		$stmt->bindParam(':nomeImagem', $nomeImagem);
		$stmt->bindParam(':temporadas', $temporadas);
		$result = $stmt->execute();

		$anime_id = $this->db->lastInsertId();
		
		if ($result) {
			foreach ($data['generos'] as $genero_id) {
				$stmt = $this->db->prepare("INSERT INTO anime_genero (anime_id, genero_id) VALUES (:anime_id, :genero_id)");
				$stmt->bindParam(':anime_id', $anime_id);
				$stmt->bindParam(':genero_id', $genero_id);
				$stmt->execute();
			}
			
			return true;
		}
		
		return false;
	}
	
	public function pegarNomeImagem($id) 
	{
		$queryBuilder = $this->db->createQueryBuilder();
		$imagem = $queryBuilder->select('imagem')
			->from('animes')
			->where('id = :id')
			->setParameter('id', $id)
			->execute()
			->fetchOne();

		return $imagem;	
	}
	
    public function Update($data) : bool
    {
		$anime_id        = $data['id'];
		$nome            = $this->helper->MB_CASE_TITLE_BR($data['nome']);
		$data_lancamento = $data['dataLancamento'];
		$generos         = $data['generos'];
		$imagem          = $data['imagem'];

		$queryBuilder = $this->db->createQueryBuilder();

		$queryBuilder
		  ->update('animes', 's')
		  ->set('s.nome', ':nome')
		  ->set('s.data_lancamento', ':data_lancamento')
		  ->where('s.id = :id')
		  ->setParameter('nome', $nome)
		  ->setParameter('data_lancamento', $data_lancamento)
		  ->setParameter('id', $anime_id, \PDO::PARAM_INT);
		  
		if (!empty($imagem)) {
			$queryBuilder->set('imagem', ':imagem')->setParameter('imagem', $imagem);
		}
		$queryBuilder->execute();		

		$queryBuilder
			->delete('anime_genero')
			->where('anime_id = :id')
			->setParameter('id', $anime_id, \PDO::PARAM_INT)
			->execute();
		
		foreach ($generos as $genero_id) {
			$queryBuilder
				->insert('anime_genero')
				->values(['anime_id' => ':anime_id', 'genero_id' => ':genero_id'])
				->setParameter('anime_id', $anime_id)
				->setParameter('genero_id', $genero_id)
				->execute();		
		}
			
		return true;
	}	
	
	public function Delete($id)
	{
		$rowCount = $this->db->executeStatement('DELETE FROM animes WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		$queryBuilder = $this->db->createQueryBuilder();
		$result = $queryBuilder->select('*')
			->from('animes')
			->where('id = :id')
			->setParameter('id', $id)
			->execute();

		$anime = $result->fetch();

		return $anime;
	}
}
