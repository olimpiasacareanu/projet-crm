<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DashboardController extends AbstractController
{

    public function index(Request $request): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $repository = $manager->getRepository(Contact::class);

        $contacts = $repository->createQueryBuilder('c')
            ->select(['c.id, c.fName, c.lName, c.phone'])
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->execute();

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


        return $this->render('dashboard/index.html.twig', [
            'contacts' => $contacts,
            'events' => $events,
        ]);
    }
}

