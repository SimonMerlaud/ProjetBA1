<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $beginAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $endAt = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?Contact $contact = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?Lieux $lieux = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresse(): ?string
    {
        return $this->lieux->getAdresse();
    }

    public function getMail(): ?string
    {
        return $this->contact->getMail();
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTime $beginAt): self
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTime $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
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

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getLieux(): ?Lieux
    {
        return $this->lieux;
    }

    public function setLieux(?Lieux $lieux): self
    {
        $this->lieux = $lieux;

        return $this;
    }
}
