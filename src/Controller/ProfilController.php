<?php

namespace App\Controller;


use App\Entity\Calendar;
use App\Entity\User;
use App\Form\CalendarType;
use App\Form\EditProfilType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ProfilController extends AbstractController
{

    public function index(UserRepository $repository)
    {
        return $this->render('profil/index.html.twig');
    }


    public function editProfil(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('message', 'Profil mis à jour');

            return $this->redirectToRoute('profil_index');
        }

        return $this->render('profil/editprofil.html.twig', [
            'formProfil' => $form->createView(),
        ]);
    }

    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if($request->isMethod('POST'))
        {
            $manager = $this->getDoctrine()->getManager();

            $user = $this->getUser();

            //On verifie si les 2 mots de passe sont identiques
            if($request->request->get('pass') == $request->request->get('pass2'))
            {
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass')));
                $manager->flush();
                $this->addFlash('message', 'Mot de passe mis a jour avec succès');

                return $this->redirectToRoute('profil_index');

            }else{
                $this->addFlash('error', 'Les mots de passe ne sont pas identiques');
            }

        }
        return $this->render('profil/editpass.html.twig');
    }

}
