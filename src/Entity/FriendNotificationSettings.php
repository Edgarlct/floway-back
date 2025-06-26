<?php

namespace App\Entity;

use App\Repository\FriendNotificationSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FriendNotificationSettingsRepository::class)]
class FriendNotificationSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['readData'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'friendNotificationSettings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['readData'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['readData'])]
    private ?User $friend = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['readData'])]
    private ?bool $isNotificationBlock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFriend(): ?User
    {
        return $this->friend;
    }

    public function setFriend(?User $friend): static
    {
        $this->friend = $friend;

        return $this;
    }

    public function isNotificationBlock(): ?bool
    {
        return $this->isNotificationBlock;
    }

    public function setNotificationBlock(?bool $isNotificationBlock): static
    {
        $this->isNotificationBlock = $isNotificationBlock;

        return $this;
    }
}
