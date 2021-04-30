<?php

namespace App\Auth\Entity;

use App\Auth\Repository\LoginFailedRepository;
use App\User\Entity\User;
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="loginFaileds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255)
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(?User $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getFailedAt(): ?\DateTimeInterface
    {
        return $this->failedAt;
    }

    public function setFailedAt(\DateTimeInterface $failedAt): self
    {
        $this->failedAt = $failedAt;

        return $this;
    }
}
