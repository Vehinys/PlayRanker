<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte est déjà associé à cette information.')]
#[UniqueEntity(fields: ['pseudo'], message: 'Un compte est déjà associé à cette information.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ** Propriétés de l'entité User **

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // Identifiant unique de l'utilisateur

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank] // Validation pour s'assurer que le champ n'est pas vide
    #[Assert\Email] // Validation pour vérifier que le champ contient une adresse e-mail valide
    private ?string $email = null; // Adresse e-mail de l'utilisateur

    #[ORM\Column(length: 100)]
    private ?string $pseudo = null; // Pseudo de l'utilisateur

    #[ORM\Column]
    private ?string $password = null; // Mot de passe de l'utilisateur

    #[ORM\Column(type: 'string')]
    private ?string $avatar = null; // Mot de passe de l'utilisateur
    
    #[ORM\Column(type: 'json')]
    private array $roles = []; // Rôles de l'utilisateur (ex: ROLE_USER, ROLE_ADMIN)

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $gamerTagPlaystation = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $gamerTagXbox = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $youtube = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitch = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'user', orphanRemoval: true, cascade: ["remove"])]
    private Collection $topics;

    /**
     * @var Collection<int, GamesList>
     */
    #[ORM\OneToMany(targetEntity: GamesList::class, mappedBy: 'user')]
    private Collection $gamesLists;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'user')]
    private Collection $scores;

    // ** Constructeur **

    public function __construct()
    {
        $this->roles[] = 'ROLE_USER'; // Attribution d'un rôle par défaut
        $this->posts = new ArrayCollection(); // Initialisation de la collection de posts
        $this->topics = new ArrayCollection(); // Initialisation de la collection de topics
        $this->gamesLists = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->scores = new ArrayCollection();
    }

    // ** Getters **

    public function getId(): ?int
    {
        return $this->id; // Retourne l'identifiant de l'utilisateur
    }

    public function getEmail(): ?string
    {
        return $this->email; // Retourne l'e-mail de l'utilisateur
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email; // Retourne l'identifiant de l'utilisateur pour l'authentification
    }

    public function getRoles(): array
    {
        return $this->roles; // Retourne les rôles de l'utilisateur
    }

    public function getPassword(): ?string
    {
        return $this->password; // Retourne le mot de passe de l'utilisateur
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo; // Retourne le pseudo de l'utilisateur
    }

    public function getAvatar(): ?string
    {
        return $this->avatar; // Retourne l'avatar de l'utilisateur
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts; // Retourne la collection de posts associés
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics; // Retourne la collection de topics associés
    }

    // ** Setters **

    public function setEmail(string $email): static
    {
        $this->email = $email; // Définit l'e-mail de l'utilisateur
        return $this;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles; // Définit les rôles de l'utilisateur
        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password; // Définit le mot de passe de l'utilisateur
        return $this;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo; // Définit le pseudo de l'utilisateur
        return $this;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar; // Définit l'avatar de l'utilisateur
        return $this;
    }

    // ** Méthodes d'interface pour l'authentification **

    public function eraseCredentials(): void
    {
        // N'implémente pas de logique ici pour effacer les informations sensibles
    }

    // ** Méthode d'anonymisation **
    public function anonymize(
        
        UserPasswordHasherInterface $passwordHasher
        
    ): void {
        // Remplace les données personnelles par des valeurs anonymisées
        $this->email = 'emaildelete-' . uniqid() . '@example.com'; // E-mail anonyme
        $this->pseudo = 'UserDelete'; // Pseudo anonyme
        $this->avatar = null; // Supprime l'avatar
        $this->password = $passwordHasher->hashPassword($this, 'PasswordDelete'); // Hachage du nouveau mot de passe
        $this->roles = ['ROLE_USERDELETE']; // Restaure le rôle par défaut
    }


    // ** Méthodes de gestion des collections (Post) **

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post); // Ajoute un post à la collection de posts
            $post->setUser($this); // Définit l'utilisateur du post
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // Si le post est retiré de la collection
            if ($post->getUser() === $this) {
                $post->setUser(null); // Supprime l'utilisateur du post
            }
        }

        return $this;
    }

    // ** Méthodes de gestion des collections (Topic) **

    public function addTopic(Topic $topic): static
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic); // Ajoute un topic à la collection de topics
            $topic->setUser($this); // Définit l'utilisateur du topic
        }

        return $this;
    }

    public function removeTopic(Topic $topic): static
    {
        if ($this->topics->removeElement($topic)) {
            // Si le topic est retiré de la collection
            if ($topic->getUser() === $this) {
                $topic->setUser(null); // Supprime l'utilisateur du topic
            }
        }

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
            $gamesList->setUser($this);
        }

        return $this;
    }

    public function removeGamesList(GamesList $gamesList): static
    {
        if ($this->gamesLists->removeElement($gamesList)) {
            // set the owning side to null (unless already changed)
            if ($gamesList->getUser() === $this) {
                $gamesList->setUser(null);
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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
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
            $score->setUser($this);
        }

        return $this;
    }

    public function removeScore(Score $score): static
    {
        if ($this->scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getUser() === $this) {
                $score->setUser(null);
            }
        }

        return $this;
    }

    public function getGamerTagPlaystation(): ?string
    {
        return $this->gamerTagPlaystation;
    }

    public function setGamerTagPlaystation(?string $gamerTagPlaystation): static
    {
        $this->gamerTagPlaystation = $gamerTagPlaystation;

        return $this;
    }

    public function getGamerTagXbox(): ?string
    {
        return $this->gamerTagXbox;
    }

    public function setGamerTagXbox(?string $gamerTagXbox): static
    {
        $this->gamerTagXbox = $gamerTagXbox;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): static
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getTwitch(): ?string
    {
        return $this->twitch;
    }
    
    public function setTwitch(?string $twitch): static
    {
        $this->twitch = $twitch;
    
        return $this;
    }

}
