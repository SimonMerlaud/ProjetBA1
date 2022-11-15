<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Form\AssociationType;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/association', name: 'association')]

class AssociationController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(): Response
    {
        return $this->render('association/doodle.html.twig', [
            'controller_name' => 'AssociationController',
        ]);
    }

    #[Route('/add', name: '_add')]
    public function AddAction(EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request):Response
    {
        $asso = new Lieux();
        $adresse = new Adresse();
        $contact = new Contact();
        $asso->setTypeLieux("association");
        $contact->setBenevole(false);
        $contact->setProximite(false);
        $asso->setAdresse($adresse);
        $form = $this->createForm(AssociationType::class,$asso);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

        }
        $this->render('association/form.html.twig',['form'=>$form->createView()]);
    }

}
