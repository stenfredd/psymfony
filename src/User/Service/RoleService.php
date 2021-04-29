<?php

namespace App\User\Service;

use App\User\Entity\Role;
use App\User\Repository\RoleRepositoryInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleService
{

	/** @var RoleRepositoryInterface */
	private $roleRepository;

	/** @var PermissionService */
	private $permissionService;

	/**
	 * RoleService constructor.
	 * @param RoleRepositoryInterface $roleRepository
	 * @param PermissionService $permissionService
	 */
	public function __construct(RoleRepositoryInterface $roleRepository, PermissionService $permissionService)
	{
		$this->roleRepository = $roleRepository;
		$this->permissionService = $permissionService;
	}

	/**
	 * @param string $name
	 * @param string $description
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function createRole(string $name, string $description): Role
	{
		$role = new Role;

		$role->setName($name);
		$role->setDescription($description);

		$this->roleRepository->save($role);

		return $role;
	}


	/**
	 * @param $names
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function getRolesByNames($names): array
	{
		$roles = [];
		if(count($names) > 0){
			foreach ($names as $cName) {
				$roles[] = $this->roleRepository->getRoleByName($cName);
			}
		}

		return $roles;
	}

	/**
	 * @param Role $role
	 * @param array $permissionsNames
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function setPermissionsByNames(Role $role, array $permissionsNames): Role
	{
		$permissions = $this->permissionService->getPermissionsByNames($permissionsNames);

		if(count($permissions) > 0){
			foreach ($permissions as $cPermission) {
				$role->addPermission($cPermission);
			}
		}

		$this->roleRepository->save($role);

		return $role;
	}


}