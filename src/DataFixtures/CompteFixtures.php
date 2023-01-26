<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\CompteBenevole;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CompteFixtures
{
    public function charger(ObjectManager $manager,UserPasswordHasherInterface $passwordHasher ): void
    {
        function createComptes($mail, $role, $nbCompte, $manager, UserPasswordHasherInterface $passwordHasher){
            $compte = new CompteBenevole();
            $benevole = new Contact();
            $benevole->setNom("nom")
                ->setPrenom("prenom")
                ->setMail($nbCompte . $mail);

            $adresse = new Adresse();
            $adresse->setCodePostale("15000")
                ->setVille("ville")
                ->setRue("Rue")
                ->setNumeroRue(1);
            $benevole->setAdresse($adresse);
            $compte->setContact($benevole);
            $compte->setMail($nbCompte . $mail)
                ->setRoles([$role])
                ->setPassword($passwordHasher->hashPassword($compte, "potdemasse"));
            if($role == "ROLE_BENEVOLE")#
                $benevole->setBenevole(true);
            $manager->persist($benevole);
            $manager->persist($adresse);
            $manager->persist($compte);
        }

        foreach (range(1, 3) as $i) {
            createComptes("bene@mail.com", "ROLE_BENEVOLE", $i, $manager, $passwordHasher);
        }
        createComptes("ba@mail.com", "ROLE_BA", 1, $manager, $passwordHasher);

        $manager->flush();

    }
}