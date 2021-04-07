<?php

namespace App\Controller;


use App\Entity\Calendar;
use App\Repository\CalendarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use http\Client\Curl\User;
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
//
//        $user = $this->setUser();
      //  $events = $calendar->findBy(array ('users' => $user));
   //   $events = $calendar ->findAll();

       $user= $this->getUser();

   $events = $calendar->findEventsByUser($user);
  // dd($user);

      // dd($events);

            $rdvs = [];
       // dd($rdvs);
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
                  //  'userId' => $event ->setUser($this->getUser()),
                ];

                $data= json_encode($rdvs);
           //     dd($data);
           }



         return $this->render('calendar/index.html.twig', compact('data'));
    }


}
