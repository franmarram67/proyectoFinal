<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Notification;
use App\Entity\Points;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
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

            $entityManager = $this->getDoctrine()->getManager();
            
            $user->setVerified(false);
            $user->setUsername("default");

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
            $totalPoints=0;
            foreach($this->getUser()->getPoints() as $points) {
                $totalPoints+=$points->getAmount();
            }
        } else {
            $unseen = null;
            $totalPoints = null;
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }
}
