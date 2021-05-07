<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\Permission;
use App\User\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Permission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permission[]    findAll()
 * @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionRepository extends ServiceEntityRepository implements PermissionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

	/**
	 * @param $permission
	 * @return Permission
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save($permission): Permission
	{
		$this->_em->persist($permission);
		$this->_em->flush();

		return $permission;
	}

	/**
	 * @param User $user
	 * @param string $permissionName
	 * @return bool
	 * @throws NonUniqueResultException
	 */
	public function userHasPermission(User $user, string $permissionName): bool
	{
		$rsm = new ResultSetMapping();
		$rsm->addEntityResult(Permission::class, 'p');
		$rsm->addFieldResult('p', 'id', 'id');

		$sql = '
			select 
				p.id 
			from 
				"permission" p 
				inner join role_permission rp on (rp.permission_id = p.id)
				inner join user_role ur on (rp.role_id = ur.role_id)	
			where 
				p."name" = :permissionName
				and 
				ur.user_id = :user_id
		';

		$entityManager = $this->getEntityManager();

		$query = $entityManager->createNativeQuery($sql, $rsm);
		$query->setParameter(':user_id', $user->getId());
		$query->setParameter(':permissionName', $permissionName);

		$result = $query->getOneOrNullResult();

		if($result === null){
			return false;
		}
		return true;
	}

	/**
	 * @param string $name
	 * @return Permission
	 */
	public function getPermissionByName(string $name): Permission
	{
		if(!$permission = $this->findOneBy(['name' => $name])){
			throw new NotFoundHttpException(sprintf('Role "%s" not found', $name));
		}

		return $permission;
	}

}
