<?php

namespace App\Auth\Security;

use App\User\Entity\User;
use App\User\Service\UserService;
use App\Auth\Service\TokenService;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var TokenService
	 */
	private $tokenService;

	/**
	 * UserProvider constructor.
	 * @param UserService $userService
	 * @param TokenService $tokenService
	 */
	public function __construct(UserService $userService, TokenService $tokenService)
	{
		$this->userService = $userService;
		$this->tokenService = $tokenService;
	}

	/**
	 * Symfony calls this method if you use features like switch_user
	 * or remember_me.
	 *
	 * If you're not using these features, you do not need to implement
	 * this method.
	 *
	 * @param string $authToken
	 * @return UserInterface
	 *
	 */
	public function loadUserByUsername(string $authToken)
	{
		try {
			$token = $this->tokenService->getByTokenValue($authToken);
			if(!$this->tokenService->isTokenActual($token)){
				$this->tokenService->deleteToken($token);
				throw new AuthenticationException('Auth token expired');
			}
			$user = $token->getHolder();

		} catch (NotFoundHttpException $e) {
			throw new AuthenticationException('Auth token not exists or expired');
		} catch (AuthenticationException $e) {
			throw new AuthenticationException('Auth token not exists or expired');
		} catch (\Exception $e) {
			throw new \LogicException('Authentication failed');
		}

		return $user;
	}

	/**
	 * Refreshes the user after being reloaded from the session.
	 *
	 * When a user is logged in, at the beginning of each request, the
	 * User object is loaded from the session and then this method is
	 * called. Your job is to make sure the user's data is still fresh by,
	 * for example, re-querying for fresh User data.
	 *
	 * If your firewall is "stateless: true" (for a pure API), this
	 * method is not called.
	 *
	 * @param UserInterface $user
	 * @return void
	 */
	public function refreshUser(UserInterface $user)
	{
		throw new UnsupportedUserException();
	}

	/**
	 * Tells Symfony to use this provider for this User class.
	 * @param string $class
	 * @return bool
	 */
	public function supportsClass(string $class)
	{
		return User::class === $class || is_subclass_of($class, User::class);
	}

}
