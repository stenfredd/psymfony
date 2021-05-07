<?php


namespace App\User\Service;


use App\Mail\MailData;
use App\Mail\Service\Mailer;
use App\User\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserNotificator
{
	/**
	 * @var string
	 */
	private $site_name;

	/**
	 * @var Mailer
	 */
	private $mailer;

	/**
	 * UserNotificator constructor.
	 * @param string $site_name
	 * @param Mailer $mailer
	 */
	public function __construct(string $site_name, Mailer $mailer)
	{
		$this->site_name = $site_name;
		$this->mailer = $mailer;
	}

	/**
	 * @param User $user
	 * @param string $activationLink
	 * @param string $password
	 * @throws TransportExceptionInterface
	 */
	public function signUpEmailNotification(User $user, string $activationLink, string $password): void
	{
		$mail = new MailData('email_signup', sprintf('Благодарим за регистрацию на сайте %s!', $this->site_name), $user->getEmail(), [
			'nickname' => $user->getUserData()->getNickname(),
			'site_name' => $this->site_name,
			'activation_link' => $activationLink,
			'user_email' => $user->getEmail(),
			'user_password' => $password
		]);
		$this->mailer->send($mail);
	}

	/**
	 * @param $user
	 * @param $activationLink
	 * @throws TransportExceptionInterface
	 */
	public function activationLinkEmail(User $user, string $activationLink): void
	{
		$mail = new MailData('email_resend_activation_link', sprintf('Активируйте аккаунт на сайте %s!', $this->site_name), $user->getEmail(), [
			'nickname' => $user->getUserData()->getNickname(),
			'site_name' => $this->site_name,
			'activation_link' => $activationLink
		]);
		$this->mailer->send($mail);
	}

	/**
	 * @param User $user
	 * @param string $resetPasswordLink
	 * @throws TransportExceptionInterface
	 */
	public function resetPasswordLink(User $user, string $resetPasswordLink): void
	{
		$mail = new MailData('email_password_reset', sprintf('Ваш пароль на сайте %s был изменен', $this->site_name), $user->getEmail(), [
			'reset_password_link' => $resetPasswordLink,
			'site_name' => $this->site_name,
			'nickname' => $user->getUserData()->getNickname(),
			'user_email' => $user->getEmail()
		]);
		$this->mailer->send($mail);
	}

	/**
	 * @param User $user
	 * @param string $password
	 * @throws TransportExceptionInterface
	 */
	public function resetPasswordSuccess(User $user, string $password): void
	{
		$mail = new MailData('email_new_password', sprintf('Ваш пароль на сайте %s изменен', $this->site_name), $user->getEmail(), [
			'nickname' => $user->getUserData()->getNickname(),
			'user_email' => $user->getEmail(),
			'user_password' => $password,
			'site_name' => $this->site_name
		]);
		$this->mailer->send($mail);
	}

}