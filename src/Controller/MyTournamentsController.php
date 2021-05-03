<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Tournament;
use App\Entity\Notification;

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
        $tournaments=$this->getUser()->getPlayedTournaments();
        $pending = [];
        $inprogress = [];
        $finished = [];
        foreach($tournaments as $tournament) {
            if($tournament->getHidden()==false) {
                if($tournament->getFinished()==true) {
                    array_push($finished,$tournament);
                }else if(date("now") < $tournament->getStartDate()) {
                    array_push($pending,$tournament);
                }else if(date("now") > $tournament->getStartDate()) {
                    array_push($inprogress,$tournament);
                }
            }
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
        return $this->render('mytournaments/index.html.twig', [
            'pending' => $pending,
            'inprogress' => $inprogress,
            'finished' => $finished,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/inprogress', name: 'mytournaments_inprogress')]
    public function myTournamentsInProgress(): Response
    {
        $tournaments=$this->getUser()->getPlayedTournaments();
        $inprogress = [];
        foreach($tournaments as $tournament) {
            if($tournament->getHidden()==false && $tournament->getFinished()!=true && date("now") > $tournament->getStartDate()) {
                array_push($inprogress,$tournament);
            }
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
        return $this->render('mytournaments/inprogress.html.twig', [
            'inprogress' => $inprogress,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/created', name: 'mytournaments_created')]
    public function myTournamentsCreated(): Response
    {
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
        return $this->render('mytournaments/created.html.twig', [
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/pending', name: 'mytournaments_pending')]
    public function myTournamentsPending(): Response
    {
        $tournaments=$this->getUser()->getPlayedTournaments();
        $pending = [];
        foreach($tournaments as $tournament) {
            if($tournament->getHidden()==false && $tournament->getFinished()!=true && date("now") < $tournament->getStartDate()) {
                array_push($pending,$tournament);
            }
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
        return $this->render('mytournaments/pending.html.twig', [
            'pending' => $pending,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/finished', name: 'mytournaments_finished')]
    public function myTournamentsFinished(): Response
    {
        $tournaments=$this->getUser()->getPlayedTournaments();
        $finished = [];
        foreach($tournaments as $tournament) {
            if($tournament->getHidden()==false && $tournament->getFinished()==true) {
                array_push($finished,$tournament);
            }
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
        return $this->render('mytournaments/finished.html.twig', [
            'finished' => $finished,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }
}
