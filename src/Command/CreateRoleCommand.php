<?php

namespace App\Command;

use App\User\Service\RoleService;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CreateRoleCommand extends Command
{

	protected static $defaultName = 'app:create-role';

	/**
	 * @var RoleService
	 */
	private $roleService;

	public function __construct(string $name = null, RoleService $roleService)
	{
		parent::__construct($name);

		$this->roleService = $roleService;
	}

	protected function configure()
	{
		$this->addArgument('name', InputArgument::REQUIRED, 'The name of the role.');
		$this->addArgument('description', InputArgument::REQUIRED, 'The description of the role.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');
		$description = $input->getArgument('description');

		try {
			$role = $this->roleService->createRole($name, $description);

			$output->writeln([
				'Role was created successfully!',
				'ID: ' . $role->getId(),
				'Name: ' . $role->getName(),
				'Description: ' . $role->getDescription(),
				''
			]);

			return Command::SUCCESS;

		} catch (\Throwable $e) {
			$output->writeln([
				'Create role error:',
				$e->getMessage(),
				''
			]);

			return Command::FAILURE;
		}

	}
}