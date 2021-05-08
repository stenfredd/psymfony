<?php

namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ErrorNormalizer implements NormalizerInterface
{
	/**
	 * @param mixed $exception
	 * @param string|null $format
	 * @param array $context
	 * @return array|array[]|\ArrayObject|bool|float|int|string|null
	 */
	public function normalize($exception, string $format = null, array $context = [])
	{
		if($exception->getCode() && $exception->getCode() != 0){
			$exception->setStatusCode($exception->getCode());
		}

		return [
			'error' => [
				'message' => $exception->getMessage(),
				'code' => $exception->getStatusCode(),
			]
		];
	}

	/**
	 * @param mixed $data
	 * @param string|null $format
	 * @return bool
	 */
	public function supportsNormalization($data, string $format = null)
	{
		if (getenv('APP_ENV') == 'dev') {
			return false;
		}

		return ($data instanceof FlattenException && $data->getClass() !== 'App\Exception\ValidationException');
	}

}
