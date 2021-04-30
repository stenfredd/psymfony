<?php

namespace App\Tests\User\Service;

use App\User\Entity\Role;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoleServiceTest extends KernelTestCase
{
	protected static $container;

	private $em;

	private $roleService;

	protected function setUp(): void
	{
		self::bootKernel();

		$container = self::$container;

		$this->roleService = $container->get('App\User\Service\RoleService');
		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->em->getConnection()->beginTransaction();
	}

	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}


	public function testCreateRole()
	{
		$role = $this->roleService->createRole('ROLE_TEST', 'Test role');

		$this->assertInstanceOf(Role::class, $role);
	}

	public function testCreateRoleDouble()
	{
		$this->expectException(\LogicException::class);

		$role = $this->roleService->createRole('ROLE_DOUBLETEST', 'Test role');
		$role = $this->roleService->createRole('ROLE_DOUBLETEST', 'Test role');
	}


	public function testGetRolesByNames()
	{
		$this->roleService->createRole('ROLE_TESTGET', 'Test role');
		$this->roleService->createRole('ROLE_TESTGET2', 'Test role');

		$roles = $this->roleService->getRolesByNames(['ROLE_TESTGET', 'ROLE_TESTGET2']);

		foreach ($roles as $c_role) {
			$this->assertInstanceOf(Role::class, $c_role);
		}
	}

}
