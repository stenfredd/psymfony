<?php

namespace App\Command;

use App\User\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RoleToUserCommand extends Command
{

	protected static $defaultName = 'app:role-to-user';

	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct(string $name = null, UserService $userService)
	{
		parent::__construct($name);

		$this->userService = $userService;
	}

	protected function configure()
	{
		$this->addArgument('user_id', InputArgument::REQUIRED, 'The id of the user.');
		$this->addArgument('role_name', InputArgument::REQUIRED, 'The name of the role.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user_id = $input->getArgument('user_id');
		$role_name = $input->getArgument('role_name');


		try {
			$user = $this->userService->getById($user_id);

			$this->userService->setRolesByNames($user, [$role_name]);

			$output->writeln([
				sprintf('Set role successfully! Now user #%s has next roles:', $user->getId()),
				implode(', ', $user->getRoles()),
				''
			]);

			return Command::SUCCESS;

		} catch (\Throwable $e) {
			$output->writeln([
				'Create user error:',
				$e->getMessage(),
				''
			]);

			return Command::FAILURE;
		}

	}
}