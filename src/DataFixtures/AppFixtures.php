<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private  $passwordHasher;

    public function __construct (UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->passwordHasher = $userPasswordHasherInterface;
    }

    public function load(ObjectManager $manager, ): void
    {
        $magasinFixtures = new MagasinFixtures();
        $magasinFixtures->charger($manager);

        $compteFixtures = new CompteFixtures();
        $compteFixtures->charger($manager, $this->passwordHasher);

        $bookingFixtures = new BookingFixtures();
        $bookingFixtures->charger($manager, $this->passwordHasher);

        $assoFixtures = new AssoFixtures();
        $assoFixtures->charger($manager);

        $manager->flush();
    }
}
