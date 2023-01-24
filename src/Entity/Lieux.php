<?php

namespace App\Entity;

use App\Repository\LieuxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(nullable: true)]
    private ?int $nbPersonneNecessaire = null;

    #[ORM\ManyToOne(inversedBy: 'lieux')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeLieux $TypeLieux = null;

    #[ORM\ManyToOne(inversedBy: 'lieux')]
    private ?Adresse $adresse = null;

    #[ORM\ManyToMany(targetEntity: Contact::class, inversedBy: 'lieux', cascade: ['persist'])]
    private Collection $contacts;

    #[ORM\OneToMany(mappedBy: 'lieux', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
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

    public function getNbPersonneNecessaire(): ?int
    {
        return $this->nbPersonneNecessaire;
    }

    public function setNbPersonneNecessaire(int $nbPersonneNecessaire): self
    {
        $this->nbPersonneNecessaire = $nbPersonneNecessaire;

        return $this;
    }

    public function getTypeLieux(): ?TypeLieux
    {
        return $this->TypeLieux;
    }

    public function setTypeLieux(?TypeLieux $TypeLieux): self
    {
        $this->TypeLieux = $TypeLieux;

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
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        $this->contacts->removeElement($contact);

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
            $booking->setLieux($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getLieux() === $this) {
                $booking->setLieux(null);
            }
        }

        return $this;
    }
}
