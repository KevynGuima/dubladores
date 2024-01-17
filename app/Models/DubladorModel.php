<?php
declare(strict_types=1);

namespace App\Models;

use DI\Container;

class DubladorModel
{
    private $db;
    private $container;

    public function __construct(Container $container) 
	{
        $this->db = $container->get('db');
    }	

    public function All()
    {
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder->select('*')->from('dubladores');
		$queryBuilder->orderBy('nome', 'ASC');

		$result = $queryBuilder->execute();

		if ($result->rowCount() > 0) {
			return $result->fetchAll();
		} else {
			return [];
		}
    }
	
    public function Insert($data) : bool
    {	
		$imagem          = $data['imagem'];
		$nome            = $data['nome'];
		$sexo            = $data['sexo'];
		$dataNascimento  = $data['dataNascimento'];
		$dataFalecimento = $data['dataFalecimento'];
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
			->insert('dubladores')
			->values([
				'imagem' => '?',
				'nome' => '?',
				'sexo' => '?',
				'data_nascimento' => '?',
				'data_falecimento' => '?'
			])
			->setParameter(0, $imagem)
			->setParameter(1, $nome)
			->setParameter(2, $sexo)
			->setParameter(3, $dataNascimento)
			->setParameter(4, $dataFalecimento);

		$statement = $queryBuilder->executeStatement();
		
		if ($statement) {
			return true;
		}
		
		return false;
	}
	
    public function Update(array $data) : bool
    {		
		$id              = $data['id'];
		$imagem          = $data['imagem'];
		$nome            = $data['nome'];
		$sexo            = $data['sexo'];
		$dataNascimento  = $data['dataNascimento'];
		$dataFalecimento = $data['dataFalecimento'];
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
            ->update('dubladores')
            ->set('imagem', '?')
            ->set('nome', '?')
            ->set('sexo', '?')
            ->set('data_nascimento', '?')
            ->set('data_falecimento', '?')
            ->where('id = ?')
            ->setParameter(0, $imagem)
            ->setParameter(1, $nome)
            ->setParameter(2, $sexo)
            ->setParameter(3, $dataNascimento)
            ->setParameter(4, $dataFalecimento)
            ->setParameter(5, $id, \PDO::PARAM_INT);

        $statement = $queryBuilder->executeStatement();
		
		//echo $queryBuilder->getSQL();

		if ($statement > 0) {
			return true;
		}
		
		return false;
	}	
	
	public function Delete($id)
	{
		$rowCount = $this->db->executeStatement('DELETE FROM dubladores WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		return $this->db->fetchOne('SELECT nome FROM dubladores WHERE id = ?', [$id]);
	}
}
