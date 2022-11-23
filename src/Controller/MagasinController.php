<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use App\Form\LieuxType;
use App\Form\SearchAssoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/magasin', name: 'magasin')]
class MagasinController extends AbstractController
{
    #[Route('/{page}', name: '_index',
        requirements: ['page' => '\d+' ],
        defaults:[ 'page' => 1] )]
    public function index(EntityManagerInterface $em,Request $request,$page): Response
    {
        if($page<1)
        {
            throw new NotFoundHttpException("La page $page n'existe pas");
        }
        $magasins = $em->getRepository("App\Entity\Lieux")->myFindAllWithPaging("magasin", $page);
        $nbTotalPages = intval(ceil(count($magasins) / 20) ) ;
        if ($page >$nbTotalPages){
            throw new NotFoundHttpException("La page n'existe pas");
        }

        $searchForm = $this->createForm(SearchAssoType::class);
        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()){
            $criteria = $searchForm->getData();
            $magasins = $em->getRepository(Lieux::class)->findWithKeyWord("magasin", $criteria);
            return $this->render('magasin/index.html.twig',["magasin"=>$magasins,"currentPage"=>$page,"nbPage"=>$nbTotalPages]);
        }else {
            return $this->render('magasin/index.html.twig', ["magasin" => $magasins, "currentPage" => $page, "nbPage" => $nbTotalPages]);
        }
    }

    #[Route('/add', name: '_add')]
    public function addMagasin(Request $request, EntityManagerInterface $em): Response
    {
        $lieux = new Lieux();
        $contact = new Contact();
        $lieux->addContact($contact);
        $typeLieux = $em->getRepository(TypeLieux::class)->findOneBy(array('libelle'=>"magasin"));
        if($typeLieux != null)
        {
            $lieux->setTypeLieux($typeLieux);
            $typeLieux->addLieux($lieux);
        }else{
            $typeLieux = new TypeLieux();
            $typeLieux->setLibelle("magasin");
            $lieux->setTypeLieux($typeLieux);
            $typeLieux->addLieux($lieux);
            $em->persist($typeLieux);
        }

        $form = $this->createForm(LieuxType::class,$lieux);
        $form->add('send',SubmitType::class,['label'=>'Ajouter']);
        $form->clearErrors();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $codePos = $data->getAdresse()->getcodePostale();
            $ville = $data->getAdresse()->getVille();
            $rue = $data->getAdresse()->getRue();
            $nbRue = $data->getAdresse()->getNumeroRue();
            $nbAppart = $data->getAdresse()->getNumeroAppart();
            $args = array('contacts'=>$data->getContacts());


            $contact = $em->getRepository(Contact::class)->findBy(array('nom'=>$args['contacts'][0]->getNom(),'prenom'=>$args['contacts'][0]->getPrenom(),'mail'=>$args['contacts'][0]->getMail()));
            if($contact == null){
                $contact = $data->getContacts()[0];
                $contact->setBenevole(false);
                $contact->setProximite(false);
            }
            else {
                $contact = $contact[0];
                $lieux->removeContact($args['contacts'][0]);
            }

            $adresse = $em->getRepository(Adresse::class)->findBy(array('codePostale'=> $codePos,'ville'=>$ville,'rue'=>$rue, 'numeroRue'=> $nbRue, 'numeroAppart'=>$nbAppart));
            if($adresse == null){
                $adresse = new Adresse();
                $adresse->setCodePostale($codePos)
                    ->setNumeroAppart($nbAppart)
                    ->setNumeroRue($nbRue)
                    ->setRue($rue)
                    ->setVille($ville);
            }else{
                $adresse = $adresse[0];
            }

            $lieux->setAdresse($adresse);
            $lieux->addContact($contact);
            $adresse->addContact($contact);
            $adresse->addLieux($lieux);
            $contact->addLieux($lieux);
            $em->persist($adresse);
            $em->persist($contact);
            $em->persist($lieux);
            $em->flush();
            $this->addFlash('add', "le magasin a été ajouté");

            return $this->redirectToRoute("accueil");
        }
        return $this->render('magasin/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/list', name: '_list')]
    public function listMagasin(Request $request, EntityManagerInterface $em): Response
    {

        return $this->render('magasin/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}