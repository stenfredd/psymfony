<?php

namespace App\Auth\ValueObject;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPassword
{
	/**
	 * @Assert\Email
	 * @Assert\NotNull
	 */
	private $email;

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail($email): void
	{
		$this->email = strtolower($email);
	}
}