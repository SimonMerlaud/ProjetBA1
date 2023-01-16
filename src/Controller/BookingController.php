<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

            return $this->redirectToRoute('booking_index', [], Response::HTTP_SEE_OTHER);
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

    #[Route(path: '/edit/{id}', name: '_edit')]
    public function edit(Request $request, int $id, BookingRepository $bookingRepository): Response
    {
        $booking = $bookingRepository->find($id);
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookingRepository->save($booking, true);

            return $this->redirectToRoute('booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);


    }

    #[Route(path: '/editAuto/{id}/{start}/{end}', name: '_edit_auto')]
    public function editAuto(Request $request, BookingRepository $bookingRepository, int $id, Date $start, Date $end)
    {
        dump($start);
        dump($end);
    }

    #[Route(path: '/delete/{id}', name: '_delete')]
    public function delete(Request $request, int $id, BookingRepository $bookingRepository): Response
    {
        $booking = $bookingRepository->find($id);
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $bookingRepository->remove($booking, true);
        }

        return $this->redirectToRoute('booking_index', [], Response::HTTP_SEE_OTHER);
    }
}
