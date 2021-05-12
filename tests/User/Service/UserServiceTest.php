<?php

namespace App\Tests\User\Service;


use App\User\Authorization\Email\ValueObject\ActivateEmail;
use App\User\Authorization\Email\ValueObject\ResetPasswordConfirm;
use App\User\Authorization\Email\ValueObject\SignUp;
use App\User\Entity\User;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserServiceTest extends KernelTestCase
{
	protected static $container;

	private $em;

	private $userService;
	private $tokenService;
	private $roleService;
	private $permissionService;

	private $users_data = [
		['testmail@gmail.com', 'testpassword', ['ROLE_USER']],
		['testmail1@gmail.com', 'testpassword1', ['ROLE_USER']]
	];

	private $added_users = [];

	private $emailActivationTokenService;
	private $emailPasswordResetTokenService;
	private $emailAuthService;


	protected function setUp(): void
	{
		self::bootKernel();

		$container = self::$container;

		$this->userService = $container->get('App\User\Service\UserService');
		$this->emailAuthService = $container->get('App\User\Authorization\Email\Service\AuthService');
		$this->tokenService = $container->get('App\User\Authorization\System\Service\TokenService');
		$this->roleService = $container->get('App\User\Service\RoleService');
		$this->permissionService = $container->get('App\User\Service\PermissionService');
		$this->emailActivationTokenService = $container->get('App\User\Authorization\Email\Service\ActivationTokenService');
		$this->emailPasswordResetTokenService = $container->get('App\User\Authorization\Email\Service\PasswordResetTokenService');

		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->em->getConnection()->beginTransaction();

		$this->addUsers();
	}

	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}

	private function addUsers()
	{
		foreach ($this->users_data as $user) {
			$this->added_users[] = $this->userService->createUser($user[0], $user[1], $user[2]);
		}
	}


	public function usersDataProvider()
	{
		return $this->users_data;
	}

	/**
	 * @dataProvider usersDataProvider
	 */
	public function testGetNewUser($email, $password)
	{
		$user = $this->userService->getNewUser($email, $password);

		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals($email, $user->getEmail());
		$this->assertNotNull($user->getPassword());
	}

	public function testGetById()
	{
		$id = $this->added_users[0]->getId();

		$user = $this->userService->getById($id);

		$this->assertInstanceOf(User::class, $user);
	}

	public function testGetByToken()
	{
		$id = $this->added_users[0]->getId();
		$user = $this->userService->getById($id);

		$token = $this->tokenService->createToken($user);

		$g_user = $this->userService->getByToken($token->getValue());

		$this->assertInstanceOf(User::class, $g_user);
	}

	public function testGetByTokenFailed()
	{
		$this->expectException(NotFoundHttpException::class);

		$this->userService->getByToken('wrong_token');
	}

	public function testGetEncodedPassword()
	{
		$user = $this->added_users[0];

		$password = '111111';

		$encoded_password = $this->userService->getEncodedPassword($user, $password);

		$this->assertIsString($encoded_password);
	}

	public function testSetRolesByNames()
	{
		$user = $this->userService->createUser('rolestestmail@gmail.com', '111111', []);
		$roles_for_set = ['ROLE_USER', 'ROLE_ADMIN'];

		$this->userService->setRolesByNames($user, $roles_for_set);

		$roles = $user->getRoles();

		$this->assertTrue(empty(array_diff($roles_for_set, $roles)) && empty(array_diff($roles, $roles_for_set)));

	}

	public function testHasPermission()
	{
		$role = $this->roleService->createRole('ROLE_TEST', 'Test role');
		$permission = $this->permissionService->createPermission('HSPTEST', 'Test permission');

		$wrong_role = $this->roleService->createRole('ROLE_WRONGTEST', 'Test wrong role');
		$wrong_permission = $this->permissionService->createPermission('WRONGHSPTEST', 'Test wrong permission');

		$this->roleService->setPermissionsByNames($role, ['HSPTEST']);
		$user = $this->userService->createUser('premissiontestmail@gmail.com', '111111', ['ROLE_TEST']);

		$this->assertTrue($this->userService->hasPermission($user, 'HSPTEST'));
		$this->assertFalse($this->userService->hasPermission($user, 'WRONGHSPTEST'));
	}

	public function testCreateUser()
	{
		$user = $this->userService->createUser('createusertestmail@gmail.com', '111111', ['ROLE_USER']);

		$this->assertInstanceOf(User::class, $user);
	}

	public function testCreateUserNonUnique()
	{
		$this->expectException(\LogicException::class);

		$user = $this->userService->createUser('createuserdubletestmail@gmail.com', '111111', ['ROLE_USER']);
		$user = $this->userService->createUser('createuserdubletestmail@gmail.com', '111111', ['ROLE_USER']);
	}

	public function testSignUpEmail()
	{
		$signUpVO = new SignUp();
		$signUpVO->setEmail('ssignupemail@gmail.com');
		$signUpVO->setPassword('111111');
		$signUpVO->setNickname('testsignupnickname');

		$user = $this->emailAuthService->signUpEmail($signUpVO);
		$this->assertInstanceOf(User::class, $user);
	}

	public function testActivateUserWithToken()
	{
		$user = $this->userService->createUser('testactivationwtokenuser@gmail.com', '111111', ['ROLE_USER']);

		$activationToken = $this->emailActivationTokenService->createEmailActivationToken($user);

		$activateEmailVO = new ActivateEmail();
		$activateEmailVO->setToken($activationToken->getToken());

		$this->emailAuthService->activateUserWithToken($activateEmailVO);

		$this->assertTrue($user->isActive());
	}

	public function testResetPasswordByToken()
	{
		$user = $this->userService->createUser('testresetpassword@gmail.com', '111111', ['ROLE_USER']);
		$this->userService->setUserData($user, ['nickname' => 'testresetpasswordnick']);
		$oldPassword = $user->getPassword();

		$resetToken = $this->emailPasswordResetTokenService->createEmailPasswordResetToken($user);

		$resetPasswordVO = new ResetPasswordConfirm();
		$resetPasswordVO->setToken($resetToken->getToken());

		$this->emailAuthService->resetPasswordByToken($resetPasswordVO);

		$newPassword = $user->getPassword();

		$this->assertNotEquals($oldPassword, $newPassword);
	}

}
