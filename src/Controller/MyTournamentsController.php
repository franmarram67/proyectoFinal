<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Tournament;
use App\Entity\Notification;
use App\Entity\Points;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/mytournaments', name: 'mytournaments')]
/**
 * @IsGranted("ROLE_USER")
 */
class MyTournamentsController extends AbstractController
{
    #[Route('/', name: 'mytournaments')]
    public function index(): Response
    {
        // $tournaments=$this->getUser()->getPlayedTournaments();
        // $pending = [];
        // $inprogress = [];
        // $finished = [];
        // foreach($tournaments as $tournament) {
        //     if($tournament->getHidden()==false) {
        //         if($tournament->getFinished()==true) {
        //             array_push($finished,$tournament);
        //         }else if(date("now") < $tournament->getStartDate()) {
        //             array_push($pending,$tournament);
        //         }else if(date("now") > $tournament->getStartDate()) {
        //             array_push($inprogress,$tournament);
        //         }
        //     }
        // }

        $inprogress=$this->getDoctrine()->getRepository(Tournament::class)->findAllByPlayerInProgress($this->getUser(), new \DateTime);
        $pending=$this->getDoctrine()->getRepository(Tournament::class)->findAllByPlayerPending($this->getUser(), new \DateTime);
        $finished=$this->getDoctrine()->getRepository(Tournament::class)->findAllByPlayerFinished($this->getUser());
        $created=$this->getDoctrine()->getRepository(Tournament::class)->findAllByCreator($this->getUser());
        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('mytournaments/index.html.twig', [
            'pending' => $pending,
            'inprogress' => $inprogress,
            'finished' => $finished,
            'created' => $created,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/inprogress', name: 'mytournaments_inprogress')]
    public function myTournamentsInProgress(): Response
    {
        // $tournaments=$this->getUser()->getPlayedTournaments();
        // $inprogress = [];
        // foreach($tournaments as $tournament) {
        //     if($tournament->getHidden()==false && $tournament->getFinished()!=true && date("now") > $tournament->getStartDate()) {
        //         array_push($inprogress,$tournament);
        //     }
        // }
        $inprogress=$this->getDoctrine()->getRepository(Tournament::class)->findAllByPlayerInProgress($this->getUser(), new \DateTime);
        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('mytournaments/inprogress.html.twig', [
            'inprogress' => $inprogress,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/created', name: 'mytournaments_created')]
    public function myTournamentsCreated(): Response
    {
        $created=$this->getDoctrine()->getRepository(Tournament::class)->findAllByCreator($this->getUser());
        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('mytournaments/created.html.twig', [
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
            'created' => $created,
        ]);
    }

    #[Route('/pending', name: 'mytournaments_pending')]
    public function myTournamentsPending(): Response
    {
        // $tournaments=$this->getUser()->getPlayedTournaments();
        // $pending = [];
        // foreach($tournaments as $tournament) {
        //     if($tournament->getHidden()==false && $tournament->getFinished()!=true && date("now") < $tournament->getStartDate()) {
        //         array_push($pending,$tournament);
        //     }
        // }
        $pending=$this->getDoctrine()->getRepository(Tournament::class)->findAllByPlayerPending($this->getUser(), new \DateTime);
        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('mytournaments/pending.html.twig', [
            'pending' => $pending,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/finished', name: 'mytournaments_finished')]
    public function myTournamentsFinished(): Response
    {
        // $tournaments=$this->getUser()->getPlayedTournaments();
        // $finished = [];
        // foreach($tournaments as $tournament) {
        //     if($tournament->getHidden()==false && $tournament->getFinished()==true) {
        //         array_push($finished,$tournament);
        //     }
        // }
        $finished=$this->getDoctrine()->getRepository(Tournament::class)->findAllByPlayerFinished($this->getUser());
        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('mytournaments/finished.html.twig', [
            'finished' => $finished,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }
}
