<?php

namespace App\Auth\Service;

use App\Auth\Entity\LoginFailed;
use App\Auth\Exception\LoginFailedCountException;
use App\Auth\Repository\LoginFailedRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepositoryInterface;
use App\Auth\Security\PasswordEncoder;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
	/** @var UserRepositoryInterface */
	private $userRepository;

	/** @var PasswordEncoder */
	private $passwordEncoder;

	/** @var TokenService */
	private $tokenService;

	/**
	 * @var LoginFailedRepository
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
	 * @param UserRepositoryInterface $userRepository
	 * @param PasswordEncoder $passwordEncoder
	 * @param TokenService $tokenService
	 * @param LoginFailedRepository $loginFailedRepository
	 * @param RequestStack $requestStack
	 */
	public function __construct(
		int $maxLoginFailsCount,
		int $loginFailsPeriod,
		int $loginFailsBlockingTime,
		UserRepositoryInterface $userRepository,
		PasswordEncoder $passwordEncoder,
		TokenService $tokenService,
		LoginFailedRepository $loginFailedRepository,
		RequestStack $requestStack
	)
	{
		$this->maxLoginFailsCount = $maxLoginFailsCount;
		$this->loginFailsPeriod = $loginFailsPeriod;
		$this->loginFailsBlockingTime = $loginFailsBlockingTime;
		$this->passwordEncoder = $passwordEncoder;
		$this->userRepository = $userRepository;
		$this->tokenService = $tokenService;
		$this->loginFailedRepository = $loginFailedRepository;
		$this->request = $requestStack->getCurrentRequest();
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @return string
	 * @throws LogicException
	 */
	public function login(string $email, string $password): string
	{
		try {
			$email = strtolower($email);
			$user = $this->userRepository->getByEmail($email);
			$this->checkLoginFails($user);
			$this->verifyPassword($user, $password);
			$token = $this->tokenService->createToken($user);

			return $token->getValue();
		}
		catch (AuthenticationException $e){
			throw new HttpException(403, "Invalid username or password");
		}
		catch (NotFoundHttpException $e) {
			throw new HttpException(403, "Invalid username or password");
		}
		catch (LoginFailedCountException $e) {
			throw new HttpException(403, "Too many attempts, try again later");
		}
		catch (\Exception $e) {
			throw new \LogicException('Authentication failed');
		}
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

	public function saveFailedLogin(User $user)
	{
		$fail = new LoginFailed();
		$fail->setIp($this->request->getClientIp());
		$fail->setTarget($user);
		$fail->setClient($this->request->headers->get('User-Agent'));

		$this->loginFailedRepository->save($fail);
	}

	private function checkLoginFails(User $user)
	{
		$this->clearOldLoginFiles();

		$period = $this->loginFailsPeriod + $this->loginFailsBlockingTime;
		$fails = $this->loginFailedRepository->userFailsCount($user, $period);

		if ((count($fails) >= $this->maxLoginFailsCount) && (count($fails) > 0)) {
			throw new LoginFailedCountException();
		}
	}

	private function clearOldLoginFiles()
	{
		$lastTime = new \DateTime('now');
		$lastTime->modify(sprintf('-%d second', ($this->loginFailsPeriod + $this->loginFailsBlockingTime)));

		$this->loginFailedRepository->clearOldFails($lastTime);
	}

}