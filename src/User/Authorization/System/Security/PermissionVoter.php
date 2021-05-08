<?php

namespace App\User\Authorization\System\Security;

use App\User\Entity\User;
use App\User\Service\UserService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
	/**
	 * @var string
	 */
	private $permissionName;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * PermissionVoter constructor.
	 * @param UserService $userService
	 */
	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * @param string $attribute
	 * @param mixed $subject
	 * @return bool
	 */
	protected function supports(string $attribute, $subject): bool
	{
		if (str_starts_with($attribute, "PERMISSION_")) {
			$this->permissionName = preg_replace('/^PERMISSION_/', '', $attribute);
			return true;
		}
		return false;
	}

	/**
	 * @param string $attribute
	 * @param mixed $subject
	 * @param TokenInterface $token
	 * @return bool
	 * @throws NonUniqueResultException
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
	{
		if (!($user = $token->getUser()) instanceof User) {
			return false;
		}

		if ($this->userService->hasPermission($user, $this->permissionName)) {
			return true;
		}
		return false;

	}

}