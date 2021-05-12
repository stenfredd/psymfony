<?php

namespace App\Tests\Auth\Controller;

use App\Exception\ValidationException;
use App\User\Authorization\Email\ValueObject\Login;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EmailAuthControllerTest extends WebTestCase
{
	protected static $container;

	private $em;

	private $emailAuthService;
	private $userService;
	private $roleService;
	private $permissionService;

	private $client;

	private $email = 'premissiontestuser@gmail.com';
	private $pass = '111111';
	private $authToken = '111111';
	private $activationTokenService;
	private $emailPasswordResetTokenService;


	protected function setUp(): void
	{
		self::ensureKernelShutdown();
		$this->client = static::createClient();

		$container = self::$container;

		$this->emailAuthService = $container->get('App\User\Authorization\Email\Service\AuthService');
		$this->userService = $container->get('App\User\Service\UserService');
		$this->roleService = $container->get('App\User\Service\RoleService');
		$this->permissionService = $container->get('App\User\Service\PermissionService');
		$this->activationTokenService = $container->get('App\User\Authorization\Email\Service\ActivationTokenService');
		$this->emailPasswordResetTokenService = $container->get('App\User\Authorization\Email\Service\PasswordResetTokenService');
		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->em->getConnection()->beginTransaction();
	}


	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}

	public function testPermissionFail()
	{
		$this->expectException(HttpException::class);
		$this->client->catchExceptions(false);

		$user = $this->userService->createUser($this->email, $this->pass, []);

		$loginVO = new Login();
		$loginVO->setEmail($this->email);
		$loginVO->setPassword($this->pass);

		$authToken = $this->emailAuthService->login($loginVO);

		$this->client->request('POST', '/api/auth/email/permission-test', [], [], [
			'HTTP_X-AUTH-TOKEN' => $authToken,
		]);


		$this->assertResponseStatusCodeSame(403);
	}


	public function testHasPermission()
	{
		$this->client->catchExceptions(false);

		$role = $this->roleService->createRole('ROLE_TESTPRM', 'Test role perm');
		$permission = $this->permissionService->createPermission('PRMPTEST', 'Test permission perm');

		$this->roleService->setPermissionsByNames($role, ['PRMPTEST']);
		$user = $this->userService->createUser($this->email, $this->pass, ['ROLE_TESTPRM']);

		$loginVO = new Login();
		$loginVO->setEmail($this->email);
		$loginVO->setPassword($this->pass);

		$authToken = $this->emailAuthService->login($loginVO);

		$this->client->request('POST', '/api/auth/email/permission-test', [], [], [
			'HTTP_X-AUTH-TOKEN' => $authToken,
		]);

		$this->assertResponseIsSuccessful();
	}

	public function testSignUpEmail()
	{
		$this->client->request('POST', '/api/auth/email/sign-up', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'signupemail@gmail.com',
			'password' => '111111',
			'nickname' => 'testsignupnickname'
		]));

		$this->assertResponseIsSuccessful();
	}

	public function testSignUpEmailValidationFailed()
	{
		$this->expectException(ValidationException::class);
		$this->client->catchExceptions(false);

		$this->client->request('POST', '/api/auth/email/sign-up', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'signupemailgmail.com',
			'password' => '111111',
			'nickname' => 'testsignupnickname'
		]));

		$this->assertResponseStatusCodeSame(500);
	}

	public function testResendActivationLink()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->createUser($this->email, $this->pass, []);
		$this->userService->setUserData($user, ['nickname' => 'testnickname']);

		$loginVO = new Login();
		$loginVO->setEmail($this->email);
		$loginVO->setPassword($this->pass);

		$authToken = $this->emailAuthService->login($loginVO);

		$this->client->request('POST', '/api/auth/email/resend-activation-link', [], [], [
			'CONTENT_TYPE' => 'application/json',
			'HTTP_X-AUTH-TOKEN' => $authToken
		]);

		$this->assertResponseIsSuccessful();
	}

	public function testActivateEmail()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->createUser($this->email, $this->pass, []);
		$this->userService->setUserData($user, ['nickname' => 'testnickname']);

		$activationToken = $this->activationTokenService->createEmailActivationToken($user);

		$this->client->request('GET', '/api/auth/email/activate-user', ['token' => $activationToken->getToken()]);

		$this->assertResponseRedirects('/account-activated');
	}

	public function testActivateEmailFailed()
	{
		$this->client->catchExceptions(false);

		$this->client->request('GET', '/api/auth/email/activate-user', ['token' => 'wrongToken']);

		$this->assertResponseRedirects('/account-activation-filed');
	}

	public function testResetPassword()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->createUser($this->email, $this->pass, []);
		$this->userService->setUserData($user, ['nickname' => 'testnickname']);

		$this->client->request('POST', '/api/auth/email/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => $this->email
		]));

		$this->assertResponseIsSuccessful();
	}


	public function testResetPasswordFail()
	{
		$this->expectException(HttpException::class);
		$this->client->catchExceptions(false);

		$user = $this->userService->createUser($this->email, $this->pass, []);
		$this->userService->setUserData($user, ['nickname' => 'testnickname']);

		$this->client->request('POST', '/api/auth/email/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'wrongemail@gmail.com'
		]));

		$this->assertResponseStatusCodeSame(500);
	}

	public function testResetPasswordConfirm()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->createUser($this->email, $this->pass, []);
		$this->userService->setUserData($user, ['nickname' => 'testnickname']);

		$resetToken = $this->emailPasswordResetTokenService->createEmailPasswordResetToken($user);

		$this->client->request('GET', '/api/auth/email/reset-password-confirm', ['token' => $resetToken->getToken()]);

		$this->assertResponseRedirects('/password-changed');
	}

	public function testResetPasswordConfirmFailed()
	{
		$this->client->catchExceptions(false);

		$this->client->request('GET', '/api/auth/email/reset-password-confirm', ['token' => 'wrongtoken']);

		$this->assertResponseRedirects('/password-change-filed');
	}
}
