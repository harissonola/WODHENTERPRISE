<?php

namespace App\Entity;

use App\Repository\PremiumRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PremiumRepository::class)]
class Premium
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'premium', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $payedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $ExpiredAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPayedAt(): ?\DateTimeImmutable
    {
        return $this->payedAt;
    }

    public function setPayedAt(\DateTimeImmutable $payedAt): static
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->ExpiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $ExpiredAt): static
    {
        $this->ExpiredAt = $ExpiredAt;

        return $this;
    }
}
