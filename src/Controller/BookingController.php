<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\Common\Util\ClassUtils;
use Proxies\__CG__\App\Entity\Contact;
use Proxies\__CG__\App\Entity\Lieux;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Date;

#[Route(path: '/booking', name: 'booking')]
class BookingController extends AbstractController
{
    #[Route(path: '/', name: '_index')]
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/calendar.html.twig');
    }

    #[Route(path: '/new', name: '_new')]
    public function new(Request $request, BookingRepository $bookingRepository): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setTitle('Tmp');
            $bookingRepository->save($booking, true);
            return $this->redirectToRoute('booking_index');
        }

        return $this->renderForm('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route(path: '/show/{id}', name: '_show')]
    public function show(int $id, BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $bookingRepository->find($id),
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

    #[Route(path: '/delete/{id}', name: '_delete')]
    public function delete(Request $request, int $id, BookingRepository $bookingRepository): Response
    {
        $booking = $bookingRepository->find($id);
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $bookingRepository->remove($booking, true);
        }

        return $this->redirectToRoute('booking_index');
    }

    #[Route(path: '/export', name: '_export')]
    public function export(BookingRepository $bookingRepository): Response
    {
        $bookings = $bookingRepository->findAll();
        $bookingsExport = array();

        foreach ($bookings as $booking){
            $bookingsExport[] = ['Date de début' => $booking->getBeginAt(), 'Date de fin' => $booking->getEndAt(),'Titre' => $booking->getTitle(), 'Mail' => $booking->getMail(), 'Adresse' => $booking->getAdresse()];
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
