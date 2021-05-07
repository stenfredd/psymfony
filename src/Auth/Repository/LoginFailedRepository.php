<?php

declare(strict_types=1);

namespace App\Auth\Repository;

use App\Auth\Entity\LoginFailed;
use App\User\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LoginFailed|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginFailed|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginFailed[]    findAll()
 * @method LoginFailed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginFailedRepository extends ServiceEntityRepository implements LoginFailedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginFailed::class);
    }

	/**
	 * @param LoginFailed $loginFailed
	 * @return LoginFailed
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(LoginFailed $loginFailed): LoginFailed
	{
		$this->_em->persist($loginFailed);
		$this->_em->flush();

		return $loginFailed;
	}

	/**
	 * @param User $user
	 * @param DateTime $lastTime
	 * @return array
	 */
	public function userFailsCount(User $user, DateTime $lastTime): array
	{
		return $this->createQueryBuilder('l')
			->andWhere('l.failedAt > :last_time')
			->setParameter('last_time', $lastTime->format('Y-m-d H:i:s'))
			->orderBy('l.failedAt', 'DESC')
			->getQuery()
			->getResult();
	}

	/**
	 * @param DateTime $lastTime
	 */
	public function clearOldFails(DateTime $lastTime): void
	{
		$this->createQueryBuilder('l')
			->delete()
			->where('l.failedAt < :last_time')
			->setParameter('last_time', $lastTime->format('Y-m-d H:i:s'))
			->getQuery()
			->execute();
	}

}
