<?php

namespace App\Entity;

use App\Repository\LieuxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LieuxRepository::class)]
class Lieux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $nbPersonneNecessaire = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $datesMag = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNbPersonneNecessaire(): ?int
    {
        return $this->nbPersonneNecessaire;
    }

    public function setNbPersonneNecessaire(int $nbPersonneNecessaire): self
    {
        $this->nbPersonneNecessaire = $nbPersonneNecessaire;

        return $this;
    }

    public function getDatesMag(): array
    {
        return $this->datesMag;
    }

    public function setDatesMag(array $datesMag): self
    {
        $this->datesMag = $datesMag;

        return $this;
    }
}
