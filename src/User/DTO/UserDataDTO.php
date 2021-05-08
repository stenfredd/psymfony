<?php

declare(strict_types=1);

namespace App\User\DTO;

use App\User\Entity\UserData;

class UserDataDTO
{
	/**
	 * @param array $data
	 * @return UserData
	 */
	public static function fromArray(array $data): UserData
	{
		$userData = new UserData();
		$userData->setNickname($data['nickname']);

		return $userData;
	}
}