<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
 */
class Tournament
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\ManyToOne(targetEntity=VideoGame::class, inversedBy="tournaments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $videogame;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="createdTournaments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creatorUser;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="firstPlaceTournaments")
     */
    private $firstPlace;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="secondPlaceTournaments")
     */
    private $secondPlace;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="thirdPlaceTournaments")
     */
    private $thirdPlace;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="fourthPlaceTournaments")
     */
    private $fourthPlace;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="playedTournaments")
     */
    private $players;

    /**
     * @ORM\OneToMany(targetEntity=Points::class, mappedBy="tournament")
     */
    private $points;

    /**
     * @ORM\Column(type="boolean")
     * @ORM\JoinColumn(nullable=false)
     */
    private $finished;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getVideogame(): ?VideoGame
    {
        return $this->videogame;
    }

    public function setVideogame(?VideoGame $videogame): self
    {
        $this->videogame = $videogame;

        return $this;
    }

    public function getCreatorUser(): ?User
    {
        return $this->creatorUser;
    }

    public function setCreatorUser(?User $creatorUser): self
    {
        $this->creatorUser = $creatorUser;

        return $this;
    }

    public function getFirstPlace(): ?User
    {
        return $this->firstPlace;
    }

    public function setFirstPlace(?User $firstPlace): self
    {
        $this->firstPlace = $firstPlace;

        return $this;
    }

    public function getSecondPlace(): ?User
    {
        return $this->secondPlace;
    }

    public function setSecondPlace(?User $secondPlace): self
    {
        $this->secondPlace = $secondPlace;

        return $this;
    }

    public function getThirdPlace(): ?User
    {
        return $this->thirdPlace;
    }

    public function setThirdPlace(?User $thirdPlace): self
    {
        $this->thirdPlace = $thirdPlace;

        return $this;
    }

    public function getFourthPlace(): ?User
    {
        return $this->fourthPlace;
    }

    public function setFourthPlace(?User $fourthPlace): self
    {
        $this->fourthPlace = $fourthPlace;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(User $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }

        return $this;
    }

    public function removePlayer(User $player): self
    {
        $this->players->removeElement($player);

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
            $point->setTournament($this);
        }

        return $this;
    }

    public function removePoint(Points $point): self
    {
        if ($this->points->removeElement($point)) {
            // set the owning side to null (unless already changed)
            if ($point->getTournament() === $this) {
                $point->setTournament(null);
            }
        }

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }
}
