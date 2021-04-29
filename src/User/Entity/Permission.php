<?php

namespace App\User\Entity;

use App\User\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PermissionRepository::class)
 */
class Permission
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, mappedBy="permissions")
     */
    private $roles;

	/**
	 * Permission constructor.
	 */
	public function __construct()
    {
        $this->roles = new ArrayCollection();
    }


	/**
	 * @return int|null
	 */
	public function getId(): ?int
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
	 * @return string|null
	 */
	public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addPermission($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }

        return $this;
    }

}
