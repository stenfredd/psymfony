<?php

namespace App\Tests\Auth\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthServiceTest extends KernelTestCase
{
	protected static $container;

	private $em;

	private $authService;
	private $userService;

	private $users_data = [
		['testmail@gmail.com', 'testpassword', ['ROLE_USER']],
		['testmail1@gmail.com', 'testpassword1', ['ROLE_USER']]
	];

	protected function setUp(): void
	{
		self::bootKernel();

		$container = self::$container;

		$this->authService = $container->get('App\Auth\Service\AuthService');
		$this->userService = $container->get('App\User\Service\UserService');
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
			$this->userService->createUser($user[0], $user[1], $user[2]);
		}
	}

	public function usersDataProvider()
	{
		return $this->users_data;
	}

	/**
	 * @dataProvider usersDataProvider
	 */
	public function testLoginSuccess($email, $pass)
	{
		$token = $this->authService->login($email, $pass);
		$this->assertIsString($token);
	}

	public function notExistsUsersDataProvider()
	{
		return [
			['nottestmail@gmail.com', 'nottestpassword']
		];
	}

	/**
	 * @dataProvider notExistsUsersDataProvider
	 */
	public function testLoginNotExistsUser($email, $pass)
	{
		try {
			$this->authService->login($email, $pass);

			$this->fail();
		} catch (HttpException $exception) {
			$this->assertEquals('Invalid username or password', $exception->getMessage());
		}

	}


	public function invalidUsersDataProvider()
	{
		return [
			['incorrecttestmail@gmail.com', 'testpassword'],
			['testmail@gmail.com', 'incorrecttestpassword']
		];
	}

	/**
	 * @dataProvider invalidUsersDataProvider
	 */
	public function testLoginInvalidCredentials($email, $pass)
	{
		try {
			$this->authService->login($email, $pass);

			$this->fail();
		} catch (HttpException $exception) {
			$this->assertEquals('Invalid username or password', $exception->getMessage());
		}
	}


	public function testVerifyPasswordSuccess()
	{
		$email = 'testemail@gmail.com';
		$password = '111111';

		$user = $this->userService->getNewUser($email, $password);

		$this->authService->verifyPassword($user, $password);

		$this->assertTrue( true );
	}

	public function testVerifyPasswordFailed()
	{
		$this->expectException(AuthenticationException::class);

		$email = 'testemail@gmail.com';
		$password = '111111';

		$user = $this->userService->getNewUser($email, $password);

		$password = 'wrong';

		$this->authService->verifyPassword($user, $password);
	}

	public function testManyLoginFailAttempts()
	{
		$email = 'manyattemptsfails@gmail.com';
		$pass = '111111';

		$user = $this->userService->createUser($email, $pass, []);

		for ($i=1;$i<=$_ENV['MAX_LOGIN_FAIL_COUNT'];$i++) {
			try {
				$this->authService->login($email, 'wrong');
			} catch (HttpException $e) {
				$this->assertEquals('Invalid username or password', $e->getMessage());
			}
		}

		try {
			$this->authService->login($email, 'wrong');
		} catch (HttpException $e) {
			$this->assertEquals('Too many attempts, try again later', $e->getMessage());
		}

	}

}
