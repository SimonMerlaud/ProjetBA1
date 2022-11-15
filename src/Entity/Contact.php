<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $nom = null;

    #[ORM\Column(length: 40)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $benevole = null;

    #[ORM\Column]
    private ?bool $proximite = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $dateDisponibilite = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $dateAffectation = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $fonction = [];

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isBenevole(): ?bool
    {
        return $this->benevole;
    }

    public function setBenevole(bool $benevole): self
    {
        $this->benevole = $benevole;

        return $this;
    }

    public function isProximite(): ?bool
    {
        return $this->proximite;
    }

    public function setProximite(bool $proximite): self
    {
        $this->proximite = $proximite;

        return $this;
    }

    public function getDateDisponibilite(): array
    {
        return $this->dateDisponibilite;
    }

    public function setDateDisponibilite(?array $dateDisponibilite): self
    {
        $this->dateDisponibilite = $dateDisponibilite;

        return $this;
    }

    public function getDateAffectation(): array
    {
        return $this->dateAffectation;
    }

    public function setDateAffectation(?array $dateAffectation): self
    {
        $this->dateAffectation = $dateAffectation;

        return $this;
    }

    public function getFonction(): array
    {
        return $this->fonction;
    }

    public function setFonction(?array $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }
}
