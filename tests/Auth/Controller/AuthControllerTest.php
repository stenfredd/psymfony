<?php

namespace App\Tests\Auth\Controller;

use App\Auth\Controller\AuthController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthControllerTest extends WebTestCase
{
	protected static $container;

	private $em;

	private $authService;
	private $userService;
	private $roleService;
	private $permissionService;

	private $client;

	private $email = 'premissiontestuser@gmail.com';
	private $pass = '111111';
	private $authToken = '111111';

	protected function setUp(): void
	{
		self::ensureKernelShutdown();
		$this->client = static::createClient();

		$container = self::$container;

		$this->authService = $container->get('App\Auth\Service\AuthService');
		$this->userService = $container->get('App\User\Service\UserService');
		$this->roleService = $container->get('App\User\Service\RoleService');
		$this->permissionService = $container->get('App\User\Service\PermissionService');
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

		$authToken = $this->authService->login($this->email, $this->pass);

		$this->client->request('POST', '/auth/permission-test', [], [], [
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

		$authToken = $this->authService->login($this->email, $this->pass);

		$this->client->request('POST', '/auth/permission-test', [], [], [
			'HTTP_X-AUTH-TOKEN' => $authToken,
		]);

		$this->assertResponseIsSuccessful();
	}

}
