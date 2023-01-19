<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        if ($this->getUser()){
            return $this->render('benevole/index.html.twig');
        }
        else {
            return $this->redirectToRoute('compte_login');
        }
    }
}
