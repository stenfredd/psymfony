<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\User\Repository\RoleRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @ORM\Table(name="`roles`")
 */
class Role
{
    /**
	 * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
	 * @var string
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private $name;

    /**
	 * @var Collection|User[]
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="roles")
     */
    private $users;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=255)
	 */
	private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Permission::class, inversedBy="roles")
     */
    private $permissions;

	/**
	 * Role constructor.
	 */
	public function __construct()
	{
		$this->users = new ArrayCollection();
		$this->permissions = new ArrayCollection();
	}

	/**
	 * @return string|null
	 */
	public function __toString()
	{
		return $this->getName();
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

	/**
	 * @param User $user
	 * @return $this
	 */
	public function addUser(User $user): self
	{
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
			$user->addRole($this);
		}

		return $this;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function removeUser(User $user): self
	{
		if ($this->users->removeElement($user)) {
			$user->removeRole($this);
		}

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return $this
	 */
	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

    /**
     * @return Collection|Permission[]
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

	/**
	 * @param Permission $permission
	 * @return $this
	 */
	public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

	/**
	 * @param Permission $permission
	 * @return $this
	 */
	public function removePermission(Permission $permission): self
    {
        $this->permissions->removeElement($permission);

        return $this;
    }
}
