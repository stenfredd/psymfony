<?php

namespace App\Auth\Repository;

use App\Auth\Entity\LoginFailed;
use App\Auth\Service\LoginFailedDateTime;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LoginFailed|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginFailed|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginFailed[]    findAll()
 * @method LoginFailed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginFailedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginFailed::class);
    }

	public function save(LoginFailed $loginFailed): LoginFailed
	{
		$this->_em->persist($loginFailed);
		$this->_em->flush();

		return $loginFailed;
	}

    // /**
    //  * @return LoginFailed[] Returns an array of LoginFailed objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LoginFailed
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

	public function userFailsCount(User $user, int $period): array
	{
		$last_time = new \DateTime('now');
		$last_time->modify(sprintf('-%d second', $period));

		return $this->createQueryBuilder('l')
			->andWhere('l.failedAt > :last_time')
			->setParameter('last_time', $last_time->format('Y-m-d H:i:s'))
			->orderBy('l.failedAt', 'DESC')
			->getQuery()
			->getResult();
	}

	public function clearOldFails(\DateTime $lastTime)
	{
		$this->createQueryBuilder('l')
			->delete()
			->where('l.failedAt < :last_time')
			->setParameter('last_time', $lastTime->format('Y-m-d H:i:s'))
			->getQuery()
			->execute();
	}

}
