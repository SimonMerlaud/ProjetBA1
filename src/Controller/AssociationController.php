<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use App\Form\AssociationType;
use App\Form\ContactAssoType;
use App\Form\ModifyLieuxType;
use App\Form\SearchAssoType;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/association', name: 'association')]

class AssociationController extends AbstractController
{
    #[Route('/{page}', name: '_index',
    requirements: ['page' => '\d+' ],
    defaults:[ 'page' => 1] )]
    public function index(EntityManagerInterface $em,\Symfony\Component\HttpFoundation\Request $request,$page): Response
    {
        if($page<1)
        {
        throw new NotFoundHttpException("La page $page n'existe pas");
        }
        $associations = $em->getRepository("App\Entity\Lieux")->myFindAllWithPaging('association',$page);
        $nbTotalPages = intval(ceil(count($associations) / 20) ) ;
        if ($page >$nbTotalPages){
            throw new NotFoundHttpException("La page n'existe pas");
        }

        $searchForm = $this->createForm(SearchAssoType::class);
        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()){
            $criteria = $searchForm->getData();
            $associations = $em->getRepository(Lieux::class)->findWithKeyWord('association',$criteria);
            return $this->render('association/index.html.twig',["associations"=>$associations,"currentPage"=>$page,"nbPage"=>$nbTotalPages]);
        }else {
            return $this->render('association/index.html.twig', ["associations" => $associations, "currentPage" => $page, "nbPage" => $nbTotalPages]);
        }
    }

    #[Route('/add', name: '_add')]
    public function AddAction(EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request):Response
    {
        $asso = new Lieux();
        $contact = new Contact();
        $asso->addContact($contact);
        $typeLieux = $em->getRepository(TypeLieux::class)->findOneBy(array('libelle'=>"association"));
        if($typeLieux != null)
        {
            $asso->setTypeLieux($typeLieux);
        }else{
            $typeLieux = new TypeLieux();
            $typeLieux->setLibelle("association");
            $asso->setTypeLieux($typeLieux);
            $em->persist($typeLieux);
        }
        $form = $this->createForm(AssociationType::class,$asso);
        $form->add('send',SubmitType::class,['label'=>'Ajouter']);
        $form->clearErrors();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $codePos = $data->getAdresse()->getcodePostale();
            $ville = $data->getAdresse()->getVille();
            $rue = $data->getAdresse()->getRue();
            $nbRue = $data->getAdresse()->getNumeroRue();
            $nbAppart = $data->getAdresse()->getNumeroAppart();

            $args = array('contacts'=>$data->getContacts());
            $cont = $em->getRepository(Contact::class)->findBy(array('nom'=>$args['contacts'][0]->getNom(),'prenom'=>$args['contacts'][0]->getPrenom(),'mail'=>$args['contacts'][0]->getMail()));

            if($cont == null){
                $contact = $data->getContacts()[0];
                $contact->setBenevole(false);
                $contact->setProximite(false);
                $asso->addContact($contact);
            }else
            {
                $this->addFlash('error', 'Ce contact existe déjà');
                return $this->redirectToRoute('association_index');
            }

            $var = ($em->getRepository(Adresse::class)->findAllWithParameter($codePos,$ville,$rue,$nbRue,$nbAppart));
            if(count($var) >=1){
                $name = ($em->getRepository(Lieux::class)->findBy(array('nom'=>$data->getNom())));
                if(count($name)>=1){
                    $this->addFlash('error', 'Cette association existe déjà');
                    return $this->redirectToRoute('association_index');
                }
                $asso->setAdresse($var[0]);
            }else{
                $asso->setAdresse($data->getAdresse());
                $em->persist($data->getAdresse());
            }
            $em->persist($asso);
            $em->persist($contact);
            $em->flush();
            $this->addFlash('add', 'L\'association a été ajouté');
            return $this->redirectToRoute('association_index');
        }
        return $this->render('association/form.html.twig',['form'=>$form->createView()]);
    }

    #[Route('/visualisation/{id}', name: '_view',
        requirements:[ 'id'=>'\d+'])]
    public function viewAction(EntityManagerInterface $em,$id):Response
    {
        $asso = $em->getRepository(Lieux::class)->find($id);
        if($asso == null){
            $this->addFlash('error', 'Cette association n\'existe pas');
            return $this->redirectToRoute('association_index');
        }
        return $this->render('association/view.html.twig',['association'=>$asso]);
    }

    #[Route('/delete/{id}', name: '_delete',
        requirements:[ 'id'=>'\d+'])]
    public function deleteAction(EntityManagerInterface $em,$id):Response
    {

        $asso = $em->getRepository(Lieux::class)->find($id);
        if ($asso == NULL){
            $this->addFlash('error', "L'association n'existe pas");
            return $this->redirectToRoute('association_index');
        }else {
            foreach ($asso->getContacts() as $contact){
                $em->getRepository(Contact::class)->remove($contact);
            }
            $em->getRepository(Lieux::class)->remove($asso);
            $em->flush();
            $this->addFlash('add', "L'association a été supprimer");
            return $this->redirectToRoute('association_index');
        }
    }

    #[Route('/modifier/{id}', name: '_edit',
        requirements:[ 'id'=>'\d+'])]
    public function editAction(EntityManagerInterface $em,$id, \Symfony\Component\HttpFoundation\Request $request):Response
    {
        $asso = $em->getRepository(Lieux::class)->find($id);
        if($asso == null){
            $this->addFlash('error', 'Cette association n\'existe pas');
            return $this->redirectToRoute('association_index');
        }
        $form = $this->createForm(ModifyLieuxType::class,$asso);
        $form->add('send',SubmitType::class,['label'=>'Modifier']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($asso);
            $em->flush();
            $this->addFlash('add', 'L\'association a été modifié');
            return $this->redirectToRoute('association_view',['id'=>$id]);
        }
        return $this->render('association/modifyForm.html.twig',['form'=>$form->createView(),'id'=>$id]);
    }

    #[Route('/addContact/{id}', name: '_addContact',
        requirements:[ 'id'=>'\d+'])]
    public function AddContactAction(EntityManagerInterface $em,$id, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactAssoType::class,$contact);
        $form->add('send',SubmitType::class,['label'=>'Ajouter']);
        $form->handleRequest($request);
        $asso = $em->getRepository(Lieux::class)->find($id);
        if($asso == null){
            $this->addFlash('error', 'L\'association n\'existe pas');
            return $this->redirectToRoute('association_index');
        }else {

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                dump($data);
                $cont = $em->getRepository(Contact::class)->findBy(array('nom' => $data->getNom(), 'prenom' => $data->getPrenom(), 'mail' => $data->getMail()));
                if ($cont != null) {
                    $this->addFlash('error', 'Le contact existe déjà');
                    return $this->redirectToRoute('association_index');
                } else {
                    $contact = $data;
                    $contact->setBenevole(false);
                    $contact->setProximite(false);
                    $asso->addContact($contact);
                    $em->persist($asso);
                    $em->persist($contact);
                    $em->flush();
                    $this->addFlash('add', 'Le contact a été ajouté');
                    return $this->redirectToRoute('association_view',['id'=>$id]);
                }
            }
        }
        return $this->render('association/formContact.html.twig',['form'=>$form->createView(),'id'=>$id,'titre'=>'Ajouter un contact']);
    }

    #[Route('/modifyContact/{AssoId}/{id}', name: '_modifyContact',
        requirements:[ 'id'=>'\d+',
                'AssoId'=>'\d+'
            ])]
    public function modifyContactAction(EntityManagerInterface $em,$id,$AssoId,\Symfony\Component\HttpFoundation\Request $request):Response
    {
        $contact = $em->getRepository(Contact::class)->find($id);


        if($contact== null){
            $this->addFlash('error', 'Le contact n\'existe pas');
            return $this->redirectToRoute('association_view',['id'=>$AssoId]);
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
