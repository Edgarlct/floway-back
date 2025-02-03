<?php

namespace App\Entity;

use App\Repository\RunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RunRepository::class)]
class Run
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'runs')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeObjective = null;

    #[ORM\Column(nullable: true)]
    private ?int $distanceObjective = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isBuyable = null;

    #[ORM\Column(nullable: true)]
    private ?int $price = null;

    /**
     * @var Collection<int, ActivationParam>
     */
    #[ORM\OneToMany(targetEntity: ActivationParam::class, mappedBy: 'run')]
    private Collection $activationParams;

    /**
     * @var Collection<int, RunBuyer>
     */
    #[ORM\OneToMany(targetEntity: RunBuyer::class, mappedBy: 'run')]
    private Collection $runBuyers;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDeleted = null;

    public function __construct()
    {
        $this->activationParams = new ArrayCollection();
        $this->runBuyers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTimeObjective(): ?int
    {
        return $this->timeObjective;
    }

    public function setTimeObjective(?int $timeObjective): static
    {
        $this->timeObjective = $timeObjective;

        return $this;
    }

    public function getDistanceObjective(): ?int
    {
        return $this->distanceObjective;
    }

    public function setDistanceObjective(?int $distanceObjective): static
    {
        $this->distanceObjective = $distanceObjective;

        return $this;
    }

    public function isBuyable(): ?bool
    {
        return $this->isBuyable;
    }

    public function setBuyable(?bool $isBuyable): static
    {
        $this->isBuyable = $isBuyable;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

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
            $activationParam->setRun($this);
        }

        return $this;
    }

    public function removeActivationParam(ActivationParam $activationParam): static
    {
        if ($this->activationParams->removeElement($activationParam)) {
            // set the owning side to null (unless already changed)
            if ($activationParam->getRun() === $this) {
                $activationParam->setRun(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RunBuyer>
     */
    public function getRunBuyers(): Collection
    {
        return $this->runBuyers;
    }

    public function addRunBuyer(RunBuyer $runBuyer): static
    {
        if (!$this->runBuyers->contains($runBuyer)) {
            $this->runBuyers->add($runBuyer);
            $runBuyer->setRun($this);
        }

        return $this;
    }

    public function removeRunBuyer(RunBuyer $runBuyer): static
    {
        if ($this->runBuyers->removeElement($runBuyer)) {
            // set the owning side to null (unless already changed)
            if ($runBuyer->getRun() === $this) {
                $runBuyer->setRun(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
