<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', unique: true)]
    private ?int $id_game_api = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column]
    private array $data = [];

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    
    public function getIdGameApi(): ?string
    {
        return $this->id_game_api;
    }
    
    public function setIdGameApi(string $id_game_api): static
    {
        $this->id_game_api = $id_game_api;
        
        return $this;
    }

    /**
     * @var Collection<int, Platform>
     */
    #[ORM\ManyToMany(targetEntity: Platform::class, inversedBy: 'games')]
    private Collection $platform;

    /**
     * @var Collection<int, GamesList>
     */
    #[ORM\OneToMany(targetEntity: GamesList::class, mappedBy: 'game')]
    private Collection $gamesLists;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'game')]
    private Collection $scores;

    public function __construct()
    {
        $this->platform = new ArrayCollection();
        $this->gamesLists = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->scores = new ArrayCollection();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Collection<int, Platform>
     */
    public function getPlatform(): Collection
    {
        return $this->platform;
    }

    public function addPlatform(Platform $platform): static
    {
        if (!$this->platform->contains($platform)) {
            $this->platform->add($platform);
        }

        return $this;
    }

    public function removePlatform(Platform $platform): static
    {
        $this->platform->removeElement($platform);

        return $this;
    }

    /**
     * @return Collection<int, GamesList>
     */
    public function getGamesLists(): Collection
    {
        return $this->gamesLists;
    }

    public function addGamesList(GamesList $gamesList): static
    {
        if (!$this->gamesLists->contains($gamesList)) {
            $this->gamesLists->add($gamesList);
            $gamesList->setGame($this);
        }

        return $this;
    }

    public function removeGamesList(GamesList $gamesList): static
    {
        if ($this->gamesLists->removeElement($gamesList)) {
            // set the owning side to null (unless already changed)
            if ($gamesList->getGame() === $this) {
                $gamesList->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setGame($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getGame() === $this) {
                $comment->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(Score $score): static
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setGame($this);
        }

        return $this;
    }

    public function removeScore(Score $score): static
    {
        if ($this->scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getGame() === $this) {
                $score->setGame(null);
            }
        }

        return $this;
    }

}
