<?php

declare(strict_types=1);

namespace App\User\Service;

use App\Auth\Service\AuthService;
use App\Auth\Service\TokenService;
use App\Auth\ValueObject\ResetPassword;
use App\Auth\ValueObject\SignUp;
use App\User\DTO\UserDataDTO;
use App\User\Entity\User;
use App\User\Repository\PermissionRepositoryInterface;
use App\User\Repository\UserDataRepository;
use App\User\Repository\UserRepositoryInterface;
use App\Auth\Security\PasswordEncoder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
	 * @var AuthService
	 */
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
	 * @var UserDataRepository
	 */
	private $userDataRepository;

	/**
	 * @var EmailPasswordResetTokenService
	 */
	private $emailPasswordResetTokenService;

	/**
	 * @var EmailActivationTokenService
	 */
	private $emailActivationTokenService;

	/**
	 * @var UrlGeneratorInterface
	 */
	private $router;

	/**
	 * @var Security
	 */
	private $security;

	/**
	 * @var UserNotificator
	 */
	private $userNotificator;

	/**
	 * UserService constructor.
	 * @param UserRepositoryInterface $userRepository
	 * @param PasswordEncoder $passwordEncoder
	 * @param RoleService $roleService
	 * @param TokenService $tokenService
	 * @param AuthService $authService
	 * @param EntityManagerInterface $entityManager
	 * @param PermissionRepositoryInterface $permissionRepository
	 * @param UserDataRepository $userDataRepository
	 * @param EmailActivationTokenService $emailActivationTokenService
	 * @param EmailPasswordResetTokenService $emailPasswordResetTokenService
	 * @param UrlGeneratorInterface $router
	 * @param Security $security
	 * @param UserNotificator $userNotificator
	 */
	public function __construct(
		UserRepositoryInterface $userRepository,
		PasswordEncoder $passwordEncoder,
		RoleService $roleService,
		TokenService $tokenService,
		AuthService $authService,
		EntityManagerInterface $entityManager,
		PermissionRepositoryInterface $permissionRepository,
		UserDataRepository $userDataRepository,
		EmailActivationTokenService $emailActivationTokenService,
		EmailPasswordResetTokenService $emailPasswordResetTokenService,
		UrlGeneratorInterface $router,
		Security $security,
		UserNotificator $userNotificator
	)
	{
		$this->passwordEncoder = $passwordEncoder;
		$this->userRepository = $userRepository;
		$this->roleService = $roleService;
		$this->tokenService = $tokenService;
		$this->authService = $authService;
		$this->entityManager = $entityManager;
		$this->permissionRepository = $permissionRepository;
		$this->userDataRepository = $userDataRepository;
		$this->emailActivationTokenService = $emailActivationTokenService;
		$this->emailPasswordResetTokenService = $emailPasswordResetTokenService;
		$this->router = $router;
		$this->security = $security;
		$this->userNotificator = $userNotificator;
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
	 * @param SignUp $signUpVO
	 * @return User
	 * @throws Throwable
	 */
	public function signUpEmail(SignUp $signUpVO): User
	{
		$email = $signUpVO->getEmail();
		$password = $signUpVO->getPassword();
		$data = ['nickname' => $signUpVO->getNickname()];

		return $this->entityManager->transactional(function() use ($email, $password, $data) {
			$user = $this->createUser($email, $password, ['ROLE_USER']);
			$this->setUserData($user, $data);

			$activationToken = $this->emailActivationTokenService->createEmailActivationToken($user);
			$activationLink = $this->router->generate('activate_email', ['token' => $activationToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

			$this->userNotificator->signUpEmailNotification($user, $activationLink, $password);

			return $this->userRepository->save($user);
		});
	}

	/**
	 * @param User $user
	 * @throws TransportExceptionInterface
	 * @throws Exception
	 */
	public function resendActivationLinkEmail(User $user): void
	{
		$this->emailActivationTokenService->deleteAllUserTokens($user);
		$activationToken = $this->emailActivationTokenService->createEmailActivationToken($user);
		$activationLink = $this->router->generate('activate_email', ['token' => $activationToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

		$this->userNotificator->activationLinkEmail($user, $activationLink);
	}

	/**
	 * @param $token
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function activateUserWithToken($token): void
	{
		$token = $this->emailActivationTokenService->getTokenByValue($token);
		$this->emailActivationTokenService->checkTokenExpired($token);

		$user = $token->getHolder();
		$this->emailPasswordResetTokenService->deleteAllUserTokens($user);

		$this->activateUser($user);
	}

	/**
	 * @param User $user
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function activateUser(User $user): void
	{
		$user->setActive(User::ACTIVE);
		$this->userRepository->save($user);
	}

	/**
	 * @param ResetPassword $resetPasswordVO
	 * @throws TransportExceptionInterface
	 * @throws Exception
	 */
	public function sendNewResetPasswordLink(ResetPassword $resetPasswordVO): void
	{
		$email = $resetPasswordVO->getEmail();

		$user = $this->getByEmail($email);

		$this->emailPasswordResetTokenService->deleteAllUserTokens($user);

		$resetToken = $this->emailPasswordResetTokenService->createEmailPasswordResetToken($user);
		$resetPasswordLink = $this->router->generate('reset_password_confirm', ['token' => $resetToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

		$this->userNotificator->resetPasswordLink($user, $resetPasswordLink);
	}

	/**
	 * @param $token
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws TransportExceptionInterface
	 * @throws Exception
	 */
	public function resetPasswordByToken(string $token): void
	{
		$token = $this->emailPasswordResetTokenService->getTokenByValue($token);
		$this->emailPasswordResetTokenService->checkTokenExpired($token);

		$user = $token->getHolder();
		$this->emailPasswordResetTokenService->deleteAllUserTokens($user);

		$password = bin2hex(random_bytes(4));
		$user->setPassword($this->getEncodedPassword($user, $password));
		$this->userRepository->save($user);

		$this->userNotificator->resetPasswordSuccess($user, $password);
	}

}