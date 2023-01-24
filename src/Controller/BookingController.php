<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Lieux;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

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
                $booking->setContact($this->getUser()->getContact());
            }else{
                $booking->setLieux($entityManager->getRepository(Lieux::class)->find($magId));
            }
            $booking->setTitle('tmp');
            $bookingRepository->save($booking, true);
            return $this->redirectToRoute('accueil');
        }

        return $this->renderForm('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form
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

    #[Route(path: '/delete/{id}', name: '_delete')]
    public function delete(Request $request, int $id, BookingRepository $bookingRepository): Response
    {
        $booking = $bookingRepository->find($id);
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $bookingRepository->remove($booking, true);
        }

        return $this->redirectToRoute('booking_index');
    }
}
