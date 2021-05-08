<?php

declare(strict_types=1);

namespace App\User\Authorization\System\Entity;

use App\User\Authorization\System\Repository\LoginFailedRepository;
use App\User\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LoginFailedRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class LoginFailed
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="loginFaileds", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $client;

    /**
     * @ORM\Column(type="datetime")
     */
    private $failedAt;

	/**
	 * @ORM\PrePersist
	 */
	public function updatedTimestamps(): void
	{
		if ($this->failedAt === null) {
			$this->failedAt = new \DateTime('now');
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
	 * @return User
	 */
	public function getTarget(): User
    {
        return $this->target;
    }

	/**
	 * @param User $target
	 * @return $this
	 */
	public function setTarget(User $target): self
    {
        $this->target = $target;

        return $this;
    }

	/**
	 * @return string
	 */
	public function getIp(): ?string
    {
        return $this->ip;
    }

	/**
	 * @param string $ip
	 * @return $this
	 */
	public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

	/**
	 * @return string
	 */
	public function getClient(): ?string
    {
        return $this->client;
    }

	/**
	 * @param string $client
	 * @return $this
	 */
	public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

	/**
	 * @return DateTimeInterface
	 */
	public function getFailedAt(): DateTimeInterface
    {
        return $this->failedAt;
    }

	/**
	 * @param DateTimeInterface $failedAt
	 * @return $this
	 */
	public function setFailedAt(DateTimeInterface $failedAt): self
    {
        $this->failedAt = $failedAt;

        return $this;
    }
}
