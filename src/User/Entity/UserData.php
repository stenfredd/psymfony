<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\User\Repository\UserDataRepository;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserDataRepository::class)
 */
class UserData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $nickname;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="userData", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $holder;

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
	public function getNickname(): ?string
    {
        return $this->nickname;
    }

	/**
	 * @param string|null $nickname
	 * @return $this
	 */
	public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

	/**
	 * @return User|null
	 */
	public function getHolder(): ?User
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
}
