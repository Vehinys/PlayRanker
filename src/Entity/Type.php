<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;
    
    /**
     * @var Collection<int, GamesList>
     */
    #[ORM\ManyToMany(targetEntity: GamesList::class, inversedBy: 'types')]
    private Collection $Types;
    
    public function __construct()
    {
        $this->Types = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, GamesList>
     */
    public function getTypes(): Collection
    {
        return $this->Types;
    }

    public function addType(GamesList $type): static
    {
        if (!$this->Types->contains($type)) {
            $this->Types->add($type);
        }

        return $this;
    }

    public function removeType(GamesList $type): static
    {
        $this->Types->removeElement($type);

        return $this;
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



}
