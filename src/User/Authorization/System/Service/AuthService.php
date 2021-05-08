<?php

declare(strict_types=1);

namespace App\User\Authorization\System\Service;


use App\User\Authorization\System\Entity\LoginFailed;
use App\User\Authorization\System\Exception\LoginFailedCountException;
use App\User\Authorization\System\Repository\LoginFailedRepositoryInterface;
use App\User\Authorization\System\Security\PasswordEncoder;
use App\User\Entity\User;

use App\User\Repository\UserRepositoryInterface;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use LogicException;

class AuthService
{

	/**
	 * @var PasswordEncoder
	 */
	private $passwordEncoder;

	/**
	 * @var TokenService
	 */
	private $tokenService;

	/**
	 * @var LoginFailedRepositoryInterface
	 */
	private $loginFailedRepository;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var string
	 */
	private $maxLoginFailsCount;

	/**
	 * @var int
	 */
	private $loginFailsPeriod;

	/**
	 * @var int
	 */
	private $loginFailsBlockingTime;


	/**
	 * AuthService constructor.
	 * @param int $maxLoginFailsCount
	 * @param int $loginFailsPeriod
	 * @param int $loginFailsBlockingTime
	 * @param PasswordEncoder $passwordEncoder
	 * @param TokenService $tokenService
	 * @param LoginFailedRepositoryInterface $loginFailedRepository
	 * @param RequestStack $requestStack
	 */
	public function __construct(
		int $maxLoginFailsCount,
		int $loginFailsPeriod,
		int $loginFailsBlockingTime,
		PasswordEncoder $passwordEncoder,
		TokenService $tokenService,
		LoginFailedRepositoryInterface $loginFailedRepository,
		RequestStack $requestStack
	)
	{
		$this->maxLoginFailsCount = $maxLoginFailsCount;
		$this->loginFailsPeriod = $loginFailsPeriod;
		$this->loginFailsBlockingTime = $loginFailsBlockingTime;
		$this->passwordEncoder = $passwordEncoder;
		$this->tokenService = $tokenService;
		$this->loginFailedRepository = $loginFailedRepository;
		$this->request = $requestStack->getCurrentRequest();
	}


	/**
	 * @param string $token
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function deleteAuthToken(string $token): void
	{
		$auth_token = $this->tokenService->getByTokenValue($token);
		$this->tokenService->deleteToken($auth_token);
	}

	/**
	 * @param User $user
	 * @param string $password
	 */
	public function verifyPassword(User $user, string $password): void
	{
		if(!$this->passwordEncoder->isPasswordValid($user, $password)){
			$this->saveFailedLogin($user);
			throw new AuthenticationException('Password incorrect');
		}
	}

	/**
	 * @param User $user
	 * @return LoginFailed
	 */
	public function saveFailedLogin(User $user): LoginFailed
	{
		$fail = new LoginFailed();
		$fail->setTarget($user);
		if ($this->request) {
			$fail->setIp($this->request->getClientIp());
			$fail->setClient($this->request->headers->get('User-Agent'));
		}
		return $this->loginFailedRepository->save($fail);
	}

	/**
	 * @param User $user
	 * @throws LoginFailedCountException
	 * @throws Exception
	 */
	public function checkLoginFails(User $user): void
	{
		$this->clearOldLoginFails();

		$period = $this->loginFailsPeriod + $this->loginFailsBlockingTime;
		$lastTime = new \DateTime(sprintf('-%d second', $period));
		$fails = $this->loginFailedRepository->userFailsCount($user, $lastTime);

		if ((count($fails) >= $this->maxLoginFailsCount) && (count($fails) > 0)) {
			throw new LoginFailedCountException();
		}
	}

	private function clearOldLoginFails(): void
	{
		$lastTime = new \DateTime('now');
		$lastTime->modify(sprintf('-%d second', ($this->loginFailsPeriod + $this->loginFailsBlockingTime)));

		$this->loginFailedRepository->clearOldFails($lastTime);
	}

}