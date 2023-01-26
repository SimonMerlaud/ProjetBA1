<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use Doctrine\Persistence\ObjectManager;

class MagasinFixtures
{
    public function charger(ObjectManager $manager): void
    {
        $lieux = new Lieux();

        $typeLieux = new TypeLieux();
        $typeLieux->setLibelle("magasin");
        $lieux->setTypeLieux($typeLieux);
        $typeLieux->addLieux($lieux);
        $manager->persist($typeLieux);

        $adresse = new Adresse();
        $adresse->setCodePostale('86000')
            ->setNumeroRue('4')
            ->setRue('Rue des Frère Lumière')
            ->setVille('Poitiers');

        $contact = new Contact();
        $nom = "netto";
        $prenom = "netto";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0549561267');

        $lieux->setAdresse($adresse)
            ->setNom('Netto')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);

        //========================2eme mag============================================================
        $lieux = new Lieux();
        $contact = new Contact();
        $adresse = new Adresse();
        $adresse->setCodePostale('86500')
            ->setNumeroRue('66')
            ->setRue('Boulevard Strasbourg')
            ->setVille('Montmorillon');

        $nom = "Panier";
        $prenom = "Sympa";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0516321741');

        $lieux->setAdresse($adresse)
            ->setNom('Panier Sympa')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);
        $manager->flush();

        //========================3eme mag============================================================
        $lieux = new Lieux();
        $contact = new Contact();
        $adresse = new Adresse();
        $adresse->setCodePostale('86100')
            ->setNumeroRue('144')
            ->setRue('Avenue Maréchal Foch')
            ->setVille('Chatellerault');

        $nom = "E";
        $prenom = "Leclerc";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0549200450');

        $lieux->setAdresse($adresse)
            ->setNom('E.Leclerc')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);

        //========================3eme mag============================================================
        $lieux = new Lieux();
        $contact = new Contact();
        $adresse = new Adresse();
        $adresse->setCodePostale('86360')
            ->setNumeroRue('1')
            ->setRue('Avenue grands Philambins')
            ->setVille('Chasseneuil du Poitou');

        $nom = "Grand";
        $prenom = "Frais";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0549881659');

        $lieux->setAdresse($adresse)
            ->setNom('Grand Frais')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);

        $manager->flush();
    }
}
