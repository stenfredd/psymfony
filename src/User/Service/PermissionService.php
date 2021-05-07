<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\Permission;
use App\User\Repository\PermissionRepositoryInterface;

class PermissionService
{

	/**
	 * @var PermissionRepositoryInterface
	 */
	private $permissionRepository;


	/**
	 * PermissionService constructor.
	 * @param PermissionRepositoryInterface $permissionRepository
	 */
	public function __construct(PermissionRepositoryInterface $permissionRepository)
	{
		$this->permissionRepository = $permissionRepository;
	}


	/**
	 * @param string $name
	 * @param string $description
	 * @return Permission
	 */
	public function createPermission(string $name, string $description): Permission
	{
		$permission = new Permission();

		$permission->setName($name);
		$permission->setDescription($description);

		$this->permissionRepository->save($permission);

		return $permission;
	}

	/**
	 * @param array $names
	 * @return array
	 */
	public function getPermissionsByNames(array $names): array
	{
		$permissions = [];
		if(count($names) > 0){
			foreach ($names as $cName) {
				$permissions[] = $this->permissionRepository->getPermissionByName($cName);
			}
		}

		return $permissions;
	}

}