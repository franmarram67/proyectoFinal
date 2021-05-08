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

use App\Entity\Notification;
use App\Entity\Points;

// Cargar imagen
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/videogame')]
/**
 * @IsGranted("ROLE_ADMIN")
 */
class VideoGameController extends AbstractController
{
    #[Route('/', name: 'video_game_index', methods: ['GET'])]
    public function index(VideoGameRepository $videoGameRepository): Response
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
        return $this->render('video_game/index.html.twig', [
            'video_games' => $videoGameRepository->findAll(),
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/new', name: 'video_game_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $videoGame = new VideoGame();
        $form = $this->createForm(VideoGameType::class, $videoGame);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Código cargar imagen
            /** @var UploadedFile $img */
            $img = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($img) {
                $originalFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'.'.$img->guessExtension();

                // Move the file to the directory where img are stored
                try {
                    $img->move(
                        'img/',
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw $e;
                }

                // updates the 'img' property to store the PDF file name
                // instead of its contents
                $videoGame->setImage($newFilename);
            }

            // ... persist the $article variable or any other work y código que estaba

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($videoGame);
            $entityManager->flush();

            return $this->redirectToRoute('video_game_index');
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
        return $this->render('video_game/new.html.twig', [
            'video_game' => $videoGame,
            'form' => $form->createView(),
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/{id}', name: 'video_game_show', methods: ['GET'])]
    public function show(VideoGame $videoGame): Response
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
        return $this->render('video_game/show.html.twig', [
            'video_game' => $videoGame,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/{id}/edit', name: 'video_game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, VideoGame $videoGame, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(VideoGameType::class, $videoGame);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Código cargar imagen
            /** @var UploadedFile $img */
            $img = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($img) {
                $originalFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'.'.$img->guessExtension();

                // Move the file to the directory where img are stored
                try {
                    $img->move(
                        'img/',
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw $e;
                }

                // updates the 'img' property to store the PDF file name
                // instead of its contents
                $videoGame->setImage($newFilename);
            }

            // ... persist the $article variable or any other work y código que estaba

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('video_game_index');
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
        return $this->render('video_game/edit.html.twig', [
            'video_game' => $videoGame,
            'form' => $form->createView(),
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
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
