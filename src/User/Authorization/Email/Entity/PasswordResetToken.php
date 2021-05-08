<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Entity;

use App\User\Authorization\Email\Repository\PasswordResetTokenRepository;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;


/**
 * @ORM\Entity(repositoryClass=PasswordResetTokenRepository::class)
 * @ORM\Table(name="`email_password_reset_token`")
 * @ORM\HasLifecycleCallbacks
 */
class PasswordResetToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $holder;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
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
	 * @return string
	 */
	public function getToken(): string
    {
        return $this->token;
    }

	/**
	 * @param string $token
	 * @return $this
	 */
	public function setToken(string $token): self
    {
        $this->token = $token;

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
	 * @return $this
	 */
	public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

	/**
	 * @return DateTimeInterface|null
	 */
	public function getExpiredAt(): ?DateTimeInterface
    {
        return $this->expiredAt;
    }

	/**
	 * @param DateTimeInterface $expiredAt
	 * @return $this
	 */
	public function setExpiredAt(DateTimeInterface $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}
