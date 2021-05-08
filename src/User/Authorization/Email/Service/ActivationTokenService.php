<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Service;

use App\User\Authorization\Email\Entity\ActivationToken;
use App\User\Authorization\Email\Repository\ActivationTokenRepository;
use App\User\Entity\User;

use DateTime;
use Exception;

class ActivationTokenService
{

	/**
	 * @var ActivationTokenRepository
	 */
	private $activationTokenRepository;

	/**
	 * @var int
	 */
	private $activationTokenLifetime;

	/**
	 * EmailActivation constructor.
	 * @param int $activationTokenLifetime
	 * @param ActivationTokenRepository $activationTokenRepository
	 */
	public function __construct(int $activationTokenLifetime, ActivationTokenRepository $activationTokenRepository)
	{
		$this->activationTokenLifetime = $activationTokenLifetime;
		$this->activationTokenRepository = $activationTokenRepository;
	}

	/**
	 * @param User $user
	 * @return ActivationToken
	 * @throws Exception
	 */
	public function createEmailActivationToken(User $user): ActivationToken
	{
		$tokenString = $this->generateEmailActivationToken($user);
		$expiredAt = new DateTime(sprintf('+%d second', $this->activationTokenLifetime ));

		$token = new ActivationToken();
		$token->setHolder($user);
		$token->setToken($tokenString);
		$token->setExpiredAt($expiredAt);

		$this->activationTokenRepository->save($token);

		return $token;
	}

	/**
	 * @param User $user
	 * @return string
	 * @throws Exception
	 */
	private function generateEmailActivationToken(User $user): string
	{
		return bin2hex(random_bytes(57)).str_pad(((string) $user->getId()), 13, '0', STR_PAD_LEFT);
	}

	/**
	 * @param string $token
	 * @return User
	 */
	public function getUserByToken(string $token): User
	{
		$token = $this->activationTokenRepository->getTokenByValue($token);
		return $token->getHolder();
	}

	/**
	 * @param string $token
	 * @return ActivationToken
	 */
	public function getTokenByValue(string $token): ActivationToken
	{
		return $this->activationTokenRepository->getTokenByValue($token);
	}

	/**
	 * @param User $user
	 */
	public function deleteAllUserTokens(User $user): void
	{
		$this->activationTokenRepository->deleteUserTokens($user);
	}

	/**
	 * @param ActivationToken $token
	 */
	public function checkTokenExpired(ActivationToken $token): void
	{
		$now = new DateTime('now');
		$expired_at = $token->getExpiredAt();

		if ($now >= $expired_at) {
			throw new \InvalidArgumentException('Activation token expired');
		}
	}
}