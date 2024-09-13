<?php

namespace App\Entity;

use App\Repository\AudioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AudioRepository::class)]
class Audio
{
    #[Groups(['readData'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 500)]
    private ?string $path = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 255)]
    private ?string $duration = null;

    #[ORM\ManyToOne(inversedBy: 'audio')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, ActivationParam>
     */
    #[ORM\OneToMany(targetEntity: ActivationParam::class, mappedBy: 'audio')]
    private Collection $activationParams;

    #[Groups(['readData'])]
    #[ORM\Column]
    private ?int $fileSize = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 255)]
    private ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDeleted = null;

    public function __construct()
    {
        $this->activationParams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
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

    /**
     * @return Collection<int, ActivationParam>
     */
    public function getActivationParams(): Collection
    {
        return $this->activationParams;
    }

    public function addActivationParam(ActivationParam $activationParam): static
    {
        if (!$this->activationParams->contains($activationParam)) {
            $this->activationParams->add($activationParam);
            $activationParam->setAudio($this);
        }

        return $this;
    }

    public function removeActivationParam(ActivationParam $activationParam): static
    {
        if ($this->activationParams->removeElement($activationParam)) {
            // set the owning side to null (unless already changed)
            if ($activationParam->getAudio() === $this) {
                $activationParam->setAudio(null);
            }
        }

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(?bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}