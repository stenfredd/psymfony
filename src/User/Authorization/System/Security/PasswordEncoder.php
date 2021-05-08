<?php

declare(strict_types=1);

namespace App\User\Authorization\System\Security;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PasswordEncoder implements UserPasswordEncoderInterface
{
	/**
	 * @var UserPasswordEncoderInterface
	 */
	private $passwordEncoder;

	/**
	 * @var string
	 */
	private $staticSalt;

	/**
	 * PasswordEncoder constructor.
	 * @param string $staticSalt
	 * @param UserPasswordEncoderInterface $passwordEncoder
	 */
	public function __construct(string $staticSalt, UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->staticSalt = $staticSalt;
		$this->passwordEncoder = $passwordEncoder;
	}

	/**
	 * @param UserInterface $user
	 * @param string $plainPassword
	 * @return string
	 */
	public function encodePassword(UserInterface $user, string $plainPassword): string
	{
		$plainPassword = $this->getPasswordWithSalt($plainPassword);

		return $this->passwordEncoder->encodePassword($user, $plainPassword);
	}

	/**
	 * @param string $plainPassword
	 * @return string
	 */
	public function getPasswordWithSalt(string $plainPassword): string
	{
		return $plainPassword.$this->staticSalt;
	}

	/**
	 * @param UserInterface $user
	 * @param string $plainPassword
	 * @return bool
	 */
	public function isPasswordValid(UserInterface $user, string $plainPassword): bool
	{
		$password = $this->getPasswordWithSalt($plainPassword);

		return $this->passwordEncoder->isPasswordValid($user, $password);
	}

	/**
	 * @param UserInterface $user
	 * @return bool
	 */
	public function needsRehash(UserInterface $user): bool
	{
		return $this->passwordEncoder->needsRehash($user);
	}
}
