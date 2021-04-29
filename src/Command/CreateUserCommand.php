<?php

namespace App\Command;

use App\User\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CreateUserCommand extends Command
{

	protected static $defaultName = 'app:create-user';

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
		$this->addArgument('user_type', InputArgument::REQUIRED, 'The type of the user.');
		$this->addArgument('user_email', InputArgument::REQUIRED, 'The username of the user.');
		$this->addArgument('user_password', InputArgument::REQUIRED, 'The password of the user.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$email = $input->getArgument('user_email');
		$type = $input->getArgument('user_type');
		$password = $input->getArgument('user_password');

		try {
			/*
			$roles =  match ($type) {
				'admin' => ['ROLE_ADMIN'],
				'user' => ['ROLE_USER'],
				default => throw new \Exception('Type must be "admin" or "user"')
			};
			*/

			switch ($type) {
				case 'admin':
					$roles = ['ROLE_ADMIN'];
					break;
				case 'user':
					$roles = ['ROLE_USER'];
					break;
				default:
					throw new \Exception('Type must be "admin" or "user"');
					break;
			}

			$user = $this->userService->createUser($email, $password, $roles);

			$output->writeln([
				'User was created successfully!',
				'ID: ' . $user->getId(),
				'Email: ' . $user->getEmail(),
				'Roles: ' . implode(', ', $user->getRoles()),
				'Password: ' . $password,
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