<?php
declare(strict_types=1);

namespace App\Models;

use DI\Container;
use App\Helpers;

class CategoriaModel
{
    private $db;
    private $container;
    private $helper;

    public function __construct(Container $container) 
	{
        $this->db     = $container->get('db');
		$this->helper = $container->get('Helpers');
    }	

    public function All()
    {
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder->select('*')->from('categorias');
		$queryBuilder->orderBy('categoria', 'ASC');

		$result = $queryBuilder->execute();

		if ($result->rowCount() > 0) {
			return $result->fetchAll();
		} else {
			return [];
		}
    }
	
    public function Insert($data) : bool
    {	
		$categoria = $this->helper->MB_CASE_TITLE_BR($data['categoria']);
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
			->insert('categorias')
			->values([
				'categoria' => '?',
			])
			->setParameter(0, $categoria);

		$statement = $queryBuilder->executeStatement();
		
		if ($statement) {
			return true;
		}
		
		return false;
	}
	
    public function Update(array $data) : bool
    {		
		$id        = $data['id'];
		$categoria = $this->helper->MB_CASE_TITLE_BR($data['categoria']);
	
		$queryBuilder = $this->db->createQueryBuilder();
		$queryBuilder
            ->update('categorias')
            ->set('categoria', '?')
            ->where('id = ?')
            ->setParameter(0, $categoria)
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
		$rowCount = $this->db->executeStatement('DELETE FROM categorias WHERE id = ?', [$id]);

		if ($rowCount > 0) {
			return true;
		}

		return false;
	}
	
	public function Find($id)
	{
		return $this->db->fetchOne('SELECT categoria FROM categorias WHERE id = ?', [$id]);
	}
}
