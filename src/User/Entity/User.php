<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\User\Authorization\System\Entity\AuthToken;
use App\User\Authorization\System\Entity\LoginFailed;
use App\User\Repository\UserRepository;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`users`", indexes={@ORM\Index(columns={"password"})})
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
	const ACTIVE = 1;
	const INACTIVE = 0;

    /**
	 * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
	 * @var string The hashed password
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
	 * @var string
     * @ORM\Column(type="string")
     */
    private $password;

    /**
	 * @var Collection|Role[]
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="users")
     */
    private $roles;

    /**
	 * @var Collection|AuthToken[]
     * @ORM\OneToMany(targetEntity=AuthToken::class, mappedBy="holder", orphanRemoval=true)
     */
    private $authTokens;

    /**
	 * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
	 * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=LoginFailed::class, mappedBy="target", orphanRemoval=true)
     */
    private $loginFaileds;

    /**
     * @ORM\OneToOne(targetEntity=UserData::class, mappedBy="holder", cascade={"persist", "remove"}, orphanRemoval=true)
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $userData;

	/**
	 * @var int
	 * @ORM\Column(type="integer", options={"default":"0"})
	 */
	private $active = self::INACTIVE;

	/**
	 * @return string
	 */
	public function __toString()
	{
		return self::class;
	}

	/**
	 * User constructor.
	 */
	public function __construct()
	{
		$this->roles = new ArrayCollection();
		$this->authTokens = new ArrayCollection();
		$this->loginFaileds = new ArrayCollection();
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function updatedTimestamps(): void
	{
		$this->updatedAt = new DateTime('now');
		if ($this->createdAt === null) {
			$this->createdAt = new DateTime('now');
		}
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
	public function getEmail(): ?string
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return User $this
	 */
	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername(): string
	{
		return (string)$this->email;
	}

	/**
	 * @see UserInterface
	 */
	public function getPassword(): string
	{
		return (string)$this->password;
	}

	/**
	 * @param string $password
	 * @return User $this
	 */
	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * Returning a salt is only needed, if you are not using a modern
	 * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
	 *
	 * @see UserInterface
	 */
	public function getSalt(): ?string
	{
		return null;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials()
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$result = [];
		if (count($roles = $this->roles) > 0) {
			foreach ($roles as $cRole) {
				$result[] = $cRole->getName();
			}
		}
		return $result;
	}

	/**
	 * @return Collection|Role[]
	 */
	public function getRolesCollection(): Collection
	{
		return $this->roles;
	}

	/**
	 * @param Role $role
	 * @return User $this
	 */
	public function addRole(Role $role): self
	{
		if (!$this->roles->contains($role)) {
			$this->roles[] = $role;
		}

		return $this;
	}

	/**
	 * @param Role $role
	 * @return User $this
	 */
	public function removeRole(Role $role): self
	{
		$this->roles->removeElement($role);

		return $this;
	}

	/**
	 * @return Collection|AuthToken[]
	 */
	public function getAuthTokens(): Collection
	{
		return $this->authTokens;
	}

	/**
	 * @param AuthToken $authToken
	 * @return User $this
	 */
	public function addAuthToken(AuthToken $authToken): self
	{
		if (!$this->authTokens->contains($authToken)) {
			$this->authTokens[] = $authToken;
			$authToken->setHolder($this);
		}

		return $this;
	}

	/**
	 * @param AuthToken $authToken
	 * @return User $this
	 */
	public function removeAuthToken(AuthToken $authToken): self
	{
		if ($this->authTokens->removeElement($authToken)) {
			// set the owning side to null (unless already changed)
			if ($authToken->getHolder() === $this) {
				$authToken->setHolder(null);
			}
		}

		return $this;
	}

	/**
	 * @return DateTimeInterface|null
	 */
	public function getCreatedAt(): ?DateTimeInterface
	{
		return $this->createdAt;
	}

	/**
	 * @param DateTimeInterface $createdAt
	 * @return User $this
	 */
	public function setCreatedAt(DateTimeInterface $createdAt): self
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return DateTimeInterface|null
	 */
	public function getUpdatedAt(): ?DateTimeInterface
	{
		return $this->updatedAt;
	}

	/**
	 * @param DateTimeInterface $updatedAt
	 * @return User $this
	 */
	public function setUpdatedAt(DateTimeInterface $updatedAt): self
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * @return Collection|LoginFailed[]
	 */
	public function getLoginFails(): Collection
	{
		return $this->loginFaileds;
	}

	/**
	 * @param LoginFailed $loginFailed
	 * @return $this
	 */
	public function addLoginFailed(LoginFailed $loginFailed): self
	{
		if (!$this->loginFaileds->contains($loginFailed)) {
			$this->loginFaileds[] = $loginFailed;
			$loginFailed->setTarget($this);
		}

		return $this;
	}

	/**
	 * @param LoginFailed $loginFailed
	 * @return $this
	 */
	public function removeLoginFailed(LoginFailed $loginFailed): self
	{
		if ($this->loginFaileds->removeElement($loginFailed)) {
			// set the owning side to null (unless already changed)
			if ($loginFailed->getTarget() === $this) {
				$loginFailed->setTarget(null);
			}
		}

		return $this;
	}

	/**
	 * @return UserData
	 */
	public function getUserData(): ?UserData
	{
		return $this->userData;
	}

	/**
	 * @param UserData $userData
	 * @return $this
	 */
	public function setUserData(UserData $userData): self
	{
		// set the owning side of the relation if necessary
		if ($userData->getHolder() !== $this) {
			$userData->setHolder($this);
		}

		$this->userData = $userData;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getActive(): int
	{
		return $this->active;
	}

	public function isActive(): bool
	{
		return $this->getActive() === self::ACTIVE;
	}

	/**
	 * @param int $active
	 */
	public function setActive(int $active): void
	{
		$this->active = $active;
	}

}
