<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Booking;
use App\Entity\CompteBenevole;
use App\Entity\Contact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BookingFixtures
{
    public function charger(ObjectManager $manager, UserPasswordHasherInterface $passwordHasher): void
    {
            $booking = new Booking();
            $booking->setTitle("Libre ");
            $booking->setBeginAt(new \DateTime('2023-03-01 08:00'));
            $booking->setEndAt(new \DateTime('2023-03-01 18:00'));
            $booking->setMagasinId(0);
            $booking->addContact($manager->getRepository(Contact::class)->findOneBy(['mail'=>"1bene@mail.com"]));

            $manager->persist($booking);
            $manager->flush();
    }
}
