<?php

namespace App\Auth\Service;

use DateTimeZone;

class TokenExpiredDateTime extends \DateTime
{
	public function __construct($time = 'now', DateTimeZone $timezone = null)
	{
		parent::__construct($time, $timezone);

		$this->modify(sprintf('+%d second', $_ENV['AUTH_TOKEN_LIFETIME']));
	}
}