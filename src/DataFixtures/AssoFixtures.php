<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use Doctrine\Persistence\ObjectManager;

class AssoFixtures
{
    public function charger(ObjectManager $manager): void
    {

        $lieux = new Lieux();

        $typeLieux = new TypeLieux();
        $typeLieux->setLibelle("association");
        $lieux->setTypeLieux($typeLieux);
        $typeLieux->addLieux($lieux);
        $manager->persist($typeLieux);

        $adresse = new Adresse();
        $adresse->setCodePostale('86130')
            ->setNumeroRue('5')
            ->setRue('Rue du piff')
            ->setVille('Jaunay marigny');

        $contact = new Contact();
        $nom = "Pas mal le nom";
        $prenom = "Nice prenom";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0549561267');

        $lieux->setAdresse($adresse)
            ->setNom('LaBelleBleu')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);

        //========================2eme asso============================================================
        $lieux = new Lieux();
        $contact = new Contact();
        $adresse = new Adresse();
        $adresse->setCodePostale('86320')
            ->setNumeroRue('66')
            ->setRue('LaBonneRue')
            ->setVille('Montmorillon');

        $nom = "Jacky";
        $prenom = "Miguel";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0516321741');

        $lieux->setAdresse($adresse)
            ->setNom('ARTISHOW')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);
        $manager->flush();

        //========================3eme asso============================================================
        $lieux = new Lieux();
        $contact = new Contact();
        $adresse = new Adresse();
        $adresse->setCodePostale('86100')
            ->setNumeroRue('20')
            ->setRue('Avenue Maréchal Foch')
            ->setVille('Chatellerault');

        $nom = "Mathy";
        $prenom = "Mimi";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0549200450');

        $lieux->setAdresse($adresse)
            ->setNom('1.20m les bras levés')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);

        //========================3eme asso============================================================
        $lieux = new Lieux();
        $contact = new Contact();
        $adresse = new Adresse();
        $adresse->setCodePostale('86360')
            ->setNumeroRue('9')
            ->setRue('Avenue grands Philambins')
            ->setVille('Chasseneuil du Poitou');

        $nom = "Pas";
        $prenom = "JeSais";
        $contact->setNom($nom)
            ->setPrenom($prenom)
            ->setMail($prenom . "." . $nom . "@mail.com")
            ->setAdresse($adresse)
            ->setBenevole(false)
            ->setTelephone('0549881659');

        $lieux->setAdresse($adresse)
            ->setNom('LabelleRouge')
            ->setTypeLieux($typeLieux)
            ->addContact($contact);

        $manager->persist($lieux);
        $manager->persist($adresse);
        $manager->persist($contact);

        $manager->flush();

    }
}