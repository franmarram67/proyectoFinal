<?php

namespace App\Controller;

use App\Entity\Points;
use App\Form\PointsType;
use App\Repository\PointsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\Notification;

#[Route('/points')]
/**
 * @IsGranted("ROLE_ADMIN")
 */
class PointsController extends AbstractController
{
    #[Route('/', name: 'points_index', methods: ['GET'])]
    public function index(PointsRepository $pointsRepository): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('points/index.html.twig', [
            'points' => $pointsRepository->findAll(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/new', name: 'points_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $point = new Points();
        $form = $this->createForm(PointsType::class, $point);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($point);
            $entityManager->flush();

            return $this->redirectToRoute('points_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('points/new.html.twig', [
            'point' => $point,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}', name: 'points_show', methods: ['GET'])]
    public function show(Points $point): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('points/show.html.twig', [
            'point' => $point,
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}/edit', name: 'points_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Points $point): Response
    {
        $form = $this->createForm(PointsType::class, $point);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('points_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('points/edit.html.twig', [
            'point' => $point,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    #[Route('/{id}', name: 'points_delete', methods: ['POST'])]
    public function delete(Request $request, Points $point): Response
    {
        if ($this->isCsrfTokenValid('delete'.$point->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($point);
            $entityManager->flush();
        }

        return $this->redirectToRoute('points_index');
    }
}
