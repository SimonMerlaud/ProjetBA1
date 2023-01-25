<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\CompteBenevole;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use App\Form\CompteBenevoleType;
use App\Form\CompteType;
use App\Form\ModifyLieuxType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    #[Route('/{page}', name: '_index',
        requirements: ['page' => '\d+' ],
        defaults:[ 'page' => 1] )]
    public function accueilCompte($page, EntityManagerInterface $entityManager): Response
    {
        if($page<1)
        {
            throw new NotFoundHttpException("La page $page n'existe pas");
        }
        $comptes = $entityManager->getRepository("App\Entity\CompteBenevole")->myFindAllWithPaging($page);
        $nbTotalPages = intval(ceil(count($comptes) / 20) ) ;
        if ($page >$nbTotalPages && $page !=1){
            throw new NotFoundHttpException("La page n'existe pas");
        }
        return $this->render('compte/index.html.twig', ["comptes" => $comptes, "currentPage" => $page, "nbPage" => $nbTotalPages]);
    }

    #[Route(path: '/add', name: '_add')]
    public function addCompte(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $em): Response
    {
        $compte = new CompteBenevole();
        $benevole = new Contact();
        $adresse = new Adresse();
        $benevole->setAdresse($adresse);
        $compte->setContact($benevole);
        $form = $this->createForm(CompteType::class, $compte);
        $form->add('valider', SubmitType::class, ['label' => 'Valider']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $compte,
                $compte->getPassword()
            );
            $compte->setPassword($hashedPassword);
            $em->persist($benevole);
            $em->persist($adresse);
            $em->persist($compte);
            $em->flush();
            $this->addFlash('add', "le compte a été ajouté");

            return $this->redirectToRoute("compte_index");
        }
        return $this->render('compte/form.html.twig', [
            'form' => $form->createView(),'title'=>"Création"
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

    #[Route(path: '/modify/{id}', name: '_modify')]
    public function editCompte($id,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher,Request $request):Response
    {
        $compte = $entityManager->getRepository("App\Entity\CompteBenevole")->find($id);
        if($compte == null){
            $this->addFlash('error', 'Ce compte n\'existe pas');
            return $this->redirectToRoute('compte_index');
        }
        if($this->isGranted('ROLE_BENEVOLE')){
            $form = $this->createForm(CompteBenevoleType::class,$compte);
        }
        else{
            $form = $this->createForm(CompteType::class,$compte);
        }
        $form->add('valider', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hashedPassword = $passwordHasher->hashPassword(
                $compte,
                $compte->getPassword()
            );
            $compte->setPassword($hashedPassword);
            $entityManager->persist($compte);
            $entityManager->flush();
            $this->addFlash('add', 'Le compte a été modifié');
            return $this->redirectToRoute('compte_index');
        }
        if($this->isGranted('ROLE_BENEVOLE')){
            return $this->render('benevole/form.html.twig',['form'=>$form->createView(),'id'=>$id,'title'=>'Modification']);
        }
        else{
            return $this->render('compte/form.html.twig',['form'=>$form->createView(),'id'=>$id,'title'=>'Modification']);
        }
    }

    #[Route(path: '/delete', name: '_delete')]
    public function deleteCompte(Request $request, EntityManagerInterface $entityManager){
        $id = $request->request->get('id');
        $compte = $entityManager->getRepository("App\Entity\CompteBenevole")->find($id);
        if($compte == null){
            $this->addFlash('error', 'Ce compte n\'existe pas');
            json_encode('error');
        }
        $entityManager->remove($compte);
        $entityManager->flush();
        $this->addFlash('error', 'Le compte a été supprimé');
        json_encode('success');
    }
}
