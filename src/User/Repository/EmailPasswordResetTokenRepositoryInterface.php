<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\EmailPasswordResetToken;
use App\User\Entity\User;

interface EmailPasswordResetTokenRepositoryInterface
{

	/**
	 * @param EmailPasswordResetToken $token
	 * @return EmailPasswordResetToken
	 */
	public function save(EmailPasswordResetToken $token): EmailPasswordResetToken;

	/**
	 * @param string $token
	 * @return EmailPasswordResetToken
	 */
	public function getTokenByValue(string $token): EmailPasswordResetToken;

	/**
	 * @param User $user
	 */
	public function deleteUserTokens(User $user): void;
}
