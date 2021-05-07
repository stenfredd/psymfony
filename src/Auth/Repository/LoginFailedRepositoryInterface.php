<?php

namespace App\Auth\Repository;

use App\Auth\Entity\LoginFailed;
use App\User\Entity\User;
use DateTime;

interface LoginFailedRepositoryInterface
{
	/**
	 * @param LoginFailed $loginFailed
	 * @return LoginFailed
	 */
	public function save(LoginFailed $loginFailed): LoginFailed;

	/**
	 * @param User $user
	 * @param DateTime $lastTime
	 * @return array
	 */
	public function userFailsCount(User $user, DateTime $lastTime): array;

	/**
	 * @param DateTime $lastTime
	 */
	public function clearOldFails(DateTime $lastTime): void;

}