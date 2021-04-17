<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Tournament;

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
        return $this->render('mytournaments/index.html.twig', [
            'pending' => $pending,
            'inprogress' => $inprogress,
            'finished' => $finished,
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
        return $this->render('mytournaments/inprogress.html.twig', [
            'inprogress' => $inprogress,
        ]);
    }

    #[Route('/created', name: 'mytournaments_created')]
    public function myTournamentsCreated(): Response
    {
        return $this->render('mytournaments/created.html.twig', [
            
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
        return $this->render('mytournaments/pending.html.twig', [
            'pending' => $pending,
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
        return $this->render('mytournaments/finished.html.twig', [
            'finished' => $finished,
        ]);
    }
}
