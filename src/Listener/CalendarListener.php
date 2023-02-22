<?php

namespace App\Listener;

use App\Entity\Booking;
use CalendarBundle\Entity\Event;
use App\Repository\BookingRepository;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarListener
{
    private $bookingRepository;
    private $router;
    private $sessionManager;
    private $securityContext;

    public function __construct(BookingRepository $bookingRepository, UrlGeneratorInterface $router)
    {

        $this->bookingRepository = $bookingRepository;
        $this->router = $router;

    }

    public function load(CalendarEvent $calendar): void
    {

        $start = $calendar->getStart()->format('Y-m-d H:i:s');
        $end = $calendar->getEnd()->format('Y-m-d H:i:s');

        $filters = $calendar->getFilters();

        $contact = null;
        $contact = $filters['contact_id'];
        if (!empty($filters))
        {
            if (array_key_exists('contact_id', $filters))
            {
                $contact = $filters['contact_id'];
            }
        }
        $bookings = $this->bookingRepository->findBetweenDates($start, $end, $contact);

        foreach ($bookings as $booking)
        {
            // this create the events with your data (here booking data) to fill calendar
            $bookingEvent = new Event(
                $booking->getTitle(),
                $booking->getBeginAt(),
                $booking->getEndAt() // If the end date is null or not defined, a all day event is created.
            );

            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
             */

            // finally, add the event to the CalendarEvent to fill the calendar
            $calendar->addEvent($bookingEvent);

        }

    }
}