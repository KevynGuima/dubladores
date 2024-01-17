<?php
declare(strict_types=1);

namespace App\Models;

use DI\Container;
use App\Helpers;

class SerieModel
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
		//$queryBuilder = $this->db->createQueryBuilder();
		$sql = "SELECT
			series.id, series.nome,	series.imagem, series.temporadas, series.data_lancamento,
			CONCAT(COALESCE(GROUP_CONCAT(generos.genero SEPARATOR ', '), ''), ' ') AS generos,
			GROUP_CONCAT(generos.id) AS genero_id
			FROM
				series
			LEFT JOIN
				genero_serie ON series.id = genero_serie.serie_id
			LEFT JOIN
				generos ON genero_serie.genero_id = generos.id
			GROUP BY
				series.id, series.nome, series.imagem
			ORDER BY
				series.nome ASC;";

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
	
		//$queryBuilder = $this->db->createQueryBuilder();
		$stmt = $this->db->prepare("INSERT INTO series (nome, data_lancamento, imagem, temporadas) VALUES (:nome, :dataLancamento, :nomeImagem, :temporadas)");
		$stmt->bindParam(':nome', $nome);
		$stmt->bindParam(':dataLancamento', $dataLancamento);
		$stmt->bindParam(':nomeImagem', $nomeImagem);
		$stmt->bindParam(':temporadas', $temporadas);
		$result = $stmt->execute();

		// Obtenha o último ID inserido (serie_id)
		$serie_id = $this->db->lastInsertId();
		
		if ($result) {
			foreach ($data['generos'] as $genero_id) {
				$stmt = $this->db->prepare("INSERT INTO genero_serie (serie_id, genero_id) VALUES (:serie_id, :genero_id)");
				$stmt->bindParam(':serie_id', $serie_id);
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
			->from('series')
			->where('id = :id')
			->setParameter('id', $id)
			->execute()
			->fetchOne();

		return $imagem;	
	}
	
    public function Update($data) : bool
    {
		$serie_id        = $data['id'];
		$nome            = $this->helper->MB_CASE_TITLE_BR($data['nome']);
		$data_lancamento = $data['dataLancamento'];
		$generos         = $data['generos'];
		$imagem          = $data['imagem'];

		$queryBuilder = $this->db->createQueryBuilder();

		$queryBuilder
		  ->update('series', 's')
		  ->set('s.nome', ':nome')
		  ->set('s.data_lancamento', ':data_lancamento')
		  ->where('s.id = :id')
		  ->setParameter('nome', $nome)
		  ->setParameter('data_lancamento', $data_lancamento)
		  ->setParameter('id', $serie_id, \PDO::PARAM_INT);
		  
		if (!empty($imagem)) {
			$queryBuilder->set('imagem', ':imagem')->setParameter('imagem', $imagem);
		}
		$queryBuilder->execute();		

		$queryBuilder
			->delete('genero_serie')
			->where('serie_id = :id')
			->setParameter('id', $serie_id, \PDO::PARAM_INT)
			->execute();
		
		foreach ($generos as $genero_id) {
			$queryBuilder
				->insert('genero_serie')
				->values(['serie_id' => ':serie_id', 'genero_id' => ':genero_id'])
				->setParameter('serie_id', $serie_id)
				->setParameter('genero_id', $genero_id)
				->execute();		
		}
			
		return true;
	}	
	
	public function Delete($id)
	{
		//antes de deletar um serie precisa deletar o relacionamento dele na tabela serie_genero
		//outra maneira de fazer isso é usar cascade para ser automatico:
		//ALTER TABLE serie_genero
		//ADD CONSTRAINT FK_AD803B2FE6E418AD
		//FOREIGN KEY (serie_id)
		//REFERENCES series(id)
		//ON DELETE CASCADE;

		/*$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
			->delete('serie_genero')
			->where('serie_id = :id')
			->setParameter('id', $id, \PDO::PARAM_INT)
			->execute();*/
	
		$rowCount = $this->db->executeStatement('DELETE FROM series WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		$queryBuilder = $this->db->createQueryBuilder();
		$result = $queryBuilder->select('*')
			->from('series')
			->where('id = :id')
			->setParameter('id', $id)
			->execute();

		$serie = $result->fetch();

		return $serie;
	}
}
