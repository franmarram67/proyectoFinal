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
use App\Form\ChangePasswordType;
use App\Form\ChangeEmailType;
use App\Form\FinishTournamentType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Config\Definition\Exception\Exception;

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

    #[Route('/adminpage', name: 'adminpage')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminPage(): Response
    {
        return $this->render('main/adminpage.html.twig', [
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
    public function signUpToTournament($id, Request $request): Response
    {
        try {
            $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
            if($this->getUser()) {
                if(!$tournament->getPlayers()->contains($this->getUser())) { //Comprobar que el usuario no está apuntado en el torneo
                    if($this->getUser()->getVerified() == true) {
                        if($this->getUser()->getId() != $tournament->getCreatorUser()->getId()) {
                            if(date("now") < $tournament->getStartDate()) {
                                if(count($tournament->getPlayers()) < 20) {
                                    
                                    $entityManager = $this->getDoctrine()->getManager();
                                    $tournament->addPlayer($this->getUser());
                                    $entityManager->flush();
                                    //return $this->redirectToRoute('main');

                                    $request->getSession()->getFlashBag()->add('notice','success');
                                    $referer = $request->headers->get('referer');
                                    return $this->redirect($referer);
                                   
                                    
                                } else {
                                    throw new Exception("You can't Sign Up to this Tournament because there's no more places left. Max 20 players per tournament.");
                                }
                            } else {
                                throw new Exception("You can't Sign Up to this Tournament the date to Sign Up has expired. You have to Sign Up before the Start Date.");
                            }
                        } else {
                            throw new Exception("You can't Sign Up to your own Tournament.");
                        }
                    } else {
                        throw new Exception("You can't Sign Up to this Tournament because you're not a verified user.");
                    }
                } else {
                    throw new Exception("You already Signed Up to this Tournament.");
                }
            } else {
                throw new Exception("Login to be able to Sign Up to this Tournament.");
            }

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
        
    }

    #[Route('/myprofile', name: 'myprofile')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function myProfile(Request $request, SluggerInterface $slugger, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();

        //Edit Profile
        $profileForm = $this->createForm(ProfileType::class, $user);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {

            // Código cargar imagen
            /** @var UploadedFile $img */
            $img = $profileForm->get('profilePicture')->getData();

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

            $dni = $profileForm->get('dni')->getData();
            $name = $profileForm->get('name')->getData();
            $surname = $profileForm->get('surname')->getData();
            $province = $profileForm->get('province')->getData();

            if($dni) {
                $user->setDni($dni);
            }
            if($name) {
                $user->setName($name);
            }
            if($surname) {
                $user->setSurname($surname);
            }
            if($province) {
                $user->setProvince($province);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('myprofile');
        }

        //Change Password
        $passwordForm = $this->createForm(ChangePasswordType::class, $user);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {

            /*var_dump($passwordForm->get('plainPassword')->getData());
            exit;*/
            // encode the plain password
            if($passwordForm->get('newPassword')->getData()!=null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $passwordForm->get('newPassword')->getData()
                    )
                );
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('myprofile');
        }

        //Change Email
        $emailForm = $this->createForm(ChangeEmailType::class, $user);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {

            $email = $emailForm->get('newEmail')->getData();

            if($email) {
                $user->setEmail($email);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('myprofile');
        }

        $userpoints = 0;
        return $this->render('main/myprofile.html.twig', [
            'userpoints' => $userpoints,
            'profileForm' => $profileForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'emailForm' => $emailForm->createView(),
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

    #[Route('/mytournaments', name: 'mytournaments')]
    public function myTournaments(): Response
    {
        $tournaments=$this->getUser()->getPlayedTournaments();
        $pending = [];
        $inprogress = [];
        $finished = [];
        foreach($tournaments as $tournament) {
            if($tournament->getFinished()==true) {
                array_push($finished,$tournament);
            }else if(date("now") < $tournament->getStartDate()) {
                array_push($pending,$tournament);
            }else if(date("now") > $tournament->getStartDate()) {
                array_push($inprogress,$tournament);
            }
        }
        return $this->render('main/mytournaments.html.twig', [
            'pending' => $pending,
            'inprogress' => $inprogress,
            'finished' => $finished,
        ]);
    }

    #[Route('/finishtournament/{id}', name: 'finishtournament')]
    public function finishTournament(Request $request, $id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        $form = $this->createForm(FinishTournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('main');
        }
        
        return $this->render('main/finishtournament.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

}
