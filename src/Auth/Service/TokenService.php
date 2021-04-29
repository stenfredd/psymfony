<?php

namespace App\Auth\Service;

use App\Auth\Entity\AuthToken;
use App\User\Entity\User;
use App\Auth\Repository\AuthTokenRepositoryInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class TokenService
{

	/** @var AuthTokenRepositoryInterface */
	private $tokenRepository;

	/**
	 * TokenService constructor.
	 * @param AuthTokenRepositoryInterface $tokenRepository
	 */
	public function __construct(AuthTokenRepositoryInterface $tokenRepository)
	{
		$this->tokenRepository = $tokenRepository;
	}

	/**
	 * @param string $token
	 * @return User
	 */
	public function getUserByToken(string $token): User
	{
		$authToken = $this->tokenRepository->getByTokenValue($token);
		return $authToken->getHolder();
	}

	/**
	 * @param string $token
	 * @return AuthToken
	 */
	public function getByTokenValue(string $token): AuthToken
	{
		return $this->tokenRepository->getByTokenValue($token);
	}

	/**
	 * @param AuthToken $authToken
	 * @return bool
	 */
	public function isTokenActual(AuthToken $authToken): bool
	{
		$now = new \DateTime('now');
		$expired_at = $authToken->getExpiredAt();

		if ($now < $expired_at) {
			return true;
		}
		return false;
	}

	/**
	 * @param AuthToken $authToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function deleteToken(AuthToken $authToken): void
	{
		$this->tokenRepository->delete($authToken);
	}

	/**
	 * @param User $user
	 * @return AuthToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws \Exception
	 */
	public function createToken(User $user): AuthToken
	{
		$token = new AuthToken;
		$token->setValue($this->generateToken($user));
		$token->setHolder($user);

		$this->tokenRepository->save($token);

		return $token;
	}

	/**
	 * @param User $user
	 * @return string
	 * @throws \Exception
	 */
	public function generateToken(User $user): string
	{
		return bin2hex(random_bytes(114)) . '&' . str_pad(((string) $user->getId()), 13, '0', STR_PAD_LEFT) . round(microtime(true)*1000);
	}

}