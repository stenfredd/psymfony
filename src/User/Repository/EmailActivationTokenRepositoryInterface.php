<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\EmailActivationToken;

interface EmailActivationTokenRepositoryInterface
{

	/**
	 * @param EmailActivationToken $token
	 * @return EmailActivationToken
	 */
	public function save(EmailActivationToken $token): EmailActivationToken;

	/**
	 * @param string $token
	 * @return EmailActivationToken
	 */
	public function getTokenByValue(string $token): EmailActivationToken;
}
