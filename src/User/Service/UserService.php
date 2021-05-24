<?php

declare(strict_types=1);

namespace App\User\Service;


use App\User\Authorization\System\Security\PasswordEncoder;
use App\User\Authorization\System\Service\TokenService;
use App\User\DTO\UserDataDTO;
use App\User\Entity\User;
use App\User\Repository\PermissionRepositoryInterface;
use App\User\Repository\UserDataRepository;
use App\User\Repository\UserRepositoryInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use Symfony\Component\Security\Core\Security;

class UserService
{
	/**
	 * @var UserRepositoryInterface
	 */
	private $userRepository;

	/**
	 * @var PasswordEncoder
	 */
	private $passwordEncoder;

	/**
	 * @var TokenService
	 */
	private $tokenService;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @var PermissionRepositoryInterface
	 */
	private $permissionRepository;

	/**
	 * @var RoleService
	 */
	private $roleService;

	/**
	 * UserService constructor.
	 * @param UserRepositoryInterface $userRepository
	 * @param PasswordEncoder $passwordEncoder
	 * @param RoleService $roleService
	 * @param TokenService $tokenService
	 * @param EntityManagerInterface $entityManager
	 * @param PermissionRepositoryInterface $permissionRepository
	 */
	public function __construct(
		UserRepositoryInterface $userRepository,
		PasswordEncoder $passwordEncoder,
		RoleService $roleService,
		TokenService $tokenService,
		EntityManagerInterface $entityManager,
		PermissionRepositoryInterface $permissionRepository
	)
	{
		$this->passwordEncoder = $passwordEncoder;
		$this->userRepository = $userRepository;
		$this->roleService = $roleService;
		$this->tokenService = $tokenService;
		$this->entityManager = $entityManager;
		$this->permissionRepository = $permissionRepository;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param array $roles
	 * @return User
	 * @throws Throwable
	 */
	public function createUser(string $email, string $password, array $roles): User
	{
		return $this->entityManager->transactional(function() use ($email, $password, $roles) {
			$user = $this->getNewUser($email, $password);
			$this->setRolesByNames($user, $roles);

			return $this->userRepository->save($user);
		});
	}

	/**
	 * @param int $id
	 */
	public function deleteUser(int $id): void
	{
		$user = $this->userRepository->getById($id);
		$this->userRepository->delete($user);
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @return User
	 */
	public function getNewUser(string $email, string $password): User
	{
		$user = new User;
		$user->setEmail($email);
		$user->setPassword($this->getEncodedPassword($user, $password));

		return $user;
	}

	/**
	 * @param string $token
	 * @return User
	 */
	public function getByToken(string $token): User
	{
		return $this->tokenService->getUserByToken($token);
	}

	/**
	 * @param int $id
	 * @return User
	 */
	public function getById(int $id): User
	{
		return $this->userRepository->getById($id);
	}

	/**
	 * @param string $email
	 * @return User
	 */
	public function getByEmail(string $email): User
	{
		return $this->userRepository->getByEmail($email);
	}

	/**
	 * @param User $user
	 * @param array $rolesNames
	 * @return User
	 */
	public function setRolesByNames(User $user, array $rolesNames): User
	{
		$roles = $this->roleService->getRolesByNames($rolesNames);

		if(count($roles) > 0){
			foreach ($roles as $cRole) {
				$user->addRole($cRole);
			}
		}

		return $user;
	}

	/**
	 * @param User $user
	 * @param array $data
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function setUserData(User $user, array $data)
	{
		$userData = UserDataDTO::fromArray($data);
		$user->setUserData($userData);

		$this->userRepository->save($user);
	}

	/**
	 * @param User $user
	 * @param string $password
	 * @return string
	 */
	public function getEncodedPassword(User $user, string $password): string
	{
		return $this->passwordEncoder->encodePassword($user, $password);
	}

	/**
	 * @param User $user
	 * @param string $permissionName
	 * @return bool
	 * @throws NonUniqueResultException
	 */
	public function hasPermission(User $user, string $permissionName): bool
	{
		return $this->permissionRepository->userHasPermission($user, $permissionName);
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function getPermissions(User $user): array
	{
		$result = [];

		$roles = $user->getRolesCollection();
		if (count($roles) > 0) {
			foreach ($roles as $cRole) {
				$rolePermissions = $cRole->getPermissions();

				if (count($rolePermissions) > 0) {
					foreach ($rolePermissions as $cPermission) {
						$result[] = $cPermission;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function getUserData(User $user): array
	{
		$permissions = $this->getPermissions($user);

		$permissions_strings = [];
		if ($permissions > 0) {
			foreach ($permissions as $cPermission) {
				$permissions_strings[] = $cPermission->getName();
			}
		}

		return [
			'id' => $user->getId(),
			'active' => $user->getActive(),
			'email' => $user->getEmail(),
			'nickname' => $user->getUserData()->getNickname(),
			'permissions' => $permissions_strings,
			'created_at' => $user->getCreatedAt()->format('d.m.Y H:i')
		];
	}

}