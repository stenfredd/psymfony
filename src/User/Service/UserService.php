<?php

namespace App\User\Service;

use App\Auth\Service\AuthService;
use App\Auth\Service\TokenService;
use App\User\Entity\User;
use App\User\Repository\PermissionRepositoryInterface;
use App\User\Repository\UserRepositoryInterface;
use App\Auth\Security\PasswordEncoder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class UserService
{
	/** @var UserRepositoryInterface */
	private $userRepository;

	/** @var PasswordEncoder */
	private $passwordEncoder;

	/** @var TokenService */
	private $tokenService;

	/** @var AuthService */
	private $authService;
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
	 * @param AuthService $authService
	 * @param EntityManagerInterface $entityManager
	 * @param PermissionRepositoryInterface $permissionRepository
	 */
	public function __construct(
		UserRepositoryInterface $userRepository,
		PasswordEncoder $passwordEncoder,
		RoleService $roleService,
		TokenService $tokenService,
		AuthService $authService,
		EntityManagerInterface $entityManager,
		PermissionRepositoryInterface $permissionRepository
	)
	{
		$this->passwordEncoder = $passwordEncoder;
		$this->userRepository = $userRepository;
		$this->roleService = $roleService;
		$this->tokenService = $tokenService;
		$this->authService = $authService;
		$this->entityManager = $entityManager;
		$this->permissionRepository = $permissionRepository;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param array $roles
	 * @return User
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

}