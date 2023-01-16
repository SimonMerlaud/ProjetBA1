<?php

namespace App\Controller;

use App\Entity\CompteBenevole;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use App\Form\CompteType;
use Doctrine\ORM\EntityManager;
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
             return $this->redirectToRoute('compte_index');
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

    #[Route(path: '/index', name: '_index')]
    public function accueilCompte(): Response
    {
        return $this->render('compte/index.html.twig');
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

    #[Route(path: '/affectation', name: '_affectation')]
    public function assosiationMagBenevole(EntityManagerInterface $em): Response{

        //afficher la liste des magasins
        //quand on choisit un mag, on choisit un crénaux
        //quand on a choisi un crénaux pour un mag, affiche la liste des bénévoles disponible (au nive des crénaux)
        //Faire un drag and drop pour choisir des bénévoles

        //affiche /magasin TODO demander si page de base de affecattion se fait dans /magasin (pour choisir les magasins)

        //Recuperer tous les bénévoles
        //afficher la liste des bénévoles
        //drag and drop les bénévoles dans la deuxiemes liste (initialement vide) pour les séléctionner
        //Réfléchir à quoi mettre comme informations pour identifier le béné (juste nom prenom, adresse mail, adresse, proximité,...)
        //nom prenom code postale ville
        //mettre bootsrap à la place du css
        //Surement mettre de la pagination (Pas sur) ou une "fenetre" deroulante (un encadré diff de la page elle meme)
        //Cliquer sur un bouton valider pour valider les affectations


        $typeLieux = $em->getRepository(TypeLieux::class)->findOneBy(array('libelle'=>"magasin"));
        $list_mag = $em->getRepository(Lieux::class)->findBy(array('TypeLieux' => $typeLieux));

        $list_bene = $em->getRepository(Contact::class)->findBy(array('benevole' => true));

        dump($list_bene);

        dump($list_mag);
        return $this->render('compte/affectation.html.twig', ['list_bene' => $list_bene]);
        //return $this->render('benevole/test.html.twig');
    }
}
