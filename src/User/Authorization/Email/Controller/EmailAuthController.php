<?php

namespace App\User\Authorization\Email\Controller;


use App\Service\CheckValidation;
use App\Service\JsonDTO;
use App\User\Authorization\Email\Service\AuthService as EmailAuthService;
use App\User\Authorization\Email\Service\RedirectService;
use App\User\Authorization\Email\ValueObject\ActivateEmail;
use App\User\Authorization\Email\ValueObject\ResetPassword;
use App\User\Authorization\Email\ValueObject\ResetPasswordConfirm;
use App\User\Authorization\Email\ValueObject\SignUp;
use App\User\Authorization\System\Service\AuthService as SystemAuthService;
use App\User\Authorization\Email\ValueObject\Login;
use App\User\Entity\User;
use App\User\Service\UserService;
use Swagger\Annotations as SWG;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Exception\ValidationException;
use Throwable;

/**
 * @Route("/api/auth")
 */
class EmailAuthController extends AbstractController
{
	/**
	 * @var EmailAuthService
	 */
	private $emailAuthService;

	/**
	 * @var SystemAuthService
	 */
	private $systemAuthService;

	/**
	 * @var CheckValidation
	 */
	private $checkValidation;

	/**
	 * @var JsonDTO
	 */
	private $jsonDTO;

	/**
	 * @var RedirectService
	 */
	private $redirectService;

	/**
	 * AuthController constructor.
	 * @param EmailAuthService $emailAuthService
	 * @param SystemAuthService $systemAuthService
	 * @param CheckValidation $checkValidation
	 * @param JsonDTO $jsonDTO
	 * @param RedirectService $redirectService
	 */
	public function __construct(
		EmailAuthService $emailAuthService,
		SystemAuthService $systemAuthService,
		CheckValidation $checkValidation,
		JsonDTO $jsonDTO,
		RedirectService $redirectService
	)
	{
		$this->emailAuthService = $emailAuthService;
		$this->systemAuthService = $systemAuthService;
		$this->checkValidation = $checkValidation;
		$this->jsonDTO = $jsonDTO;
		$this->redirectService = $redirectService;
	}

	/**
	 * @Route("/email/login", name="auth_login", methods={"POST"})
	 * @SWG\Tag(name="Auth")
	 * @SWG\Parameter(
	 *     name="email",
	 *     in="query",
	 *     type="string",
	 *     description="User email"
	 * )
	 * @SWG\Parameter(
	 *     name="password",
	 *     in="query",
	 *     type="string",
	 *     description="User password"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Returns auth token",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="token", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws ValidationException
	 */
	public function login(Request $request): Response
	{
		/* @var Login $loginVO */
		$loginVO = $this->jsonDTO->fromRequest(new Login);
		$this->checkValidation->validate($loginVO);

		$token = $this->emailAuthService->login($loginVO);

		return $this->json([
			'status' => 'success',
			'data' => [
				'token' => $token
			]
		]);
	}

	/**
	 * @Route("/logout", name="logout", methods={"POST"})
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Auth")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Invalidates the auth token",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="result", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function logout(Request $request): Response
	{
		$token = $request->headers->get('X-AUTH-TOKEN');

		$this->systemAuthService->deleteAuthToken($token);

		return $this->json([
			'status' => 'success',
			'data' => [
				'result' => 'Logout success'
			]
		]);
	}

	/**
	 * @Route("/email/sign-up", name="sign_up", methods={"POST"})
	 * @SWG\Tag(name="Auth")
	 * @SWG\Parameter(
	 *     name="email",
	 *     in="query",
	 *     type="string",
	 *     description="User email"
	 * )
	 * @SWG\Parameter(
	 *     name="password",
	 *     in="query",
	 *     type="string",
	 *     description="User password"
	 * )
	 * @SWG\Parameter(
	 *     name="nickname",
	 *     in="query",
	 *     type="string",
	 *     description="User nickname"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Returns user data",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="user_id", type="integer"),
	 *         @SWG\Property(property="password", type="string"),
	 *         @SWG\Property(property="nickname", type="string")
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws Throwable
	 */
	public function signUpEmail(Request $request): Response
	{
		/* @var SignUp $signUpVO */
		$signUpVO = $this->jsonDTO->fromRequest(new SignUp);
		$this->checkValidation->validate($signUpVO);

		$user = $this->emailAuthService->signUpEmail($signUpVO);

		return $this->json([
			'status' => 'success',
			'data' => [
				'user_id' => $user->getId(),
				'email' => $user->getEmail(),
				'nickname' => $user->getUserData()->getNickname()
			]
		]);
	}

