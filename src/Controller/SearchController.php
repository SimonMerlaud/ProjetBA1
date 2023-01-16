<?php

namespace App\Controller;

use App\Entity\Lieux;
use App\Form\SearchAssoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function searchAsso(EntityManagerInterface $em, Request $request): Response
    {
        $searchForm = $this->createForm(SearchAssoType::class);
        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()){
            $criteria = $searchForm->getData();
            dump($criteria);
            $produits = $em->getRepository(Lieux::class)->findWithKeyWord("association", $criteria);
            dump($produits);
            return $this->render('association/list.html.twig',['produits'=>$produits]);
        }else{
            return $this->render("search/searchBarAsso.html.twig",['form' => $searchForm->createView()]);
        }
    }

    public function searchMag(EntityManagerInterface $em, Request $request): Response
    {
        $searchForm = $this->createForm(SearchAssoType::class);
        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()){
            $criteria = $searchForm->getData();
            $produits = $em->getRepository(Lieux::class)->findWithKeyWord("magasin", $criteria);
            return $this->render('association/list.html.twig',['produits'=>$produits]);
        }else{
            return $this->render("search/searchBarAsso.html.twig",['form' => $searchForm->createView()]);
        }
    }

}
