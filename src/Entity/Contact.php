<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\Email(message: "Cette email: {{ value }} n'est pas valide.")]
    private ?string $mail = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Regex(pattern: "#\d{10}#",message: "Numéro de téléphone incorrect",)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $benevole = false;

    #[ORM\Column]
    private ?bool $proximite = false;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $fonction = [];

    #[ORM\ManyToMany(targetEntity: Lieux::class, mappedBy: 'contacts')]
    private Collection $lieux;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Adresse $adresse = null;

    #[ORM\ManyToMany(targetEntity: Booking::class, mappedBy: 'contacts')]
    private Collection $bookings;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }
    public function __toString() {
        $res = $this->nom . " " . $this->prenom . ", " . $this->mail . " " . $this->telephone ;
        return $res;
    }

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

    public function getFonction(): array
    {
        return $this->fonction;
    }

    public function setFonction(?array $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    /**
     * @return Collection<int, Lieux>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(Lieux $lieux): self
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->addContact($this);
        }

        return $this;
    }

    public function removeLieux(Lieux $lieux): self
    {
        if ($this->lieux->removeElement($lieux)) {
            $lieux->removeContact($this);
        }

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->addContact($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            $booking->removeContact($this);
        }

        return $this;
    }
}
