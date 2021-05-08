<?php

declare(strict_types=1);

namespace App\User\Authorization\System\Service;

use App\User\Authorization\System\Entity\AuthToken;
use App\User\Authorization\System\Repository\AuthTokenRepositoryInterface;
use App\User\Entity\User;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

class TokenService
{

	/**
	 * @var AuthTokenRepositoryInterface
	 */
	private $tokenRepository;

	/**
	 * @var int
	 */
	private $authTokenLifetime;

	/**
	 * TokenService constructor.
	 * @param int $authTokenLifetime
	 * @param AuthTokenRepositoryInterface $tokenRepository
	 */
	public function __construct(int $authTokenLifetime, AuthTokenRepositoryInterface $tokenRepository)
	{
		$this->tokenRepository = $tokenRepository;
		$this->authTokenLifetime = $authTokenLifetime;
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
		$now = new DateTime('now');
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
	 * @throws Exception
	 */
	public function createToken(User $user): AuthToken
	{
		$expiredAt = new DateTime(sprintf('+%d second', $this->authTokenLifetime));

		$token = new AuthToken();
		$token->setValue($this->generateToken($user));
		$token->setHolder($user);
		$token->setExpiredAt($expiredAt);

		$this->tokenRepository->save($token);

		return $token;
	}

	/**
	 * @param User $user
	 * @return string
	 * @throws Exception
	 */
	public function generateToken(User $user): string
	{
		return bin2hex(random_bytes(114)) . '&' . str_pad(((string) $user->getId()), 13, '0', STR_PAD_LEFT) . round(microtime(true)*1000);
	}

}