<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Booking;
use App\Entity\Contact;
use App\Entity\Lieux;
use App\Entity\TypeLieux;
use App\Form\ContactAssoType;
use App\Form\LieuxType;
use App\Form\ModifyLieuxType;
use App\Form\SearchAssoType;
use Doctrine\Common\Util\ClassUtils;
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
        if ($page >$nbTotalPages && $page !=1){
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
            $this->addFlash('warning', "Le magasin a été supprimer");
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
        return $this->render('magasin/formContact.html.twig',['form'=>$form->createView(),'id'=>$id,'titre'=>'Ajouter un contact']);
    }

    #[Route('/modifyContact/{magId}/{id}', name: '_modifyContact',
        requirements:[ 'id'=>'\d+',
            'MagId'=>'\d+'
        ])]
    public function modifyContactAction(EntityManagerInterface $em,$id,$magId,\Symfony\Component\HttpFoundation\Request $request):Response
    {
        $contact = $em->getRepository(Contact::class)->find($id);


        if($contact== null){
            $this->addFlash('error', 'Le contact n\'existe pas');
            return $this->redirectToRoute('magasin_view',['id'=>$magId]);
        }
        $form = $this->createForm(ContactAssoType::class,$contact);
        $form->add('send',SubmitType::class,['label'=>'Modifier']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($contact);
            $em->flush();
            $this->addFlash('add', 'Le contact a été modifié');
            return $this->redirectToRoute('magasin_view',['id'=>$magId]);
        }
        return $this->render('magasin/formContact.html.twig',['form'=>$form->createView(),'id'=>$magId,'titre'=>'Modifier un contact']);
    }

    #[Route('/deleteContact/{magId}/{id}', name: '_deleteContact',
        requirements:[ 'id'=>'\d+',
            'magId'=>'\d+'
        ])]
    public function deleteContactAction(EntityManagerInterface $em,$id, $magId):Response
    {
        $mag = $em->getRepository(Lieux::class)->find($magId);
        if($mag == null){
            $this->addFlash('error', 'Le magasin n\'existe pas');
            return $this->redirectToRoute('magasin_index');
        }

        $cont = $em->getRepository(Contact::class)->find($id);
        if ($cont == null){
            $this->addFlash('error', 'Le contact n\'existe pas');
            return $this->redirectToRoute('magasin_view',['id'=>$magId]);
        }
        $mag->removeContact($cont);
        $em->persist($mag);
        $em->flush();
        $this->addFlash('warning', 'Le contact a été supprimer');
        return $this->redirectToRoute('magasin_view',['id'=>$magId]);
    }

    public function Pagination($currentPage, $nbPage):Response
    {
        return $this->render('magasin/pagination.html.twig',['nbPage'=>$nbPage,'currentPage'=>$currentPage]);
    }

    #[Route('/booking/{magId}', name: '_booking')]
    public function bookingMagasin($magId,EntityManagerInterface $entityManager):Response
    {
        $magasin = $entityManager->getRepository(Lieux::class)->find($magId);
        return $this->render('magasin/booking.html.twig',['magasin'=>$magasin]);
    }

    #[Route('/affectation/{idBenevsOutput}/{idBenevsInput}/{params}/', name: '_affectation')]
    public function affectation($idBenevsOutput,$idBenevsInput,EntityManagerInterface $entityManager,$params):Response
    {

        $parametres = explode(',', $params);
        $start = new \DateTime($parametres[0]);
        $end = new \DateTime($parametres[1]);
        $magId = $parametres[2];
        $magasin = $entityManager->getRepository(Lieux::class)->find($magId);
        $magasinCreneau = $entityManager->getRepository(Booking::class)->findOneBy(['lieux'=>$magasin,'beginAt'=>$start,'endAt'=>$end]);
        if($idBenevsOutput == "null" && $idBenevsInput == "null"){
            return $this->redirectToRoute('magasin_booking', ['magId' =>$magId]);
        }
        if(strstr( $idBenevsInput, ',' )) {
            $idBenevsInput = explode(',', $idBenevsInput);
            foreach ($idBenevsInput as $idBenevInput) {
                $this->AffectationInput($magasinCreneau, $magasin, $start, $end, $idBenevInput, $entityManager);
            }
        }elseif($idBenevsInput != "null"){
            $idBenevInput = $idBenevsInput;
            $this->AffectationInput($magasinCreneau, $magasin, $start, $end, $idBenevInput, $entityManager);
        }

        if(strstr( $idBenevsOutput, ',' )) {
            $idBenevsOutput = explode(',', $idBenevsOutput);
            foreach ($idBenevsOutput as $idBenevOutput) {
                dump($idBenevOutput);
                $this->AffectationOutput($magasinCreneau, $idBenevOutput, $entityManager);
            }
        }elseif($idBenevsOutput != "null"){
            $idBenevOutput = $idBenevsOutput;
            $this->AffectationOutput($magasinCreneau, $idBenevOutput, $entityManager);
        }
        return $this->redirectToRoute('booking_show', ['id' => $magasinCreneau->getId(), 'magId' => $magasin->getId()]);    }

    private function createBeginBooking($benevole, $booking, $start){
        $creneauStart = new Booking();
        $creneauStart->addContact($benevole);
        $creneauStart->setBeginAt($booking->getBeginAt());
        $creneauStart->setEndAt($start);
        $creneauStart->setMagasinId(0);
        $creneauStart->setTitle('Libre');
        $booking->setBeginAt($start);
        return $creneauStart;
    }

    private function createEndBooking($benevole, $booking, $end){
        $creneauEnd = new Booking();
        $creneauEnd->addContact($benevole);
        $creneauEnd->setBeginAt($end);
        $creneauEnd->setEndAt($booking->getEndAt());
        $creneauEnd->setMagasinId(0);
        $creneauEnd->setTitle('Libre');
        $booking->setEndAt($end);
        return $creneauEnd;
    }

    private function AffectationInput($magasinCreneau, $magasin, $start, $end, $idBenevInput, EntityManagerInterface $entityManager){
        $idBenevInput = explode('_', $idBenevInput);
        $benevole = $entityManager->getRepository(Contact::class)->find($idBenevInput[0]);
        $bookings=$entityManager->getRepository(Booking::class)->findWithId($idBenevInput[0],$idBenevInput[1]);
        foreach ($bookings as $booking) {
            if ($booking->getMagasinId() == 0) {
                if ($booking->getEndAt() > $end || $booking->getBeginAt() < $start) {
                    if ($booking->getEndAt() > $end) {
                        $creneauEnd = $this->createEndBooking($benevole, $booking, $end);
                    }
                    if ($booking->getBeginAt() < $start) {
                        $creneauStart = $this->createBeginBooking($benevole, $booking, $start);
                    }
                    $entityManager->persist($creneauStart);
                    $entityManager->persist($creneauEnd);
                }
                $booking->setTitle('Affecté à ' . ' ' . $magasin->getNom());
                $booking->setMagasinId($magasin->getId());
                if ($magasinCreneau->getNbPersonneNecessaire() - 1 < 0) {
                    $this->addFlash('error', 'Le nombre de personne nécessaire est dépasser');
                    return $this->redirectToRoute('booking_show', ['id' => $magasinCreneau->getId(), 'magId' => $magasin->getId()]);
                } else {
                    $magasinCreneau->setNbPersonneNecessaire($magasinCreneau->getNbPersonneNecessaire() - 1);
                }
                $magasinCreneau->addContact($benevole);
                $entityManager->persist($magasinCreneau);
                $entityManager->persist($booking);
                $entityManager->flush();
            }
        }
    }

    private function AffectationOutput($magasinCreneau, $idBenevOutput, $entityManager)
    {
        $idBenevOutput = explode('_', $idBenevOutput);
        $benevole = $entityManager->getRepository(Contact::class)->find($idBenevOutput[0]);
        $bookings=$entityManager->getRepository(Booking::class)->findWithId($idBenevOutput[0],$idBenevOutput[1]);
        foreach ($bookings as $booking) {
            if ($booking->getMagasinId() != 0) {
                $booking->setTitle('Libre');
                $booking->setMagasinId(0);
                $magasinCreneau->setNbPersonneNecessaire($magasinCreneau->getNbPersonneNecessaire() + 1);
                $magasinCreneau->removeContact($benevole);
                $entityManager->persist($magasinCreneau);
                $entityManager->persist($booking);
                $entityManager->flush();
            }
        }
    }
}
