<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\CompteBenevole;
use App\Entity\Contact;
use App\Form\BenevoleType;
use App\Form\CompteBenevoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/benevole', name: 'benevole_')]
class BenevoleController extends AbstractController
{


    #[Route('', name: '')]
    public function index(): Response{
        return $this->render('benevole/form.html.twig', [
            'controller_name' => 'BenevoleController',
        ]);
    }


    #[Route('/inscription', name: 'inscription_')]
    public function inscriptionAction(EntityManagerInterface $em, Request $request,UserPasswordHasherInterface $passwordHasher): Response{
        $compte = new CompteBenevole();
        $benevole = new Contact();
        $adresse = new Adresse();
        $benevole->setAdresse($adresse);
        $compte->setContact($benevole);
        $form = $this->createForm(CompteBenevoleType::class, $compte);
        $form->add('valider', SubmitType::class, ['label' => 'Valider']);
        $form->handleRequest($request);
        $benevole->setBenevole(true);
        $compte->setRoles(array("ROLE_BENEVOLE"));

        if($form->isSubmitted() && $form->isValid()){
            $benevole->setMail($compte->getMail());
            if($benevole->getMail() || $benevole->getTelephone()){
                $hashedPassword = $passwordHasher->hashPassword(
                    $compte,
                    $compte->getPassword()
                );
                $compte->setPassword($hashedPassword);
                $em->persist($benevole);
                $em->persist($adresse);
                $em->persist($compte);
                $em->flush();

                return $this->redirectToRoute('compte_login');

            }else{
                $this->addFlash('error', "Le téléphone ou le mail doit être renseigné");
                return $this->redirectToRoute("benevole_inscription_");
            }
            //faire un render sur une page pour dire que
            //le user est bien inscrit
        }
        return $this->render('benevole/form.html.twig', ['form' => $form->createView()]);

    }


}