<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['readData'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[Groups(['readData'])]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $picturePath = null;

    /**
     * @var Collection<int, Audio>
     */
    #[ORM\OneToMany(targetEntity: Audio::class, mappedBy: 'user')]
    private Collection $audio;

    /**
     * @var Collection<int, Run>
     */
    #[ORM\OneToMany(targetEntity: Run::class, mappedBy: 'user')]
    private Collection $runs;

    /**
     * @var Collection<int, RunBuyer>
     */
    #[ORM\OneToMany(targetEntity: RunBuyer::class, mappedBy: 'user')]
    private Collection $runBuyers;

    #[Groups(['readData'])]
    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $alias = null;

    /**
     * @var Collection<int, FriendNotificationSettings>
     */
    #[ORM\OneToMany(targetEntity: FriendNotificationSettings::class, mappedBy: 'user')]
    private Collection $friendNotificationSettings;

    public function __construct()
    {
        $this->audio = new ArrayCollection();
        $this->runs = new ArrayCollection();
        $this->runBuyers = new ArrayCollection();
        $this->friendNotificationSettings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(?string $picturePath): static
    {
        $this->picturePath = $picturePath;

        return $this;
    }

    /**
     * @return Collection<int, Audio>
     */
    public function getAudio(): Collection
    {
        return $this->audio;
    }

    public function addAudio(Audio $audio): static
    {
        if (!$this->audio->contains($audio)) {
            $this->audio->add($audio);
            $audio->setUser($this);
        }

        return $this;
    }

    public function removeAudio(Audio $audio): static
    {
        if ($this->audio->removeElement($audio)) {
            // set the owning side to null (unless already changed)
            if ($audio->getUser() === $this) {
                $audio->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Run>
     */
    public function getRuns(): Collection
    {
        return $this->runs;
    }

    public function addRun(Run $run): static
    {
        if (!$this->runs->contains($run)) {
            $this->runs->add($run);
            $run->setUser($this);
        }

        return $this;
    }

    public function removeRun(Run $run): static
    {
        if ($this->runs->removeElement($run)) {
            // set the owning side to null (unless already changed)
            if ($run->getUser() === $this) {
                $run->setUser(null);
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
            $runBuyer->setUser($this);
        }

        return $this;
    }

    public function removeRunBuyer(RunBuyer $runBuyer): static
    {
        if ($this->runBuyers->removeElement($runBuyer)) {
            // set the owning side to null (unless already changed)
            if ($runBuyer->getUser() === $this) {
                $runBuyer->setUser(null);
            }
        }

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return Collection<int, FriendNotificationSettings>
     */
    public function getFriendNotificationSettings(): Collection
    {
        return $this->friendNotificationSettings;
    }

    public function addFriendNotificationSetting(FriendNotificationSettings $friendNotificationSetting): static
    {
        if (!$this->friendNotificationSettings->contains($friendNotificationSetting)) {
            $this->friendNotificationSettings->add($friendNotificationSetting);
            $friendNotificationSetting->setUser($this);
        }

        return $this;
    }

    public function removeFriendNotificationSetting(FriendNotificationSettings $friendNotificationSetting): static
    {
        if ($this->friendNotificationSettings->removeElement($friendNotificationSetting)) {
            // set the owning side to null (unless already changed)
            if ($friendNotificationSetting->getUser() === $this) {
                $friendNotificationSetting->setUser(null);
            }
        }

        return $this;
    }
}
