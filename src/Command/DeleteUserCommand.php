<?php

namespace App\Command;

use App\User\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DeleteUserCommand extends Command
{

	protected static $defaultName = 'app:delete-user';

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
		$this->addArgument('user_id', InputArgument::REQUIRED, 'The ID of the user.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user_id = $input->getArgument('user_id');

		try {

			$this->userService->deleteUser($user_id);

			$output->writeln([
				'User deleted successfully!',
				''
			]);

			return Command::SUCCESS;

		} catch (\Throwable $e) {
			$output->writeln([
				'Delete user error:',
				$e->getMessage(),
				''
			]);

			return Command::FAILURE;
		}

	}
}