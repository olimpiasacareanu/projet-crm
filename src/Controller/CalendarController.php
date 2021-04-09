<?php

namespace App\Controller;


use App\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CalendarController extends AbstractController
{

    public function index(CalendarRepository $calendar )
    {

//$events = $calendar -> findAll();
       $user= $this->getUser();
       $events = $calendar->findEventsByUser($user);
        // dd($user);
//        dd($events);

        $rdvs = [];
     //   dd($rdvs);

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

            $data= json_encode($rdvs);
            //dd($data);
        }


        return $this->render('calendar/index.html.twig', compact('data'));
    }


}
