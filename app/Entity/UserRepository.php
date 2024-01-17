<?php
declare(strict_types=1);

// UserRespository.php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findAllUsers()
    {
        return $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();
    }
	
    public function insert(User $user)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }
	
    public function update(User $user)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->merge($user);
        $entityManager->flush();
    }

    public function delete(User $user)
    {
        try {
			$entityManager = $this->getEntityManager();
		    if (!$entityManager->contains($user)) {
				$user = $entityManager->merge($user);
			}
            $entityManager->remove($user);
            $entityManager->flush();
            return true; // Operação bem-sucedida
        } catch (\Exception $e) {
            return false; // Tratar erro, log, etc.
        }
    }
	
    public function findUserById(int $userId)
    {
        return $this->find($userId);
    }	
}
