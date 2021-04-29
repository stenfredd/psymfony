<?php

namespace App\Auth\Service;

use App\User\Entity\User;
use App\User\Repository\UserRepositoryInterface;
use App\Auth\Security\PasswordEncoder;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
	/** @var UserRepositoryInterface */
	private $userRepository;

	/** @var PasswordEncoder */
	private $passwordEncoder;

	/** @var TokenService */
	private $tokenService;

	/**
	 * AuthService constructor.
	 * @param UserRepositoryInterface $userRepository
	 * @param PasswordEncoder $passwordEncoder
	 * @param TokenService $tokenService
	 */
	public function __construct(
		UserRepositoryInterface $userRepository,
		PasswordEncoder $passwordEncoder,
		TokenService $tokenService
	)
	{
		$this->passwordEncoder = $passwordEncoder;
		$this->userRepository = $userRepository;
		$this->tokenService = $tokenService;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @return string
	 * @throws LogicException
	 */
	public function login(string $email, string $password): string
	{
		try {
			$email = strtolower($email);
			$user = $this->userRepository->getByEmail($email);
			$this->verifyPassword($user, $password);
			$token = $this->tokenService->createToken($user);

			return $token->getValue();
		}
		catch (AuthenticationException $e){
			//TODO: wait
			throw new HttpException(403, "Invalid username or password");
		}
		catch (NotFoundHttpException $e) {
			throw new HttpException(403, "Invalid username or password");
		}
		catch (\Exception $e) {
			throw new \LogicException('Authentication failed');
		}
	}

	/**
	 * @param string $token
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function deleteAuthToken(string $token): void
	{
		$auth_token = $this->tokenService->getByTokenValue($token);
		$this->tokenService->deleteToken($auth_token);
	}

	/**
	 * @param User $user
	 * @param string $password
	 */
	public function verifyPassword(User $user, string $password): void
	{
		if(!$this->passwordEncoder->isPasswordValid($user, $password)){
			throw new AuthenticationException('Password incorrect');
		}
	}
}