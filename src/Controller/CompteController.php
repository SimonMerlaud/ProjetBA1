<?php

namespace App\Controller;

use App\Entity\CompteBenevole;
use App\Entity\Contact;
use App\Form\CompteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/compte', name: 'compte')]
class CompteController extends AbstractController
{
    #[Route(path: '/login', name: '_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('accueil');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: '_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/add', name: '_add')]
    public function addCompte(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $em): Response
    {
        $compte = new CompteBenevole();
        $form = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $compte,
                $compte->getPassword()
            );
            $compte->setPassword($hashedPassword);
            $contact = new Contact();
            $contact->setPrenom('ba')
                ->setNom('ba');

            $compte->setContact($contact);
            $em->persist($compte);
            $em->flush();
            $this->addFlash('add', "le compte a été ajouté");
            return $this->redirectToRoute("accueil");
        }
        return $this->render('compte/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
