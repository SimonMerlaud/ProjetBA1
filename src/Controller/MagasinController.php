<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use App\Form\ContactAssoType;
use App\Form\LieuxType;
use App\Form\ModifyLieuxType;
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
            return $this->render('magasin/index.html.twig',["magasins"=>$magasins,"currentPage"=>$page,"nbPage"=>$nbTotalPages]);
        }else {
            return $this->render('magasin/index.html.twig', ["magasins" => $magasins, "currentPage" => $page, "nbPage" => $nbTotalPages]);
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

            return $this->redirectToRoute("magasin_index");
        }
        return $this->render('magasin/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/visualisation/{id}', name: '_view',
        requirements:[ 'id'=>'\d+'])]
    public function viewAction(EntityManagerInterface $em,$id):Response
    {
        $magasin = $em->getRepository(Lieux::class)->find($id);
        if($magasin == null){
            $this->addFlash('error', 'Ce magasin n\'existe pas');
            return $this->redirectToRoute('magasin_index');
        }
        return $this->render('magasin/view.html.twig',['magasin'=>$magasin]);
    }

    #[Route('/modifier/{id}', name: '_edit',
        requirements:[ 'id'=>'\d+'])]
    public function editAction(EntityManagerInterface $em,$id, \Symfony\Component\HttpFoundation\Request $request):Response
    {
        $mag = $em->getRepository(Lieux::class)->find($id);
        if($mag == null){
            $this->addFlash('error', 'Ce magasin n\'existe pas');
            return $this->redirectToRoute('magasin_index');
        }
        $form = $this->createForm(ModifyLieuxType::class,$mag);
        $form->add('send',SubmitType::class,['label'=>'Modifier']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($mag);
            $em->flush();
            $this->addFlash('add', 'Le magasin a été modifié');
            return $this->redirectToRoute('magasin_index');
        }
        return $this->render('magasin/formModifyLieux.html.twig',['form'=>$form->createView(), 'id'=>$id]);
    }

    #[Route('/delete/{id}', name: '_delete',
        requirements:[ 'id'=>'\d+'])]
    public function deleteAction(EntityManagerInterface $em,$id):Response
    {
        $mag = $em->getRepository(Lieux::class)->find($id);
        if ($mag == NULL){
            $this->addFlash('error', "Le magasin n'existe pas");
            return $this->redirectToRoute('magasin_index');
        }else {
            foreach ($mag->getContacts() as $contact){
                $em->getRepository(Contact::class)->remove($contact);
            }
            $em->getRepository(Lieux::class)->remove($mag);
            $em->flush();
            $this->addFlash('add', "Le magasin a été supprimer");
            return $this->redirectToRoute('magasin_index');
        }
    }

    #[Route('/addContact/{id}', name: '_addContact',
        requirements:[ 'id'=>'\d+'])]
    public function AddContactAction(EntityManagerInterface $em,$id, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactAssoType::class,$contact);
        $form->add('send',SubmitType::class,['label'=>'Ajouter']);
        $form->handleRequest($request);
        $mag = $em->getRepository(Lieux::class)->find($id);
        if($mag == null){
            $this->addFlash('error', 'Le magasin n\'existe pas');
            return $this->redirectToRoute('magasin_index');
        }else {
            if ($form->isSubmitted() && $form->isValid()) {

                $data = $form->getData();
                $cont = $em->getRepository(Contact::class)->findOneBy(array('nom' => $data->getNom(), 'prenom' => $data->getPrenom(), 'mail' => $data->getMail()));
                if ($cont != null) {
                    $mag->addContact($cont);
                } else {
                    $contact = $data;
                    $contact->setBenevole(false);
                    $contact->setProximite(false);
                    $mag->addContact($contact);
                    $em->persist($contact);
                }
                $em->persist($mag);
                $em->flush();
                $this->addFlash('add', 'Le contact a été ajouté au magasin');
                return $this->redirectToRoute('magasin_view', array('id' => $id));
            }
        }
        return $this->render('magasin/formContact.html.twig',['form'=>$form->createView()]);
    }

    public function Pagination($currentPage, $nbPage):Response
    {
        return $this->render('magasin/pagination.html.twig',['nbPage'=>$nbPage,'currentPage'=>$currentPage]);
    }

    #[Route('/modifyContact/{AssoId}/{id}', name: '_modifyContact',
        requirements:[ 'id'=>'\d+',
            'AssoId'=>'\d+'
        ])]
    public function modifyContactAction(EntityManagerInterface $em,$id,$MagId,\Symfony\Component\HttpFoundation\Request $request):Response
    {
        $contact = $em->getRepository(Contact::class)->find($id);


        if($contact== null){
            $this->addFlash('error', 'Le contact n\'existe pas');
            return $this->redirectToRoute('magasin_view',['id'=>$MagId]);
        }
        $form = $this->createForm(ContactAssoType::class,$contact);
        $form->add('send',SubmitType::class,['label'=>'Modifier']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            dump($form);
            $em->persist($contact);
            $em->flush();
            $this->addFlash('add', 'Le contact a été modifié');
            return $this->redirectToRoute('association_view',['id'=>$AssoId]);
        }


        return $this->render('association/formContact.html.twig',['form'=>$form->createView(),'id'=>$AssoId,'titre'=>'Modifier un contact']);

    }

    #[Route('/viewContacts/{id}', name: '_viewContacts',
        requirements:[ 'id'=>'\d+'])]
    public function viewAllContactAction(EntityManagerInterface $em,$id):Response
    {
        $asso = $em->getRepository(Lieux::class)->find($id);
        if($asso == null){
            $this->addFlash('error', 'L\'association n\'existe pas');
            return $this->redirectToRoute('association_index');
        }
        return $this->render('association/viewContacts.html.twig',['association'=>$asso]);
    }

    public function Pagination($currentPage, $nbPage):Response
    {
        return $this->render('association/pagination.html.twig',['nbPage'=>$nbPage,'currentPage'=>$currentPage]);
    }
}
