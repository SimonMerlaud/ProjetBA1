<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Lieux;
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
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/calendar.html.twig',['magasinId'=>0]);
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

        return $this->renderForm('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
            'magId' => $magId
        ]);
    }

    #[Route(path: '/show/{id}/{magId}', name: '_show')]
    public function show(int $id,$magId, BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $bookingRepository->find($id),'magId'=>$magId
        ]);
    }

    #[Route(path: '/editAuto', name: '_edit_auto')]
    public function editAuto(Request $request, BookingRepository $bookingRepository): Response
    {
        $id = $request->request->get('id');
        $start = $request->request->get('start');
        $end = $request->request->get('end');
        $start = str_replace('T',' ', $start);
        $end = str_replace('T',' ', $end);

        $start = new \DateTime($start);
        $end = new \DateTime($end);
        $booking = $bookingRepository->find($id);
        $booking->setBeginAt($start);
        $booking->setEndAt($end);
        $bookingRepository->save($booking, true);

        return new Response(json_encode(''));
    }

    #[Route(path: '/delete/{id}/{magId}', name: '_delete',
        defaults:[ 'magId' => 0])]
    public function delete($magId, Request $request, int $id, BookingRepository $bookingRepository): Response
    {
        $booking = $bookingRepository->find($id);
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $bookingRepository->remove($booking, true);
        }
        if($this->isGranted('ROLE_BENEVOLE')){
            return $this->redirectToRoute('accueil');
        }
        else{
            return $this->redirectToRoute('magasin_booking', ['magId' => $magId]);
        }

    }

    #[Route(path: '/export/{mag}', name: '_export')]
    public function export($mag): Response
    {

        if($this->isGranted('ROLE_BENEVOLE')){
            $bookings = $this->getUser()->getContact()->getBookings();
        }else{
            $bookings = $mag->getBookings();
        }
        $bookingsExport = array();

        foreach ($bookings as $booking){
            if($this->isGranted('ROLE_BENEVOLE')){
                $bookingsExport[] = ['Date de début' => $booking->getBeginAt(), 'Date de fin' => $booking->getEndAt(),'Titre' => $booking->getTitle()];
            }else {
                $bookingsExport[] = ['Date de début' => $booking->getBeginAt(), 'Date de fin' => $booking->getEndAt(), 'Titre' => $booking->getTitle()];
            }
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
