<?php

namespace App\Entity;

use App\Repository\ActivationParamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivationParamRepository::class)]
class ActivationParam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activationParams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Audio $audio = null;

    #[ORM\ManyToOne(inversedBy: 'activationParams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Run $run = null;

    #[ORM\Column(nullable: true)]
    private ?int $time = null;

    #[ORM\Column(nullable: true)]
    private ?int $distance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAudio(): ?Audio
    {
        return $this->audio;
    }

    public function setAudio(?Audio $audio): static
    {
        $this->audio = $audio;

        return $this;
    }

    public function getRun(): ?Run
    {
        return $this->run;
    }

    public function setRun(?Run $run): static
    {
        $this->run = $run;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): static
    {
        $this->distance = $distance;

        return $this;
    }
}
