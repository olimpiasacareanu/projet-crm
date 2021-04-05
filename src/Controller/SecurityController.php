<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('dashboard_index');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function logout()
    {
        return $this->render('security/logout.html.twig');
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function forgottenPass(Request $request, UserRepository $repository, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        // On crée le formulaire
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        //Si le formulaire est valide
        if($form->isSubmitted() && $form->isValid())
        {
            $donnees = $form->getData();
         // dd($donnees);

            // On cherche si un utilisateur a cet email
            $user = $repository->findOneBy(array('email' => $donnees));
            // dd($user);
            //Si l'utilisateur n'existe pas
            if(!$user)
            {
                //On envoie un msg flash
                $this->addFlash('danger', 'cette adresse n\'existe pas');
                return $this->redirectToRoute('security_login');
            }

                // On génère un token
                $token = $tokenGenerator->generateToken();

                try{
                    $user->setResetToken($token);
                    $manager = $this->getDoctrine()->getManager();
                    $manager->persist($user);
                    $manager->flush();
                }catch(\Exception $e){
                    $this->addFlash('warning', 'Une erreur est survenue : '. $e->getMessage());
                    return $this->redirectToRoute('security_login');
                }

                // On génère l'URL de réinitialisation de mot de passe
                $url = $this->generateUrl('security_resetpass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // On crée le msg
                $message = (new \Swift_Message('Mot de passe oublié'))
                    //On attribue l'expediteur
                    ->setFrom('no-replay@easy.com')
                    // On attribue le destinataire
                    ->setTo($user->getEmail())
                    // On crée le contenu
                    ->setBody(
                        "<p>Bonjour,</p><p>Une demande de réinitialisation de mot de passe a été effectuée pour votre compte. Veuillez cliquer sur le lien suivant : " . $url .'</p>',
                        'text/html'
                    )
                ;
                //On envoie le message
                $mailer->send($message);

                // On crée le msg flash
                $this->addFlash('message', 'Un e-mail de réinitialisation d emot de passe vous a été envoyé');

                return $this->redirectToRoute('security_login');

        }
            // On envoie ver sla page de demande de l'email
            return $this->render('security/forgotten_pass.html.twig', ['emailForm' => $form->createView()]);
    }

    public function resetPass($token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche l'utilisateur avec le token fourni
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token' =>$token]);

        if(!$user)
        {
            $this->addFlash('danger', 'Token inconnu');
            return $this->redirectToRoute('security_login');
        }
        // Si le formulaire est envoyé en méthode POST
        if($request->isMethod('post')){
            // On supprime le token
            $user->setResetToken(null);

            //On chiffre le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('message', 'Mot de passe modifié avec succès');

            return $this->redirectToRoute('security_login');
        }else{
            return $this->render('security/reset_pass.html.twig', ['token' => $token]);
        }

    }
}
