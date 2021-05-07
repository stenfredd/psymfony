<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\EmailActivationToken;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method EmailActivationToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailActivationToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailActivationToken[]    findAll()
 * @method EmailActivationToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailActivationTokenRepository extends ServiceEntityRepository implements EmailActivationTokenRepositoryInterface
{
	/**
	 * EmailActivationTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailActivationToken::class);
    }

	/**
	 * @param EmailActivationToken $token
	 * @return EmailActivationToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(EmailActivationToken $token): EmailActivationToken
	{
		$this->_em->persist($token);
		$this->_em->flush();

		return $token;
	}

	/**
	 * @param string $token
	 * @return EmailActivationToken
	 */
	public function getTokenByValue(string $token): EmailActivationToken
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
