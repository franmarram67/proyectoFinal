<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verified;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profilePicture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dni;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="creatorUser")
     */
    private $createdTournaments;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="firstPlace")
     */
    private $firstPlaceTournaments;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="secondPlace")
     */
    private $secondPlaceTournaments;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="thirdPlace")
     */
    private $thirdPlaceTournaments;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="fourthPlace")
     */
    private $fourthPlaceTournaments;

    /**
     * @ORM\ManyToMany(targetEntity=Tournament::class, mappedBy="players")
     */
    private $playedTournaments;

    /**
     * @ORM\OneToMany(targetEntity=Points::class, mappedBy="user")
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $province;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surname;

    public function __construct()
    {
        $this->createdTournaments = new ArrayCollection();
        $this->firstPlaceTournaments = new ArrayCollection();
        $this->secondPlaceTournaments = new ArrayCollection();
        $this->thirdPlaceTournaments = new ArrayCollection();
        $this->fourthPlaceTournaments = new ArrayCollection();
        $this->playedTournaments = new ArrayCollection();
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(?string $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getCreatedTournaments(): Collection
    {
        return $this->createdTournaments;
    }

    public function addCreatedTournament(Tournament $createdTournament): self
    {
        if (!$this->createdTournaments->contains($createdTournament)) {
            $this->createdTournaments[] = $createdTournament;
            $createdTournament->setCreatorUser($this);
        }

        return $this;
    }

    public function removeCreatedTournament(Tournament $createdTournament): self
    {
        if ($this->createdTournaments->removeElement($createdTournament)) {
            // set the owning side to null (unless already changed)
            if ($createdTournament->getCreatorUser() === $this) {
                $createdTournament->setCreatorUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getFirstPlaceTournaments(): Collection
    {
        return $this->firstPlaceTournaments;
    }

    public function addFirstPlaceTournament(Tournament $firstPlaceTournament): self
    {
        if (!$this->firstPlaceTournaments->contains($firstPlaceTournament)) {
            $this->firstPlaceTournaments[] = $firstPlaceTournament;
            $firstPlaceTournament->setFirstPlace($this);
        }

        return $this;
    }

    public function removeFirstPlaceTournament(Tournament $firstPlaceTournament): self
    {
        if ($this->firstPlaceTournaments->removeElement($firstPlaceTournament)) {
            // set the owning side to null (unless already changed)
            if ($firstPlaceTournament->getFirstPlace() === $this) {
                $firstPlaceTournament->setFirstPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getSecondPlaceTournaments(): Collection
    {
        return $this->secondPlaceTournaments;
    }

    public function addSecondPlaceTournament(Tournament $secondPlaceTournament): self
    {
        if (!$this->secondPlaceTournaments->contains($secondPlaceTournament)) {
            $this->secondPlaceTournaments[] = $secondPlaceTournament;
            $secondPlaceTournament->setSecondPlace($this);
        }

        return $this;
    }

    public function removeSecondPlaceTournament(Tournament $secondPlaceTournament): self
    {
        if ($this->secondPlaceTournaments->removeElement($secondPlaceTournament)) {
            // set the owning side to null (unless already changed)
            if ($secondPlaceTournament->getSecondPlace() === $this) {
                $secondPlaceTournament->setSecondPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getThirdPlaceTournaments(): Collection
    {
        return $this->thirdPlaceTournaments;
    }

    public function addThirdPlaceTournament(Tournament $thirdPlaceTournament): self
    {
        if (!$this->thirdPlaceTournaments->contains($thirdPlaceTournament)) {
            $this->thirdPlaceTournaments[] = $thirdPlaceTournament;
            $thirdPlaceTournament->setThirdPlace($this);
        }

        return $this;
    }

    public function removeThirdPlaceTournament(Tournament $thirdPlaceTournament): self
    {
        if ($this->thirdPlaceTournaments->removeElement($thirdPlaceTournament)) {
            // set the owning side to null (unless already changed)
            if ($thirdPlaceTournament->getThirdPlace() === $this) {
                $thirdPlaceTournament->setThirdPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getFourthPlaceTournaments(): Collection
    {
        return $this->fourthPlaceTournaments;
    }

    public function addFourthPlaceTournament(Tournament $fourthPlaceTournament): self
    {
        if (!$this->fourthPlaceTournaments->contains($fourthPlaceTournament)) {
            $this->fourthPlaceTournaments[] = $fourthPlaceTournament;
            $fourthPlaceTournament->setFourthPlace($this);
        }

        return $this;
    }

    public function removeFourthPlaceTournament(Tournament $fourthPlaceTournament): self
    {
        if ($this->fourthPlaceTournaments->removeElement($fourthPlaceTournament)) {
            // set the owning side to null (unless already changed)
            if ($fourthPlaceTournament->getFourthPlace() === $this) {
                $fourthPlaceTournament->setFourthPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getPlayedTournaments(): Collection
    {
        return $this->playedTournaments;
    }

    public function addPlayedTournament(Tournament $playedTournament): self
    {
        if (!$this->playedTournaments->contains($playedTournament)) {
            $this->playedTournaments[] = $playedTournament;
            $playedTournament->addPlayer($this);
        }

        return $this;
    }

    public function removePlayedTournament(Tournament $playedTournament): self
    {
        if ($this->playedTournaments->removeElement($playedTournament)) {
            $playedTournament->removePlayer($this);
        }

        return $this;
    }

    /**
     * @return Collection|Points[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Points $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setUser($this);
        }

        return $this;
    }

    public function removePoint(Points $point): self
    {
        if ($this->points->removeElement($point)) {
            // set the owning side to null (unless already changed)
            if ($point->getUser() === $this) {
                $point->setUser(null);
            }
        }

        return $this;
    }

    public function getProvince(): ?Province
    {
        return $this->province;
    }

    public function setProvince(?Province $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }
}
