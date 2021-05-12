<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Service;

use App\User\Authorization\Email\ValueObject\ActivateEmail;
use App\User\Authorization\Email\ValueObject\ResetPassword;
use App\User\Authorization\Email\ValueObject\ResetPasswordConfirm;
use App\User\Authorization\Email\ValueObject\SignUp;
use App\User\Authorization\System\Service\TokenService;
use App\User\Entity\User;
use App\User\Repository\UserRepositoryInterface;
use App\User\Authorization\Email\ValueObject\Login;
use App\User\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\User\Authorization\System\Exception\LoginFailedCountException;
use App\User\Authorization\System\Service\AuthService as SystemAuthService;
use Throwable;

class AuthService
{
	/**
	 * @var UserRepositoryInterface
	 */
	private $userRepository;

	/**
	 * @var TokenService
	 */
	private $tokenService;

	/**
	 * @var SystemAuthService
	 */
	private $authService;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var ActivationTokenService
	 */
	private $activationTokenService;

	/**
	 * @var Notificator
	 */
	private $notificator;

	/**
	 * @var PasswordResetTokenService
	 */
	private $passwordResetTokenService;


	/**
	 * AuthService constructor.
	 * @param UserRepositoryInterface $userRepository
	 * @param TokenService $tokenService
	 * @param SystemAuthService $authService
	 * @param EntityManagerInterface $entityManager
	 * @param UserService $userService
	 * @param ActivationTokenService $activationTokenService
	 * @param PasswordResetTokenService $passwordResetTokenService
	 * @param Notificator $notificator
	 */
	public function __construct(
		UserRepositoryInterface $userRepository,
		TokenService $tokenService,
		SystemAuthService $authService,
		EntityManagerInterface $entityManager,
		UserService $userService,
		ActivationTokenService $activationTokenService,
		PasswordResetTokenService $passwordResetTokenService,
		Notificator $notificator
	)
	{
		$this->userRepository = $userRepository;
		$this->tokenService = $tokenService;
		$this->authService = $authService;
		$this->userService = $userService;
		$this->activationTokenService = $activationTokenService;
		$this->passwordResetTokenService = $passwordResetTokenService;
		$this->entityManager = $entityManager;
		$this->notificator = $notificator;
	}

	/**
	 * @param Login $loginVO
	 * @return string
	 */
	public function login(Login $loginVO): string
	{
		$email = $loginVO->getEmail();
		$password = $loginVO->getPassword();

		try {
			$user = $this->userService->getByEmail($email);
			$this->authService->checkLoginFails($user);
			$this->authService->verifyPassword($user, $password);
			$token = $this->tokenService->createToken($user);

			return $token->getValue();
		}
		catch (AuthenticationException $e){
			throw new HttpException(403, "Invalid username or password");
		}
		catch (NotFoundHttpException $e) {
			throw new HttpException(403, "Invalid username or password");
		}
		catch (LoginFailedCountException $e) {
			throw new HttpException(403, "Too many attempts, try again later");
		}
		catch (Exception $e) {
			throw new LogicException('Authentication failed');
		}
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
			$user = $this->userService->createUser($email, $password, ['ROLE_USER']);
			$this->userService->setUserData($user, $data);

			$activationToken = $this->activationTokenService->createEmailActivationToken($user);
			$this->notificator->signUpEmailNotification($user, $activationToken->getToken(), $password);

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
		$this->activationTokenService->deleteAllUserTokens($user);
		$activationToken = $this->activationTokenService->createEmailActivationToken($user);

		$this->notificator->activationLinkEmail($user, $activationToken->getToken());
	}

	/**
	 * @param ActivateEmail $tokenVO
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function activateUserWithToken(ActivateEmail $tokenVO): void
	{
		$token = $tokenVO->getToken();
		$token = $this->activationTokenService->getTokenByValue($token);
		$this->activationTokenService->checkTokenExpired($token);

		$user = $token->getHolder();
		$this->activationTokenService->deleteAllUserTokens($user);

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
		$user = $this->userService->getByEmail($email);

		$this->passwordResetTokenService->deleteAllUserTokens($user);

		$resetToken = $this->passwordResetTokenService->createEmailPasswordResetToken($user);
		$this->notificator->resetPasswordLink($user, $resetToken->getToken());
	}

	/**
	 * @param ResetPasswordConfirm $resetPasswordVO
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws TransportExceptionInterface
	 */
	public function resetPasswordByToken(ResetPasswordConfirm $resetPasswordVO): void
	{
		$token = $resetPasswordVO->getToken();

		$token = $this->passwordResetTokenService->getTokenByValue($token);
		$this->passwordResetTokenService->checkTokenExpired($token);

		$user = $token->getHolder();
		$this->passwordResetTokenService->deleteAllUserTokens($user);

		$password = bin2hex(random_bytes(4));
		$user->setPassword($this->userService->getEncodedPassword($user, $password));
		$this->userRepository->save($user);

		$this->notificator->resetPasswordSuccess($user, $password);
	}
}