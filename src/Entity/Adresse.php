<?php

namespace App\Entity;

use App\Repository\AdresseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5)]
    private ?string $codePostale = null;

    #[ORM\Column(length: 50)]
    private ?string $ville = null;

    #[ORM\Column(length: 100)]
    private ?string $rue = null;

    #[ORM\Column]
    private ?int $numeroRue = null;

    #[ORM\Column(nullable: true)]
    private ?int $numeroAppart = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodePostale(): ?string
    {
        return $this->codePostale;
    }

    public function setCodePostale(string $codePostale): self
    {
        $this->codePostale = $codePostale;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getNumeroRue(): ?int
    {
        return $this->numeroRue;
    }

    public function setNumeroRue(int $numeroRue): self
    {
        $this->numeroRue = $numeroRue;

        return $this;
    }

    public function getNumeroAppart(): ?int
    {
        return $this->numeroAppart;
    }

    public function setNumeroAppart(?int $numeroAppart): self
    {
        $this->numeroAppart = $numeroAppart;

        return $this;
    }
}
