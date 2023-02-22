<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Lieux;
use App\Entity\MainStart;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route(path: '/booking', name: 'booking')]
class BookingController extends AbstractController
{
    #[Route(path: '/', name: '_index')]
    public function index(BookingRepository $bookingRepository, EntityManagerInterface $em): Response
    {
        $dateCollecte =$em->getRepository(MainStart::class)->find(1);
        $startCollecte = $dateCollecte->getBeginAt();
        $endCollecte = $dateCollecte->getEndAt();
        dump($startCollecte);
        return $this->render('booking/calendar.html.twig',['magasinId'=>0, 'startCollecte' => $startCollecte, 'endCollecte' => $endCollecte]);
    }

    #[Route(path: '/new/{magId}', name: '_new',
    defaults:[ 'magId' => 0]
    )]
    public function new(Request $request, BookingRepository $bookingRepository,EntityManagerInterface $entityManager, $magId): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($this->isGranted('ROLE_BENEVOLE')){
                $booking->addContact($this->getUser()->getContact());
                $booking->setMagasinId(0);
            }else{
                $booking->setLieux($entityManager->getRepository(Lieux::class)->find($magId));
                $booking->setMagasinId(0);
            }
            $booking->setTitle('Libre');
            $bookingRepository->save($booking, true);
            if($this->isGranted('ROLE_BENEVOLE')){
                return $this->redirectToRoute('accueil');
            }
            else{
                return $this->redirectToRoute('magasin_booking', ['magId' => $magId]);
            }
        }
        $dateCollecte = $entityManager->getRepository(MainStart::class)->find(1);
        if ($dateCollecte->getBeginAt() != null){
            $startCollecte = $dateCollecte->getBeginAt()->format('Y-m-d');
            $endCollecte = $dateCollecte->getEndAt()->format('Y-m-d');
            return $this->renderForm('booking/new.html.twig', [
                'booking' => $booking,
                'form' => $form,
                'magId' => $magId,
                'startCollecte' => $startCollecte,
                'endCollecte' => $endCollecte
            ]);
        }
        else{
            return $this->renderForm('booking/new.html.twig', [
                'booking' => $booking,
                'form' => $form,
                'magId' => $magId,
                'startCollecte' => null,
                'endCollecte' => null
            ]);
        }
    }

    #[Route(path: '/show/{id}/{magId}', name: '_show')]
    public function show(int $id,$magId, EntityManagerInterface $entityManager): Response
    {

        $booking = $entityManager->getRepository(Booking::class)->find($id);
        $bookingsOutput = $entityManager->getRepository(Booking::class)->findBetweenDates($booking->getBeginAt(),$booking->getEndAt());
        $bookingsInput = $entityManager->getRepository(Booking::class)->findBetweenDates($booking->getBeginAt(),$booking->getEndAt(), $magId);
        if($this->isGranted('ROLE_BA'))
            $magasin = $entityManager->getRepository(Lieux::class)->find($magId);
        else
            $magasin = $entityManager->getRepository(Lieux::class)->find($booking->getMagasinId());
        return $this->render('booking/show.html.twig', [
            'magId' => $magId,
            'booking' => $booking,
            'magasin'=>$magasin,
            'bookingsInput' => $bookingsInput,
            'bookingsOutput' =>$bookingsOutput,
            'startDate' => $booking->getBeginAt(),
            'endDate' =>$booking->getEndAt()
        ]);
    }

    #[Route(path: '/editAuto', name: '_edit_auto')]
    public function editAuto(Request $request, BookingRepository $bookingRepository): Response
    {
        $id = $request->request->get('id');
        $start = $request->request->get('start');
        $end = $request->request->get('end');
        $benevole = $request->request->get('benevole');
        $start = str_replace('T',' ', $start);
        $end = str_replace('T',' ', $end);

        $start = new \DateTime($start);
        $end = new \DateTime($end);
        $booking = $bookingRepository->find($id);
        if($benevole && $booking->getMagasinId() != 0){
            return new Response(json_encode('Creneaux non modifiable car il est attribuer a un magasin'));
        }else{
            $booking->setBeginAt($start);
            $booking->setEndAt($end);
            $bookingRepository->save($booking, true);
            return new Response(json_encode(''));
        }


    }

    #[Route(path: '/delete/{id}/{magId}', name: '_delete',
        defaults:[ 'magId' => 0])]
    public function delete($magId, Request $request, int $id, EntityManagerInterface $em): Response
    {
        $booking = $em->getRepository(Booking::class)->find($id);
        $contacts = $booking->getContacts();
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            foreach ($contacts as $contact){
                $booking->removeContact($contact);
            }
            $em->persist($booking);
            $em->flush();
            $em->remove($booking);
            $em->flush();
        }
        if($this->isGranted('ROLE_BENEVOLE')){
            return $this->redirectToRoute('accueil');
        }
        else{
            return $this->redirectToRoute('magasin_booking', ['magId' => $magId]);
        }

    }

    #[Route(path: '/export/{magId}', name: '_export')]
    public function export($magId, EntityManagerInterface $em): Response
    {
        if($magId == '0'){
            $bookings = $this->getUser()->getContact()->getBookings();
        }else{
            $magasin = $em->getRepository(Lieux::class)->find($magId);
            $bookings = $em->getRepository(Booking::class)->findBy(['lieux' => $magasin]);
        }
        $bookingsExport = array();

        foreach ($bookings as $booking){
            $bookingsExport[] = ['Date de début' => $booking->getBeginAt(), 'Date de fin' => $booking->getEndAt(), 'Titre' => $booking->getTitle()];
        }

        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer());

        $serializer = new Serializer($normalizer, [new CsvEncoder()]);// Les 2 lignes pour avoir la date dans le bon format
        $context = [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'];

        $serializedBooking = $serializer->serialize($bookingsExport,'csv', $context);

        $response = new Response($serializedBooking);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=export.csv');
        return $response;
    }

}
