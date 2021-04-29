<?php

namespace App\User\Repository;

use App\User\Entity\Role;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository implements RoleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

	/**
	 * @param $role
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save($role): Role
	{
		try {
			$this->_em->persist($role);
			$this->_em->flush();

			return $role;
		} catch (UniqueConstraintViolationException $exception) {
			throw new \LogicException(sprintf('Role "%s" already exists', $role->getName()));
		}
	}

	/**
	 * @param string $name
	 * @return Role
	 * @throws NotFoundHttpException
	 */
	public function getRoleByName(string $name): Role
	{
		if(!$role = $this->findOneBy(['name' => $name])){
			throw new NotFoundHttpException(sprintf('Role "%s" not found', $name));
		}

		return $role;
	}

}
