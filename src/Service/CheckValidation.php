<?php

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckValidation
{
	/**
	 * @var ValidatorInterface
	 */
	private $validator;

	/**
	 * CheckValidation constructor.
	 * @param ValidatorInterface $validator
	 */
	public function __construct(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * @param object $valueObject
	 * @throws ValidationException
	 */
	public function validate(object $valueObject): void
	{
		$errors = $this->validator->validate($valueObject);
		if (count($errors) > 0) {
			throw new ValidationException($errors);
		}
	}
}