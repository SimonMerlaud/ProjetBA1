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
        defaults: [ 'restart' => null]
    )]
    public function index(Request $request, EntityManagerInterface $em, $restart): Response
    {
        if ($this->getUser()){
            $dateCollecte = $em->getRepository(MainStart::class)->find(1);
            if ($this->isGranted('ROLE_BA')){
                if($dateCollecte){
                    if($restart){
                        $dateCollecte->setBeginAt(null);
                        $dateCollecte->setEndAt(null);
                        $em->persist($dateCollecte);
                        $em->flush();
                        return $this->redirectToRoute('accueil');
                    }
                    if($dateCollecte->getBeginAt()){
                        $form = $this->createForm(StartType::class, $dateCollecte);
                        $form->handleRequest($request);
                        return $this->render('benevole/index.html.twig', ['formStart' => $form->createView(), 'disabled' => true, 'startCollecte' => null, 'endCollecte' => null]);
                    }
                    else{
                        $form = $this->createForm(StartType::class, $dateCollecte);
                        $form->handleRequest($request);
                        if ($form->isSubmitted() && $form->isValid()) {
                            $dateCollecte->setEndAt($dateCollecte->getEndAt()->modify('+1 day'));
                            $em->persist($dateCollecte);
                            $em->flush();
                            return $this->redirectToRoute('accueil');
                        }
                        return $this->render('benevole/index.html.twig', ['formStart' => $form->createView(), 'disabled' => false, 'startCollecte' => null, 'endCollecte' => null]);
                    }
                }
                else{
                    $dateCollecte = new MainStart();
                    $form = $this->createForm(StartType::class, $dateCollecte);
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $dateCollecte->setEndAt($dateCollecte->getEndAt()->modify('+1 day'));
                        $em->persist($dateCollecte);
                        $em->flush();
                        return $this->redirectToRoute('accueil');
                    }
                    return $this->render('benevole/index.html.twig', ['formStart' => $form->createView(), 'disabled' => false, 'startCollecte' => null, 'endCollecte' => null]);
                }
            }
            else{
                if($dateCollecte){
                    if($dateCollecte->getBeginAt()){
                        $startCollecte = $dateCollecte->getBeginAt()->format('Y-m-d');
                        $endCollecte = $dateCollecte->getEndAt()->format('Y-m-d');
                        return $this->render('benevole/index.html.twig', ['formStart' => null, 'disabled' => false, 'startCollecte' => $startCollecte, 'endCollecte' => $endCollecte]);
                    }
                    else{
                        return $this->render('benevole/index.html.twig', ['formStart' => null, 'disabled' => false, 'dateCollecte' => false, 'startCollecte' => null, 'endCollecte' => null]);
                    }
                }
                else{
                    return $this->render('benevole/index.html.twig', ['formStart' => null, 'disabled' => false, 'dateCollecte' => false, 'startCollecte' => null, 'endCollecte' => null]);
                }
            }
        }
        else {
            return $this->redirectToRoute('compte_login');
        }
    }
}
