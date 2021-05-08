<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Service;


use App\User\Authorization\Email\Entity\PasswordResetToken;
use App\User\Authorization\Email\Repository\PasswordResetTokenRepository;
use App\User\Entity\User;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

class PasswordResetTokenService
{

	/**
	 * @var PasswordResetTokenRepository
	 */
	private $emailPasswordResetTokenRepository;

	/**
	 * @var int
	 */
	private $passwordResetTokenLifetime;


	/**
	 * EmailPasswordResetTokenService constructor.
	 * @param int $passwordResetTokenLifetime
	 * @param PasswordResetTokenRepository $emailPasswordResetTokenRepository
	 */
	public function __construct(int $passwordResetTokenLifetime, PasswordResetTokenRepository $emailPasswordResetTokenRepository)
	{
		$this->passwordResetTokenLifetime = $passwordResetTokenLifetime;
		$this->emailPasswordResetTokenRepository = $emailPasswordResetTokenRepository;
	}

	/**
	 * @param User $user
	 * @return PasswordResetToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws Exception
	 */
	public function createEmailPasswordResetToken(User $user)
	{
		$tokenString = $this->generateEmailPasswordResetToken($user);
		$expiredAt = new DateTime(sprintf('+%d second', $this->passwordResetTokenLifetime ));

		$token = new PasswordResetToken();
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
	 * @return PasswordResetToken
	 */
	public function getTokenByValue(string $token): PasswordResetToken
	{
		return $this->emailPasswordResetTokenRepository->getTokenByValue($token);
	}

	/**
	 * @param PasswordResetToken $token
	 */
	public function checkTokenExpired(PasswordResetToken $token): void
	{
		$now = new DateTime('now');
		$expired_at = $token->getExpiredAt();

		if ($now >= $expired_at) {
			throw new \InvalidArgumentException('Reset token expired');
		}
	}
}