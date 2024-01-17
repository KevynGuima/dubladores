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
		//$queryBuilder = $this->db->createQueryBuilder();
		$sql = "SELECT
			animes.id, animes.nome,	animes.imagem, animes.data_lancamento,
			CONCAT(COALESCE(GROUP_CONCAT(generos.genero SEPARATOR ', '), ''), ' ') AS generos,
			GROUP_CONCAT(generos.id) AS genero_id
FROM
    animes
LEFT JOIN
    filme_genero ON animes.id = filme_genero.filme_id
LEFT JOIN
    generos ON filme_genero.genero_id = generos.id
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
	
		//$queryBuilder = $this->db->createQueryBuilder();
		$stmt = $this->db->prepare("INSERT INTO filmes (nome, data_lancamento, imagem) VALUES (:nome, :dataLancamento, :nomeImagem)");
		$stmt->bindParam(':nome', $nome);
		$stmt->bindParam(':dataLancamento', $dataLancamento);
		$stmt->bindParam(':nomeImagem', $nomeImagem);
		$result = $stmt->execute();

		// Obtenha o último ID inserido (filme_id)
		$filme_id = $this->db->lastInsertId();
		
		if ($result) {
			foreach ($data['generos'] as $genero_id) {
				$stmt = $this->db->prepare("INSERT INTO filme_genero (filme_id, genero_id) VALUES (:filme_id, :genero_id)");
				$stmt->bindParam(':filme_id', $filme_id);
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
			->from('filmes')
			->where('id = :id')
			->setParameter('id', $id)
			->execute()
			->fetchOne();

		return $imagem;	
	}
	
    public function Update($data) : bool
    {
		$filme_id        = $data['id'];
		$nome            = $this->helper->MB_CASE_TITLE_BR($data['nome']);
		$data_lancamento = $data['dataLancamento'];
		$generos         = $data['generos'];
		$imagem          = $data['imagem'];

		$queryBuilder = $this->db->createQueryBuilder();

		$queryBuilder
		  ->update('filmes', 'f')
		  ->set('f.nome', ':nome')
		  ->set('f.data_lancamento', ':data_lancamento')
		  ->where('f.id = :id')
		  ->setParameter('nome', $nome)
		  ->setParameter('data_lancamento', $data_lancamento)
		  ->setParameter('id', $filme_id, \PDO::PARAM_INT);
		  
		if (!empty($imagem)) {
			$queryBuilder->set('imagem', ':imagem')->setParameter('imagem', $imagem);
		}
		$queryBuilder->execute();		

		$queryBuilder
			->delete('filme_genero')
			->where('filme_id = :id')
			->setParameter('id', $filme_id, \PDO::PARAM_INT)
			->execute();
		
		foreach ($generos as $genero_id) {
			$queryBuilder
				->insert('filme_genero')
				->values(['filme_id' => ':filme_id', 'genero_id' => ':genero_id'])
				->setParameter('filme_id', $filme_id)
				->setParameter('genero_id', $genero_id)
				->execute();		
		}
			
		return true;
	}	
	
	public function Delete($id)
	{
		//antes de deletar um filme precisa deletar o relacionamento dele na tabela filme_genero
		//outra maneira de fazer isso é usar cascade para ser automatico:
		//ALTER TABLE filme_genero
		//ADD CONSTRAINT FK_AD803B2FE6E418AD
		//FOREIGN KEY (filme_id)
		//REFERENCES filmes(id)
		//ON DELETE CASCADE;

		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
			->delete('filme_genero')
			->where('filme_id = :id')
			->setParameter('id', $id, \PDO::PARAM_INT)
			->execute();
	
		$rowCount = $this->db->executeStatement('DELETE FROM filmes WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		$queryBuilder = $this->db->createQueryBuilder();
		$result = $queryBuilder->select('*')
			->from('filmes')
			->where('id = :id')
			->setParameter('id', $id)
			->execute();

		$filme = $result->fetch();

		return $filme;
	}
}
