<?php

namespace App\Command;

use App\User\Entity\Role;
use App\User\Service\RoleService;
use App\User\Service\PermissionService;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class PermissionToRoleCommand extends Command
{

	protected static $defaultName = 'app:permission-to-role';

	/**
	 * @var RoleService
	 */
	private $roleService;

	/**
	 * @var PermissionService
	 */
	private $permissionService;

	public function __construct(string $name = null, RoleService $roleService, PermissionService $permissionService)
	{
		parent::__construct($name);

		$this->roleService = $roleService;
		$this->permissionService = $permissionService;
	}

	protected function configure()
	{
		$this->addArgument('role', InputArgument::REQUIRED, 'The name of the role.');
		$this->addArgument('permission', InputArgument::REQUIRED, 'The name of the permission.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$role = $input->getArgument('role');
		$permission = $input->getArgument('permission');

		try {
			$roles = $this->roleService->getRolesByNames([$role]);
			if($roles[0] instanceof Role){
				$this->roleService->setPermissionsByNames($roles[0], [$permission]);
			} else {
				$output->writeln([
					'Set permission error:',
					'role name not found',
					''
				]);

				return Command::FAILURE;
			}

			$output->writeln([
				'Permission set successfully!',
				''
			]);

			return Command::SUCCESS;

		} catch (\Throwable $e) {
			$output->writeln([
				'Set permission error:',
				$e->getMessage(),
				''
			]);

			return Command::FAILURE;
		}

	}

}