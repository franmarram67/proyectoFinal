<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\Notification;

#[Route('/user')]
/**
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    /*
    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }*/

    #[Route('/verify/{id}', name: 'user_verify', methods: ['GET', 'POST'])]
    public function verify(Request $request, $id): Response
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $user->setVerified(true);
        $entityManager->flush();
        

        return $this->redirectToRoute('user_index');
    }
}
