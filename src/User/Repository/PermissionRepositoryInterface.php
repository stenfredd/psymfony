<?php

namespace App\User\Repository;

use App\User\Entity\Permission;
use App\User\Entity\User;
use Doctrine\ORM\NonUniqueResultException;

interface PermissionRepositoryInterface
{

	/**
	 * @param Permission $permission
	 * @return Permission
	 */
	public function save(Permission $permission): Permission;

	/**
	 * @param User $user
	 * @param string $permissionName
	 * @return bool
	 * @throws NonUniqueResultException
	 */
	public function userHasPermission(User $user, string $permissionName): bool;

	/**
	 * @param string $name
	 * @return Permission
	 */
	public function getPermissionByName(string $name): Permission;
}
