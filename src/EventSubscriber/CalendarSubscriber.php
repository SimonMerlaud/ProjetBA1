<?php

namespace App\EventSubscriber;

use App\Repository\BookingRepository;
use App\Repository\ContactRepository;
use App\Repository\LieuxRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private $bookingRepository;
    private $router;
    private $contactRepository;

    public function __construct(
        BookingRepository $bookingRepository,
        UrlGeneratorInterface $router,
        ContactRepository $contactRepository,
        LieuxRepository $lieuxRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->router = $router;
        $this->contactRepository = $contactRepository;
        $this->LieuxRepository = $lieuxRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();
        $contact = $this->contactRepository->find($filters['contact_id']);
        $magasin = $this->LieuxRepository->find($filters['magasin_id']);
        // Modify the query to fit to your entity and needs
        // Change booking.beginAt by your start date property
        if($magasin == null) {
            $bookings = $this->bookingRepository
                ->createQueryBuilder('booking')
                ->where('booking.beginAt BETWEEN :start and :end OR booking.endAt BETWEEN :start and :end')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->Join('booking.contacts','contacts')
                ->andWhere('contacts = :contact')
                ->setParameter('contact', $contact)
                ->getQuery()
                ->getResult();
        }else{
            $bookings = $this->bookingRepository
                ->createQueryBuilder('booking')
                ->where('booking.beginAt BETWEEN :start and :end OR booking.endAt BETWEEN :start and :end')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->andWhere('booking.lieux = :magasin')
                ->setParameter('magasin', $magasin)
                ->getQuery()
                ->getResult();
        }
        foreach ($bookings as $booking) {
            // this create the events with your data (here booking data) to fill calendar
            $bookingEvent = new Event(
                $booking->getTitle(), //Fallait mettre un titre vide car Event a besoin d'un titre mais booking n'en n'a pas
                $booking->getBeginAt(),
                $booking->getEndAt() // If the end date is null or not defined, a all day event is created.
            );

            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
             */

            $bookingEvent->setOptions([
                'backgroundColor' => 'red',
                'borderColor' => 'red',
            ]);
            if($magasin != null) {
                $bookingEvent->addOption(
                    'url',

                    $this->router->generate('booking_show', [
                            'id' => $booking->getId(),
                            'magId'=> $magasin->getId()
            ]));
            }else{
                $bookingEvent->addOption(
                    'url',

                    $this->router->generate('booking_show', [
                        'id' => $booking->getId(),
                        'magId'=> 0
                    ]));
            }

            $bookingEvent->addOption(
                'id',
                $booking->getId(),
            );
            // finally, add the event to the CalendarEvent to fill the calendar
            $calendar->addEvent($bookingEvent);
        }
    }
}