<?php

namespace App\Auth\Service;

use DateTimeZone;

class LoginFailedDateTime extends \DateTime
{
	public function __construct($time = 'now', DateTimeZone $timezone = null)
	{
		parent::__construct($time, $timezone);

		$this->modify(sprintf('-%d second', $_ENV['LOGIN_FAIL_BLOCKING_TIME']));
	}
}