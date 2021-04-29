<?php

namespace App\User\Repository;

use App\User\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

interface UserRepositoryInterface
{
	/**
	 * UserRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry);

	/**
	 * @param User $user
	 * @return User
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(User $user): User;

	/**
	 * @param string $email
	 * @return User|null
	 */
	public function getByEmail(string $email): User;

	/**
	 * @param int $id
	 * @return mixed
	 */
	public function getById(int $id): User;

}