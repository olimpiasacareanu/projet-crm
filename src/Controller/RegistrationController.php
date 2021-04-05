<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;


class RegistrationController extends AbstractController
{

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator, \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            // On génère le token d'activation
            $user->setActivationToken(md5(uniqid()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            // On crée le msg
            $message = (new \Swift_Message('Activation de votre compte'))
                //On attribue l'expediteur
                ->setFrom('no-replay@easy.com')
                // On attribue le destinataire
                ->setTo($user->getEmail())
                // On crée le contenu
                ->setBody(
                    $this->renderView(
                    'emails/activation.html.twig', ['token' => $user->getActivationToken()]
                ),
                    'text/html'
            )
       ;

            // On envoie l'e-mail
            $mailer->send($message);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function activation($token, UserRepository $repository)
    {
        // On verifie si un utser a son token
        $user = $repository->findOneBy(['activation_token' => $token]);

        //Si aucoun utilisateur n'existe avec ce token
        if(!$user)
        {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // On suprime le token
        $user->setActivationToken(null);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();

        // On envoie un message flash
        $this->addFlash('message', 'Vous avez bien activé votre compte');

        // On retrouve à l'accueil
        return $this->redirectToRoute('profil_index');

    }
}
