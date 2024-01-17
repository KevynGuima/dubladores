<?php
declare(strict_types=1);

namespace App\Models;

use DI\Container;
use App\Helpers;

class GeneroModel
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
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder->select('*')->from('generos');
		$queryBuilder->orderBy('genero', 'ASC');

		$result = $queryBuilder->execute();

		if ($result->rowCount() > 0) {
			return $result->fetchAll();
		} else {
			return [];
		}
    }
	
    public function Insert($data) : bool
    {	
		$genero = $this->helper->MB_CASE_TITLE_BR($data['genero']);
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
			->insert('generos')
			->values([
				'genero' => '?',
			])
			->setParameter(0, $genero);

		$statement = $queryBuilder->executeStatement();
		
		if ($statement) {
			return true;
		}
		
		return false;
	}
	
    public function Update(array $data) : bool
    {		
		$id        = $data['id'];
		$genero = $this->helper->MB_CASE_TITLE_BR($data['genero']);
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
            ->update('generos')
            ->set('genero', '?')
            ->where('id = ?')
            ->setParameter(0, $genero)
            ->setParameter(1, $id, \PDO::PARAM_INT);

        $statement = $queryBuilder->executeStatement();
		
		//echo $queryBuilder->getSQL();

		if ($statement > 0) {
			return true;
		}
		
		return false;
	}	
	
	public function Delete($id)
	{
		$rowCount = $this->db->executeStatement('DELETE FROM generos WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		return $this->db->fetchOne('SELECT genero FROM generos WHERE id = ?', [$id]);
	}
}
