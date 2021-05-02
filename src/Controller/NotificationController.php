<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Form\NotificationType;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[Route('/notification')]
/**
 * @IsGranted("ROLE_ADMIN")
 */
class NotificationController extends AbstractController
{
    #[Route('/', name: 'notification_index', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('notification/index.html.twig', [
            'notifications' => $notificationRepository->findAll(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/new', name: 'notification_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $notification = new Notification();
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($notification);
            $entityManager->flush();

            return $this->redirectToRoute('notification_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('notification/new.html.twig', [
            'notification' => $notification,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}', name: 'notification_show', methods: ['GET'])]
    public function show(Notification $notification): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('notification/show.html.twig', [
            'notification' => $notification,
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}/edit', name: 'notification_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Notification $notification): Response
    {
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('notification_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('notification/edit.html.twig', [
            'notification' => $notification,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}', name: 'notification_delete', methods: ['POST'])]
    public function delete(Request $request, Notification $notification): Response
    {
        if ($this->isCsrfTokenValid('delete'.$notification->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($notification);
            $entityManager->flush();
        }

        return $this->redirectToRoute('notification_index');
    }
}
