<?php

namespace App\User\Authorization\Email\ValueObject;

use Symfony\Component\Validator\Constraints as Assert;

class SignUp
{
	/**
	 * @Assert\Email
	 * @Assert\NotNull
	 */
	private $email;

	/**
	 * @Assert\Regex("/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\-\_\=\+]+$/")
	 * @Assert\Length(
	 *      min = 6,
	 *      max = 512
	 * )
	 * @Assert\NotNull
	 */
	private $password;

	/**
	 * @Assert\Regex("/^[a-zA-Z0-9\-\_]+$/")
	 * @Assert\NotNull
	 */
	private $nickname;

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

	/**
	 * @return mixed
	 */
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * @param mixed $nickname
	 */
	public function setNickname($nickname): void
	{
		$this->nickname = $nickname;
	}

}