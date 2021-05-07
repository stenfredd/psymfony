<?php

declare(strict_types=1);

namespace App\Mail;

class MailData
{
	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var string
	 */
	private $subject;

	/**
	 * @var string
	 */
	private $recipient;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * MailData constructor.
	 * @param string $template
	 * @param string $subject
	 * @param string $recipient
	 * @param array $data
	 */
	public function __construct(string $template, string $subject, string $recipient, array $data)
	{
		$this->template = $template;
		$this->subject = $subject;
		$this->recipient = $recipient;
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getRecipient(): string
	{
		return $this->recipient;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}

	/**
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject;
	}

}