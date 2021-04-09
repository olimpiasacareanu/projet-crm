<?php

namespace App\Controller;

use App\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


class CalendarController extends AbstractController
{
    /**
     * @param CalendarRepository $calendar
     * @return Response
     */

    public function index(CalendarRepository $calendar): Response
    {

        $user= $this->getUser();
        $events = $calendar->findEventsByUser($user);

            $rdvs = [];
            foreach($events as $event){
                $rdvs[] = [
                    'id' => $event->getId(),
                    'start' => $event->getStart()->format('Y-m-d H:i:s'),
                    'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                    'title' => $event->getTitle(),
                    'description' => $event->getDescription(),
                    'backgroundColor' => $event->getBackgroundColor(),
                    'textColor' => $event->getTextColor(),
                    'allDay' => $event ->getAllDay(),
                ];

                $data = json_encode($rdvs);
            }


        return $this->render('calendar/index.html.twig', compact('data'));
    }
}
