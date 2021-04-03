<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\Province;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(): Response
    {
        $tournaments=$this->getDoctrine()->getRepository(Tournament::class)->findAll();
        return $this->render('main/index.html.twig', [
            'tournaments' => $tournaments,
        ]);
    }

    #[Route('/verifyusers', name: 'verifyusers')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function verifyUsers(): Response
    {
        $allusers=$this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('main/verifyusers.html.twig', [
            'allusers' => $allusers,
        ]);
    }

    #[Route('/verify/{id}', name: 'verify')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function verify($id): Response
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $user->setVerified(true);
        $entityManager->flush();

        return $this->redirectToRoute('verifyusers');
    }

    #[Route('/signuptotournament/{id}', name: 'signuptotournament')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function signUpToTournament($id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $tournament->addPlayer($this->getUser());
        $entityManager->flush();

        return $this->redirectToRoute('main');
    }

    #[Route('/myprofile', name: 'myprofile')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function myProfile(): Response
    {
        $userpoints = 0;
        return $this->render('main/myprofile.html.twig', [
            'userpoints' => $userpoints,
        ]);
    }

    #[Route('/seetournament/{id}', name: 'seetournament')]
    public function seeTournament($id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        return $this->render('main/seetournament.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/seeprovince/{id}', name: 'seeprovince')]
    public function seeProvince($id): Response
    {
        $province=$this->getDoctrine()->getRepository(Province::class)->find($id);
        return $this->render('main/seeprovince.html.twig', [
            'province' => $province,
        ]);
    }

    #[Route('/seeallprovinces', name: 'seeallprovinces')]
    public function seeAllProvinces(): Response
    {
        $provinces=$this->getDoctrine()->getRepository(Province::class)->findAll();
        return $this->render('main/seeallprovinces.html.twig', [
            'provinces' => $provinces,
        ]);
    }

}
