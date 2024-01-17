<?php
declare(strict_types=1);

namespace App\Models;

use DI\Container;

class UserModel
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
		$queryBuilder->select('*')->from('usuarios');
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
		$nome           = $data['nome'];
		$email          = $data['email'];
		$senha          = password_hash($data['senha'], PASSWORD_BCRYPT);
		$tokenValidacao = bin2hex(random_bytes(32));
		$dataNascimento = $data['dataNascimento'];
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
			->insert('usuarios')
			->values([
				'nome' => '?',
				'email' => '?',
				'senha' => '?',
				'token_validacao' => '?',
				'data_nascimento' => '?'
			])
			->setParameter(0, $nome)
			->setParameter(1, $email)
			->setParameter(2, $senha)
			->setParameter(3, $tokenValidacao)
			->setParameter(4, $dataNascimento);

		$statement = $queryBuilder->executeStatement();
		
		if ($statement) {
			return true;
		}
		
		return false;
	}
	
    public function Update(array $data) : bool
    {		
		$id             = $data['id'];
		$nome           = $data['nome'];
		$email          = $data['email'];
		$senha          = password_hash($data['senha'], PASSWORD_BCRYPT);
		$tokenValidacao = bin2hex(random_bytes(32));
		$dataNascimento = $data['dataNascimento'];
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
            ->update('usuarios')
            ->set('nome', '?')
            ->set('email', '?')
            ->set('senha', '?')
            ->set('data_nascimento', '?')
            ->where('id = ?')
            ->setParameter(0, $nome)
            ->setParameter(1, $email)
            ->setParameter(2, $senha)
            ->setParameter(3, $dataNascimento)
            ->setParameter(4, $id, \PDO::PARAM_INT);

        $statement = $queryBuilder->executeStatement();
		
		echo $queryBuilder->getSQL();

		if ($statement > 0) {
			//return true;
		}
		
		return false;
	}	
	
	public function Delete($id)
	{
		$rowCount = $this->db->executeStatement('DELETE FROM usuarios WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		return $this->db->fetchOne('SELECT nome FROM usuarios WHERE id = ?', [$id]);
	}
}
