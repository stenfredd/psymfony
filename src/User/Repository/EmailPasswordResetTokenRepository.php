<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\EmailPasswordResetToken;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method EmailPasswordResetToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailPasswordResetToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailPasswordResetToken[]    findAll()
 * @method EmailPasswordResetToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailPasswordResetTokenRepository extends ServiceEntityRepository implements EmailPasswordResetTokenRepositoryInterface
{
	/**
	 * EmailPasswordResetTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailPasswordResetToken::class);
    }

	/**
	 * @param EmailPasswordResetToken $token
	 * @return EmailPasswordResetToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(EmailPasswordResetToken $token): EmailPasswordResetToken
	{
		$this->_em->persist($token);
		$this->_em->flush();

		return $token;
	}

	/**
	 * @param string $token
	 * @return EmailPasswordResetToken
	 */
	public function getTokenByValue(string $token): EmailPasswordResetToken
	{
		if(!$token = $this->findOneBy(['token' => $token])){
			throw new NotFoundHttpException('Token not found');
		}
		return $token;
	}

	/**
	 * @param User $user
	 */
	public function deleteUserTokens(User $user): void
	{
		$this->createQueryBuilder('t')
			->delete()
			->where('t.holder = :holder_id')
			->setParameter('holder_id', $user->getId())
			->getQuery()
			->execute();
	}
}
