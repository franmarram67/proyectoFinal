<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\Province;

use App\Form\ProfileType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// Cargar imagen
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        $allusers=$this->getDoctrine()->getRepository(User::class)->findByVerified(false);
        //$allusers=$this->getDoctrine()->getRepository(User::class)->findAll();
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
    public function myProfile(Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Código cargar imagen
            /** @var UploadedFile $img */
            $img = $form->get('profilePicture')->getData();

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
                $user->setProfilePicture($newFilename);
            }

            // ... persist the $article variable or any other work y código que estaba

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('myprofile');
        }

        $userpoints = 0;
        return $this->render('main/myprofile.html.twig', [
            'userpoints' => $userpoints,
            'form' => $form->createView(),
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
