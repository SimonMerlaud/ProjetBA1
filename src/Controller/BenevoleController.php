<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Form\BenevoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/benevole', name: 'benevole_')]
class BenevoleController extends AbstractController
{

    #[Route('', name: '')]
    public function index(): Response{
        return $this->render('benevole/doodle.html.twig', [
            'controller_name' => 'BenevoleController',
        ]);
    }

    #[Route('/inscription', name: 'inscription_')]
    public function inscriptionAction(EntityManagerInterface $em, Request $request): Response{

        $benevole = new Contact();
        $adresse = new Adresse();
        $benevole->setAdresse($adresse);

        $form = $this->createForm(BenevoleType::class, $benevole);
        $form->add('send', SubmitType::class, ['label' => 'Valider']);
        $form->handleRequest($request);
        $benevole->setBenevole(true);


        if($form->isSubmitted() && $form->isValid()){
            //mettre le formulaire
            dump($benevole);
            $em->persist($benevole);
            $em->persist($adresse);
            $em->flush();

            //faire un render sur une page pour dire que
            //le user est bien inscrit

            return $this->render('benevole/doodle.html.twig', [
                'controller_name' => 'Bien inscrit',
            ]);

        }

        return $this->render('benevole/form.html.twig', ['form' => $form->createView()]);


    }


}