<?php

declare(strict_types=1);

namespace App\Auth\Entity;

use App\User\Entity\User;
use App\Auth\Repository\AuthTokenRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AuthTokenRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"value"})})
 * @ORM\HasLifecycleCallbacks
 */
class AuthToken
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
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
	 * @var User
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tmp")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $holder;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	private $expiredAt;

	/**
	 * @ORM\PrePersist
	 */
	public function onPrePersist(): void
	{
		$this->createdAt = new \DateTime('now');
	}

	/**
	 * @return int
	 */
	public function getId(): int
    {
        return $this->id;
    }

	/**
	 * @return string
	 */
	public function getValue(): string
    {
        return $this->value;
    }

	/**
	 * @param string $value
	 * @return $this
	 */
	public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

	/**
	 * @return User
	 */
	public function getHolder(): User
    {
        return $this->holder;
    }

	/**
	 * @param User $holder
	 * @return $this
	 */
	public function setHolder(User $holder): self
    {
        $this->holder = $holder;

        return $this;
    }

	/**
	 * @return DateTimeInterface
	 */
	public function getCreatedAt(): ?DateTimeInterface
	{
		return $this->createdAt;
	}

	/**
	 * @param DateTimeInterface $createdAt
	 * @return AuthToken $this
	 */
	public function setCreatedAt(DateTimeInterface $createdAt): self
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return DateTimeInterface
	 */
	public function getExpiredAt(): ?DateTimeInterface
	{
		return $this->expiredAt;
	}

	/**
	 * @param DateTimeInterface $expiredAt
	 * @return AuthToken $this
	 */
	public function setExpiredAt(DateTimeInterface $expiredAt): self
	{
		$this->expiredAt = $expiredAt;

		return $this;
	}
}
