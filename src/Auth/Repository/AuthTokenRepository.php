<?php

declare(strict_types=1);

namespace App\Auth\Repository;

use App\Auth\Entity\AuthToken;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method AuthToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthToken[]    findAll()
 * @method AuthToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthTokenRepository extends ServiceEntityRepository implements AuthTokenRepositoryInterface
{
	/**
	 * AuthTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthToken::class);
    }

	/**
	 * @param AuthToken $auth_token
	 * @return AuthToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(AuthToken $auth_token): AuthToken
	{
		$this->_em->persist($auth_token);
		$this->_em->flush();

		return $auth_token;
	}

	/**
	 * @param string $token
	 * @return AuthToken
	 */
	public function getByTokenValue(string $token): AuthToken
	{
		if(!$token = $this->findOneBy(['value' => $token])){
			throw new NotFoundHttpException('Auth Token not found');
		}
		return $token;
	}

	/**
	 * @param AuthToken $auth_token
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(AuthToken $auth_token): void
	{
		$this->_em->remove($auth_token);
		$this->_em->flush();
	}

}
