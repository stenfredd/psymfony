<?php

namespace App\Auth\ValueObject;

use Symfony\Component\Validator\Constraints as Assert;

class Login
{
	/**
	 * @Assert\Email
	 * @Assert\NotNull
	 */
	private $email;

	/**
	 * @Assert\Length(
	 *      min = 6,
	 *      max = 512
	 * )
	 */
	private $password;

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

	/**
	 * @return mixed
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword($password): void
	{
		$this->password = $password;
	}
}