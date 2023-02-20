<?php

namespace App\Controller;

use App\Entity\MainStart;
use App\Form\StartType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/{restart}', name: 'accueil',
        defaults: [ 'restart' => false]
    )]
    public function index(Request $request, EntityManagerInterface $em, $restart): Response
    {
        if ($this->getUser()){
            if($this->isGranted('ROLE_BA')){
                $startCollecte = $em->getRepository(MainStart::class)->find(1);
                if ($startCollecte != null && $restart){
                    $startCollecte->setBeginAt(null);
                    $startCollecte->setEndAt(null);
                    $em->persist($startCollecte);
                    $em->flush();
                    $form = $this->createForm(StartType::class, $startCollecte);
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $em->persist($startCollecte);
                        $em->flush();
                        return $this->redirectToRoute('accueil');
                    }
                    return $this->render('benevole/index.html.twig', ['formStart' => $form->createView(), 'disabled' => false]);
                }
                else if ($startCollecte != null && $restart == false){
                    $form = $this->createForm(StartType::class, $startCollecte);
                    $form->handleRequest($request);
                    return $this->render('benevole/index.html.twig', ['formStart' => $form->createView(), 'disabled' => true]);
                }
                else{

                    $startCollecte = new MainStart();
                    $form = $this->createForm(StartType::class, $startCollecte);
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $em->persist($startCollecte);
                        $em->flush();
                        return $this->redirectToRoute('accueil');
                    }
                    return $this->render('benevole/index.html.twig', ['formStart' => $form->createView(), 'disabled' => false]);
                }
            }
            return $this->render('benevole/index.html.twig', ['formStart' => null, 'disabled' => false]);
        }
        else {
            return $this->redirectToRoute('compte_login');
        }
    }
}
