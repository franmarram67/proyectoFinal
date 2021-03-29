<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Form\VideoGameType;
use App\Repository\VideoGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/videogame')]
/**
 * @IsGranted("ROLE_ADMIN")
 */
class VideoGameController extends AbstractController
{
    #[Route('/', name: 'video_game_index', methods: ['GET'])]
    public function index(VideoGameRepository $videoGameRepository): Response
    {
        return $this->render('video_game/index.html.twig', [
            'video_games' => $videoGameRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'video_game_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $videoGame = new VideoGame();
        $form = $this->createForm(VideoGameType::class, $videoGame);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($videoGame);
            $entityManager->flush();

            return $this->redirectToRoute('video_game_index');
        }

        return $this->render('video_game/new.html.twig', [
            'video_game' => $videoGame,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'video_game_show', methods: ['GET'])]
    public function show(VideoGame $videoGame): Response
    {
        return $this->render('video_game/show.html.twig', [
            'video_game' => $videoGame,
        ]);
    }

    #[Route('/{id}/edit', name: 'video_game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, VideoGame $videoGame): Response
    {
        $form = $this->createForm(VideoGameType::class, $videoGame);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('video_game_index');
        }

        return $this->render('video_game/edit.html.twig', [
            'video_game' => $videoGame,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'video_game_delete', methods: ['POST'])]
    public function delete(Request $request, VideoGame $videoGame): Response
    {
        if ($this->isCsrfTokenValid('delete'.$videoGame->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($videoGame);
            $entityManager->flush();
        }

        return $this->redirectToRoute('video_game_index');
    }
}
