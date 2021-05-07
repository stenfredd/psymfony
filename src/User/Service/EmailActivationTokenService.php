<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\EmailActivationToken;
use App\User\Entity\User;
use App\User\Repository\EmailActivationTokenRepositoryInterface;
use DateTime;
use Exception;

class EmailActivationTokenService
{

	/**
	 * @var EmailActivationTokenRepositoryInterface
	 */
	private $emailActivationTokenRepository;

	/**
	 * @var int
	 */
	private $activationTokenLifetime;

	/**
	 * EmailActivation constructor.
	 * @param int $activationTokenLifetime
	 * @param EmailActivationTokenRepositoryInterface $emailActivationTokenRepository
	 */
	public function __construct(int $activationTokenLifetime, EmailActivationTokenRepositoryInterface $emailActivationTokenRepository)
	{
		$this->activationTokenLifetime = $activationTokenLifetime;
		$this->emailActivationTokenRepository = $emailActivationTokenRepository;
	}

	/**
	 * @param User $user
	 * @return EmailActivationToken
	 * @throws Exception
	 */
	public function createEmailActivationToken(User $user): EmailActivationToken
	{
		$tokenString = $this->generateEmailActivationToken($user);
		$expiredAt = new DateTime(sprintf('+%d second', $this->activationTokenLifetime ));

		$token = new EmailActivationToken();
		$token->setHolder($user);
		$token->setToken($tokenString);
		$token->setExpiredAt($expiredAt);

		$this->emailActivationTokenRepository->save($token);

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
		$token = $this->emailActivationTokenRepository->getTokenByValue($token);
		return $token->getHolder();
	}

	/**
	 * @param string $token
	 * @return EmailActivationToken
	 */
	public function getTokenByValue(string $token): EmailActivationToken
	{
		return $this->emailActivationTokenRepository->getTokenByValue($token);
	}

	/**
	 * @param User $user
	 */
	public function deleteAllUserTokens(User $user): void
	{
		$this->emailActivationTokenRepository->deleteUserTokens($user);
	}

	/**
	 * @param EmailActivationToken $token
	 */
	public function checkTokenExpired(EmailActivationToken $token): void
	{
		$now = new DateTime('now');
		$expired_at = $token->getExpiredAt();

		if ($now >= $expired_at) {
			throw new \InvalidArgumentException('Activation token expired');
		}
	}
}