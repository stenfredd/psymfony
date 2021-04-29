<?php

namespace App\Auth\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * TokenAuthenticator constructor.
	 * @param EntityManagerInterface $em
	 */
	public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

	/**
	 * Called on every request to decide if this authenticator should be
	 * used for the request. Returning `false` will cause this authenticator
	 * to be skipped.
	 * @param Request $request
	 * @return string
	 */
    public function supports(Request $request)
    {
		return $request->headers->has('X-AUTH-TOKEN');
    }

	/**
	 * Called on every request. Return whatever credentials you want to
	 * be passed to getUser() as $credentials.
	 * @param Request $request
	 * @return string
	 */
    public function getCredentials(Request $request)
    {
		return $request->headers->get('X-AUTH-TOKEN');
    }

	/**
	 * @param mixed $credentials
	 * @param UserProviderInterface $userProvider
	 * @return UserInterface|null
	 */
	public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

		return $userProvider->loadUserByUsername($credentials);
    }

	/**
	 * @param mixed $credentials
	 * @param UserInterface $user
	 * @return bool
	 */
	public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

	/**
	 * @param Request $request
	 * @param TokenInterface $token
	 * @param string $providerKey
	 * @return Response|null
	 */
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

	/**
	 * @param Request $request
	 * @param AuthenticationException $exception
	 * @return void
	 */
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
		throw new AuthenticationException('Authentication failed', Response::HTTP_UNAUTHORIZED);
    }

	/**
	 * Called when authentication is needed, but it's not sent
	 * @param Request $request
	 * @param AuthenticationException|null $authException
	 * @return void
	 */
    public function start(Request $request, AuthenticationException $authException = null)
    {
		if (!$this->supports($request)) {
			throw new AuthenticationException('Auth token not sent', Response::HTTP_UNAUTHORIZED);
		}
		throw new AuthenticationException('Auth token not exists or expired', Response::HTTP_UNAUTHORIZED);
    }

	/**
	 * @return bool
	 */
	public function supportsRememberMe()
    {
        return false;
    }
}