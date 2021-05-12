<?php

namespace App\User\Authorization\Email\ValueObject;

use App\Interfaces\RequestValueObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ActivateEmail implements RequestValueObjectInterface
{
	/**
	 * @Assert\NotNull
	 */
	private $token;

	/**
	 * @return mixed
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @param mixed $token
	 */
	public function setToken($token): void
	{
		$this->token = $token;
	}

}