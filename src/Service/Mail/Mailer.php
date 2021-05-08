<?php

declare(strict_types=1);

namespace App\Service\Mail;


use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{

	/**
	 * @var MailerInterface
	 */
	private $mailer;

	/**
	 * @var string
	 */
	private $emailSendFrom;

	/**
	 * Mailer constructor.
	 * @param string $emailSendFrom
	 * @param MailerInterface $mailer
	 */
	public function __construct(string $emailSendFrom, MailerInterface $mailer)
	{
		$this->emailSendFrom = $emailSendFrom;
		$this->mailer = $mailer;
	}

	/**
	 * @param MailData $mail
	 * @throws TransportExceptionInterface
	 */
	public function send(MailData $mail): void
	{
		$template = sprintf('emails/%s.html.twig', $mail->getTemplate());

		$email = (new TemplatedEmail())
			->from($this->emailSendFrom)
			->to(new Address($mail->getRecipient()))
			->context($mail->getData())
			->subject($mail->getSubject())
			->htmlTemplate($template);

		$this->mailer->send($email);
	}
}