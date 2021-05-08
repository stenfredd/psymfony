<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Repository;

use App\User\Authorization\Email\Entity\PasswordResetToken;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method PasswordResetToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordResetToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordResetToken[]    findAll()
 * @method PasswordResetToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
	/**
	 * EmailPasswordResetTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

	/**
	 * @param PasswordResetToken $token
	 * @return PasswordResetToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(PasswordResetToken $token): PasswordResetToken
	{
		$this->_em->persist($token);
		$this->_em->flush();

		return $token;
	}

	/**
	 * @param string $token
	 * @return PasswordResetToken
	 */
	public function getTokenByValue(string $token): PasswordResetToken
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
