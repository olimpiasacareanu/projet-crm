<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\User;
use App\Form\CalendarType;
use App\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(CalendarRepository $calendar): Response
    {
        $user= $this->getUser();

        $events = $calendar->findEventsByUser($user);
        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);

    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $calendar = new Calendar();
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        //    $calendar->setUser($this->getUser());
           $calendar->addUser($this->getUser());
         //  dd($calendar);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($calendar);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"})
     */
    public function show(Calendar $calendar): Response
    {
        return $this->render('event/show.html.twig', [
            'calendar' => $calendar,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Calendar $calendar): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"POST"})
     */
    public function delete(Request $request, Calendar $calendar): Response
    {
        if ($this->isCsrfTokenValid('delete'.$calendar->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($calendar);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/{id}/invite", name="event_invite", methods={"GET", "POST"})
     */
    public function inviteUser($id, Request $request)
    {
      //  $manager = $this->getDoctrine()->getManager();
    //    $events = $manager->getRepository(Calendar::class)->find($id);
   //   dd($events);
/*
        if (!$events) {
            throw $this->createNotFoundException('Aucun événement trouvé '.$id);
        }
        if($request->isMethod('post'))
        {

            $repository = $manager->getRepository(Calendar::class);
            $user= $this->getUser();
            if($user){
                $events = $repository->createQueryBuilder('e')
                    ->join('e.users', 'user')
                    ->where('user.id = :user')
                    ->setParameter('user', $user)
                    ->orderBy('e.id', 'DESC')
                    ->setMaxResults(3)
                    ->getQuery()
                    ->execute();
            }
            $user = $this->getUser();
            //     dd($user);
            $events->getUsers()->add($user);
            //    dd($event);
            $manager->persist($events);
            $manager->flush();
        }



        return $this->redirectToRoute('event_invite', [
            'events'=>$events->getId(),
        ]);
*/
    }
}

//SELECT calendar.id, calendar.title
//FROM user_calendar
//JOIN calendar ON user_calendar.calendar_id = calendar.id
//JOIN user ON user_calendar.user_id = user.id
//WHERE user.email = 'test@test.fr'