	/**
	 * @Route("/email/resend-activation-link", name="resend_activation_link", methods={"POST"})
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Auth")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Send activation link to the user email",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws TransportExceptionInterface
	 */
	public function resendActivationLink(Request $request): Response
	{
		/** @var $user User */
		$user = $this->getUser();

		$this->emailAuthService->resendActivationLinkEmail($user);

		return $this->json([
			'status' => 'success',
			'data' => [
				'message' => 'A link to activation has been sent'
			]
		]);
	}

	/**
	 * @Route("/email/activate-user", name="activate_email", methods={"GET"})
	 * @SWG\Tag(name="Auth")
	 * @SWG\Parameter(
	 *     name="token",
	 *     in="query",
	 *     type="string",
	 *     description="Activation token"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Activate user",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws ValidationException
	 */
	public function activateEmail(Request $request): Response
	{
		$token = $request->get('token');

		$activateEmailVO = new ActivateEmail();
		$activateEmailVO->setToken($token);

		$this->checkValidation->validate($activateEmailVO);

		try {
			$this->emailAuthService->activateUserWithToken($activateEmailVO);
		} catch (\InvalidArgumentException $e) {
			return $this->redirect($this->redirectService->getActivationLinkFailRedirectTo());
		} catch (NotFoundHttpException $e) {
			return $this->redirect($this->redirectService->getActivationLinkFailRedirectTo());
		}

		return $this->redirect($this->redirectService->getActivationLinkSuccessRedirectTo());
	}

	/**
	 * @Route("/email/reset-password", name="reset_password", methods={"POST"})
	 * @SWG\Tag(name="Auth")
	 * @SWG\Parameter(
	 *     name="email",
	 *     in="query",
	 *     type="string",
	 *     description="User email"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Send reset link to the user email",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws TransportExceptionInterface
	 * @throws ValidationException
	 */
	public function resetPassword(Request $request): Response
	{
		/* @var ResetPassword $resetPasswordVO */
		$resetPasswordVO = $this->jsonDTO->fromRequest(new ResetPassword);
		$this->checkValidation->validate($resetPasswordVO);

		$this->emailAuthService->sendNewResetPasswordLink($resetPasswordVO);

		return $this->json([
			'status' => 'success',
			'data' => [
				'message' => 'A link to reset password has been sent'
			]
		]);
	}

	/**
	 * @Route("/email/reset-password-confirm", name="reset_password_confirm", methods={"GET"})
	 * @SWG\Tag(name="Auth")
	 * @SWG\Parameter(
	 *     name="token",
	 *     in="query",
	 *     type="string",
	 *     description="Reset password token"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Set new password and send to user email",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws TransportExceptionInterface
	 * @throws ValidationException
	 */
	public function resetPasswordConfirm(Request $request): Response
	{
		$token = $request->get('token');

		$resetPasswordVO = new ResetPasswordConfirm();
		$resetPasswordVO->setToken($token);

		$this->checkValidation->validate($resetPasswordVO);

		try {
			$this->emailAuthService->resetPasswordByToken($resetPasswordVO);
		} catch (\Exception $e) {
			return $this->redirect($this->redirectService->getResetPasswordFailRedirectTo());
		}

		return $this->redirect($this->redirectService->getResetPasswordSuccessRedirectTo());
	}


	/**
	 * @Route("/email/permission-test", name="ptest")
	 * @SWG\Tag(name="Auth")
	 * @Security("is_granted('PERMISSION_PRMPTEST')", statusCode=403, message="Access denied")
	 */
	public function permissionTest(): Response
	{
		return $this->json([
			'status' => 'SUCCESS',
			'data' => 'ok'
		]);
	}

}
