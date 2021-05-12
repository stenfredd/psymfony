<?php

namespace App\User\Authorization\Email\Service;

class RedirectService
{
	/**
	 * @var string
	 */
	private $activationLinkSuccessRedirectTo;

	/**
	 * @var string
	 */
	private $activationLinkFailRedirectTo;

	/**
	 * @var string
	 */
	private $resetPasswordSuccessRedirectTo;

	/**
	 * @var string
	 */
	private $resetPasswordFailRedirectTo;

	public function __construct(
		string $activationLinkSuccessRedirectTo,
		string $activationLinkFailRedirectTo,
		string $resetPasswordSuccessRedirectTo,
		string $resetPasswordFailRedirectTo
	)
	{
		$this->activationLinkSuccessRedirectTo = $activationLinkSuccessRedirectTo;
		$this->activationLinkFailRedirectTo = $activationLinkFailRedirectTo;
		$this->resetPasswordSuccessRedirectTo = $resetPasswordSuccessRedirectTo;
		$this->resetPasswordFailRedirectTo = $resetPasswordFailRedirectTo;
	}

	/**
	 * @return string
	 */
	public function getActivationLinkSuccessRedirectTo(): string
	{
		return $this->activationLinkSuccessRedirectTo;
	}

	/**
	 * @return string
	 */
	public function getActivationLinkFailRedirectTo(): string
	{
		return $this->activationLinkFailRedirectTo;
	}

	/**
	 * @return string
	 */
	public function getResetPasswordSuccessRedirectTo(): string
	{
		return $this->resetPasswordSuccessRedirectTo;
	}

	/**
	 * @return string
	 */
	public function getResetPasswordFailRedirectTo(): string
	{
		return $this->resetPasswordFailRedirectTo;
	}


}