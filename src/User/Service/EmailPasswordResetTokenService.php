<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\EmailPasswordResetToken;
use App\User\Entity\User;
use App\User\Repository\EmailPasswordResetTokenRepositoryInterface;
use DateTime;
use Exception;

class EmailPasswordResetTokenService
{

	/**
	 * @var EmailPasswordResetTokenRepositoryInterface
	 */
	private $emailPasswordResetTokenRepository;

	/**
	 * @var int
	 */
	private $passwordResetTokenLifetime;


	/**
	 * EmailPasswordResetTokenService constructor.
	 * @param int $passwordResetTokenLifetime
	 * @param EmailPasswordResetTokenRepositoryInterface $emailPasswordResetTokenRepository
	 */
	public function __construct(int $passwordResetTokenLifetime, EmailPasswordResetTokenRepositoryInterface $emailPasswordResetTokenRepository)
	{
		$this->passwordResetTokenLifetime = $passwordResetTokenLifetime;
		$this->emailPasswordResetTokenRepository = $emailPasswordResetTokenRepository;
	}

	/**
	 * @param User $user
	 * @return EmailPasswordResetToken
	 * @throws Exception
	 */
	public function createEmailPasswordResetToken(User $user)
	{
		$tokenString = $this->generateEmailPasswordResetToken($user);
		$expiredAt = new DateTime(sprintf('+%d second', $this->passwordResetTokenLifetime ));

		$token = new EmailPasswordResetToken();
		$token->setHolder($user);
		$token->setToken($tokenString);
		$token->setExpiredAt($expiredAt);

		$this->emailPasswordResetTokenRepository->save($token);

		return $token;
	}

	/**
	 * @param User $user
	 * @return string
	 * @throws Exception
	 */
	private function generateEmailPasswordResetToken(User $user): string
	{
		return bin2hex(random_bytes(57)).str_pad(((string) $user->getId()), 13, '0', STR_PAD_LEFT);
	}

	/**
	 * @param $token
	 * @return User
	 */
	public function getUserByToken($token): User
	{
		$token = $this->emailPasswordResetTokenRepository->getTokenByValue($token);
		return $token->getHolder();
	}

	/**
	 * @param User $user
	 */
	public function deleteAllUserTokens(User $user): void
	{
		$this->emailPasswordResetTokenRepository->deleteUserTokens($user);
	}

	/**
	 * @param string $token
	 * @return EmailPasswordResetToken
	 */
	public function getTokenByValue(string $token): EmailPasswordResetToken
	{
		return $this->emailPasswordResetTokenRepository->getTokenByValue($token);
	}

	/**
	 * @param EmailPasswordResetToken $token
	 */
	public function checkTokenExpired(EmailPasswordResetToken $token): void
	{
		$now = new DateTime('now');
		$expired_at = $token->getExpiredAt();

		if ($now >= $expired_at) {
			throw new \InvalidArgumentException('Reset token expired');
		}
	}
}