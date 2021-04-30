<?php

namespace App\User\Repository;

use App\User\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
	/**
	 * UserRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	/**
	 * @param User $user
	 * @return User
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(User $user): User
	{
		try {
			$this->_em->persist($user);
			$this->_em->flush();

			return $user;
		} catch (UniqueConstraintViolationException $exception) {
			throw new \LogicException(sprintf('User with email "%s" already exists', $user->getEmail()));
		}

	}


	/**
	 * @param string $email
	 * @return User
	 * @throws NotFoundHttpException
	 */
	public function getByEmail(string $email): User
	{
		if(!$user = $this->findOneBy(['email' => $email])){
			throw new NotFoundHttpException('User not found');
		}
		return $user;
	}

	/**
	 * @param int $id
	 * @return User
	 * @throws NotFoundHttpException
	 */
	public function getById(int $id): User
	{
		if(!$user = $this->findOneBy(['id' => $id])){
			throw new NotFoundHttpException('User not found');
		}
		return $user;
	}
}
