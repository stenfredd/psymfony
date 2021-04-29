<?php

namespace App\Command;

use App\User\Service\RoleService;
use App\User\Service\PermissionService;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class CreatePermissionCommand extends Command
{

	protected static $defaultName = 'app:create-permission';

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
		$this->addArgument('name', InputArgument::REQUIRED, 'The name of the permission.');
		$this->addArgument('description', InputArgument::REQUIRED, 'The description of the permission.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');
		$description = $input->getArgument('description');

		try {
			$permission = $this->permissionService->createPermission($name, $description);

			$output->writeln([
				'Permission was created successfully!',
				'ID: ' . $permission->getId(),
				'Name: ' . $permission->getName(),
				'Description: ' . $permission->getDescription(),
				''
			]);

			return Command::SUCCESS;

		} catch (\Throwable $e) {
			$output->writeln([
				'Create permission error:',
				$e->getMessage(),
				''
			]);

			return Command::FAILURE;
		}

	}

}