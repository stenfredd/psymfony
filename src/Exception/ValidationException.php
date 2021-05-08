<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception
{
	/**
	 * @var ConstraintViolationListInterface
	 */
	private $errors;

	public function __construct(ConstraintViolationListInterface $errors)
	{
		parent::__construct($this->getErrorsJson($errors), 500, null);
	}

	private function getErrorsJson(ConstraintViolationListInterface $errors)
	{
		$errorsArray = [];
		if (count($errors) > 0) {
			foreach ($errors as $cError) {
				$errorsArray[] = [
					'field' => $cError->getPropertyPath(),
					'error' => $cError->getMessage()
				];
			}
		}

		return json_encode($errorsArray);
	}

	/**
	 * @return ConstraintViolationListInterface
	 */
	public function getErrors(): ConstraintViolationListInterface
	{
		return $this->errors;
	}
}