<?php

namespace App\User\Authorization\System\Repository;

use App\User\Authorization\System\Entity\AuthToken;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

interface AuthTokenRepositoryInterface
{
	/**
	 * AuthTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry);

	/**
	 * @param AuthToken $authToken
	 * @return AuthToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(AuthToken $authToken): AuthToken;

	/**
	 * @param string $token
	 * @return AuthToken
	 */
	public function getByTokenValue(string $token): AuthToken;

	/**
	 * @param AuthToken $authToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(AuthToken $authToken): void;
}