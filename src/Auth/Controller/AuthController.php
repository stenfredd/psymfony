<?php

namespace App\Auth\Controller;

use App\Auth\Service\AuthService;

use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/auth")
 */
class AuthController extends AbstractController
{
	/**
	 * @var AuthService
	 */
	private $authService;
	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * AuthController constructor.
	 * @param AuthService $authService
	 * @param UserService $userService
	 */
	public function __construct(AuthService $authService, UserService $userService)
	{
		$this->authService = $authService;
		$this->userService = $userService;
	}

	/**
	 * @Route("/login", name="auth_login", methods={"POST"})
	 * @param Request $request
	 * @return Response
	 */
	public function login(Request $request): Response
	{
		$email = $request->request->get('email');
		$password = $request->request->get('password');

		$token = $this->authService->login($email, $password);

		return $this->json([
			'status' => 'success',
			'data' => [
				'token' => $token
			]
		]);
	}

	/**
	 * @Route("/logout", name="logout")
	 * @param Request $request
	 * @return Response
	 */
	public function logout(Request $request): Response
	{
		$token = $request->headers->get('X-AUTH-TOKEN');

		$this->authService->deleteAuthToken($token);

		return $this->json([
			'status' => 'success',
			'data' => [
				'result' => 'Logout success'
			]
		]);
	}

	/**
	 * @Route("/permission-test", name="logintest")
	 * @IsGranted("PERMISSION_TEST")
	 */
	public function permissionTest(): Response
	{
		return $this->json([
			'status' => 'SUCCESS',
			'data' => 'norm vse'
		]);
	}

}
