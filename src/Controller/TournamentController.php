<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TournamentType;
use App\Form\AdminTournamentType;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\Notification;

use Symfony\Component\Config\Definition\Exception\Exception;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/', name: 'tournament_index', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournamentRepository->findAll(),
            'unseen' => $unseen,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    #[Route('/new', name: 'tournament_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if($this->getUser()->getVerified() == true) {
            $tournament = new Tournament();
            $form = $this->createForm(TournamentType::class, $tournament);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();

                $tournament->setCreatorUser($this->getUser());
                $tournament->setFinished(false);
                $tournament->setCreationDate(new \DateTime);
                $tournament->setHidden(false);

                $entityManager->persist($tournament);
                $entityManager->flush();

                return $this->redirectToRoute('main');
            }

            if($this->getUser()) {
                $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
            } else {
                $unseen = null;
            }
            return $this->render('tournament/new.html.twig', [
                'tournament' => $tournament,
                'form' => $form->createView(),
                'unseen' => $unseen,
            ]);
        } else {
            return new Response("You have to be a verified user to create a Tournament.");
        }
        
        
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/adminnew', name: 'admin_tournament_new', methods: ['GET', 'POST'])]
    public function adminNew(Request $request): Response
    {
        if($this->getUser()->getVerified() == true) {
            $tournament = new Tournament();
            $form = $this->createForm(AdminTournamentType::class, $tournament);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();

                $entityManager->persist($tournament);
                $entityManager->flush();

                return $this->redirectToRoute('main');
            }

            if($this->getUser()) {
                $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
            } else {
                $unseen = null;
            }
            return $this->render('tournament/new.html.twig', [
                'tournament' => $tournament,
                'form' => $form->createView(),
                'unseen' => $unseen,
            ]);
        } else {
            return new Response("You have to be a verified user to create a Tournament.");
        }
        
        
        
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/{id}', name: 'tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament): Response
    {
        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'unseen' => $unseen,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/{id}/edit', name: 'tournament_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournament $tournament): Response
    {
        $form = $this->createForm(AdminTournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tournament_index');
        }

        if($this->getUser()) {
            $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        } else {
            $unseen = null;
        }
        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
            'unseen' => $unseen,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/{id}', name: 'tournament_delete', methods: ['POST'])]
    public function delete(Request $request, Tournament $tournament): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_index');
    }
}
