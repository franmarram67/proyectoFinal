<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\Province;
use App\Entity\Notification;
use App\Entity\Points;
use App\Entity\VideoGame;

use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use App\Form\ChangeEmailType;
use App\Form\FinishTournamentType;
use App\Form\TournamentNoPlacesType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Validator\Constraints\DateTime;

// Cargar imagen
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(): Response
    {
        $tournaments=$this->getDoctrine()->getRepository(Tournament::class)->findAllByHidden(false);
        // Continuar por aquí
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
        return $this->render('main/index.html.twig', [
            'tournaments' => $tournaments,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
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

        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('main/verifyusers.html.twig', [
            'allusers' => $allusers,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/adminpage', name: 'adminpage')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminPage(): Response
    {
        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('main/adminpage.html.twig', [
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
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

    #[Route('/verifyall', name: 'verifyall')]
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function verifyAll(): Response
    {
        $allusers=$this->getDoctrine()->getRepository(User::class)->findByVerified(false);
        $entityManager = $this->getDoctrine()->getManager();
        foreach($allusers as $user) {
            $user->setVerified(true);
        }
        $entityManager->flush();

        return $this->redirectToRoute('verifyusers');
    }

    #[Route('/signuptotournament/{id}', name: 'signuptotournament')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function signUpToTournament($id, Request $request): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        if($tournament->getHidden()==false) {
            if($this->getUser()->getId() != $tournament->getCreatorUser()->getId()) {
                if($tournament->getFinished()==false) {
                    if(!$tournament->getPlayers()->contains($this->getUser())) { //Comprobar que el usuario no está apuntado en el torneo
                        if($this->getUser()->getVerified() == true) {
                            
                            if(date("now") < $tournament->getStartDate()) {
                                if(count($tournament->getPlayers()) < $tournament->getPlaces()) {
                                    
                                    $entityManager = $this->getDoctrine()->getManager();
                                    $tournament->addPlayer($this->getUser());
                                    $entityManager->flush();
                                    //return $this->redirectToRoute('main');

                                    $request->getSession()->getFlashBag()->add('notice','success');
                                    $referer = $request->headers->get('referer');
                                    return $this->redirect($referer);
                                    
                                    
                                } else {
                                    return new Response("You can't Sign Up to this Tournament because there's no more places left");
                                }
                            } else {
                                return new Response("You can't Sign Up to this Tournament the date to Sign Up has expired. You have to Sign Up before the Start Date.");
                            }
                            
                        } else {
                            return new Response("You can't Sign Up to this Tournament because you're not a verified user.");
                        }
                    } else {
                        return new Response("You already Signed Up to this Tournament.");
                    }
                } else {
                    return new Response("You can't Sign Up to a finished Tournament.");
                }
            } else {
                return new Response("You can't Sign Up to your own Tournament.");
            }
            
        } else {
            return new Response("You can't sign up to a deleted Tournament");
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

        $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
        $totalPoints=0;
        foreach($this->getUser()->getPoints() as $points) {
            $totalPoints+=$points->getAmount();
        }
        return $this->render('main/myprofile.html.twig', [
            'profileForm' => $profileForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'emailForm' => $emailForm->createView(),
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/seetournament/{id}', name: 'seetournament')]
    public function seeTournament($id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        if($tournament->getHidden()==false) {
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
            return $this->render('main/seetournament.html.twig', [
                't' => $tournament,
                'unseen' => $unseen,
                'totalPoints' => $totalPoints,
            ]);
        } else {
            return new Response("You can't see this tournament because it has been deleted.");
        }
        
    }

    #[Route('/mypoints', name: 'mypoints')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function myPoints(): Response
    {
        $myPoints=$this->getDoctrine()->getRepository(Points::class)->findAllByUser($this->getUser());
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
        return $this->render('main/mypoints.html.twig', [
            'myPoints' => $myPoints,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/mynotifications', name: 'mynotifications')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function myNotifications(): Response
    {
        $notifications=$this->getDoctrine()->getRepository(Notification::class)->findAllOrderedByCreationDate($this->getUser());

        $em = $this->getDoctrine()->getManager();

        foreach($notifications as $n) {
            if($n->getSeen()==false) {
                $n->setSeen(true);
                $n->setSeenDate(new \DateTime);
            }
        }

        $em->flush();

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
        return $this->render('main/mynotifications.html.twig', [
            'notifications' => $notifications,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/seealltournaments', name: 'seealltournaments')]
    public function seeAllTournaments(): Response
    {
        $tournaments=$this->getDoctrine()->getRepository(Tournament::class)->findAllByHidden(false);
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
        return $this->render('main/seealltournaments.html.twig', [
            'tournaments' => $tournaments,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/finishtournament/{id}', name: 'finishtournament')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function finishTournament(Request $request, $id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);    
        if($tournament->getHidden()==false) {
            if($this->getUser()->getId() == $tournament->getCreatorUser()->getId()) {
                if($tournament->getFinished()==false) {
                    if(date("now") < $tournament->getStartDate()) {
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
                        return $this->render('main/finishtournament.html.twig', [
                            'tournament' => $tournament,
                            'unseen' => $unseen,
                            'totalPoints' => $totalPoints,
                        ]);
                    } else {
                        return new Response("You can't finish a tournament before the start date.");
                    }
                } else {
                    return new Response("You can't finish an already finished Tournament.");
                }
            } else {
                return new Response("You have to be the tournament creator to finish it.");
            }
            
        } else {
            return new Response("You can't finish a deleted Tournament.");
        }
        
    }

    #[Route('/finishtournamentajax/{id}', name: 'finishtournamentajax', methods: ['GET'])]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function finishTournamentAjax(Request $request,$id): Response
    {
        //Continuar por aquí...
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id); 
        if($tournament->getHidden()==false) {
            if($this->getUser()->getId() == $tournament->getCreatorUser()->getId()) {
                if($tournament->getFinished()==false) {
                    if(date("now") < $tournament->getStartDate()) {
                        $firstPlace = $this->getDoctrine()->getRepository(User::class)->find($request->query->get('firstPlace'));
                        $secondPlace = $this->getDoctrine()->getRepository(User::class)->find($request->query->get('secondPlace'));
                        $thirdPlace = $this->getDoctrine()->getRepository(User::class)->find($request->query->get('thirdPlace'));
                        $fourthPlace = $this->getDoctrine()->getRepository(User::class)->find($request->query->get('fourthPlace'));
                    
                        if ( !$firstPlace || !$secondPlace || !$thirdPlace || !$fourthPlace ) {
                            return new Response("Error");
                        }
            
                        if($tournament->getPlayers()->contains($firstPlace)&&$tournament->getPlayers()->contains($secondPlace)&&$tournament->getPlayers()->contains($thirdPlace)&&$tournament->getPlayers()->contains($fourthPlace)) {
                            if($firstPlace!=$secondPlace&&$firstPlace!=$thirdPlace&&$firstPlace!=$fourthPlace && $secondPlace!=$thirdPlace&&$secondPlace!=$fourthPlace && $thirdPlace!=$fourthPlace) {
                                
                                $em = $this->getDoctrine()->getManager();

                                //Set Places
                                $tournament->setFirstPlace($firstPlace);
                                $tournament->setSecondPlace($secondPlace);
                                $tournament->setThirdPlace($thirdPlace);
                                $tournament->setFourthPlace($fourthPlace);
                
                                //Finish Tournaments
                                $tournament->setFinished(true);
                                $tournament->setFinishDate(new \DateTime);

                                //Create Points
                                $pointsFirst = new Points();
                                $pointsSecond = new Points();
                                $pointsThird = new Points();
                                $pointsFourth = new Points();

                                $multiplier = $tournament->getPlaces()/8;
                                $pointsFirst->setUser($firstPlace);
                                $pointsFirst->setDatetime(new \DateTime);
                                $pointsFirst->setAmount(500 * $multiplier);
                                $pointsFirst->setTournament($tournament);

                                $pointsSecond->setUser($secondPlace);
                                $pointsSecond->setDatetime(new \DateTime);
                                $pointsSecond->setAmount(350 * $multiplier);
                                $pointsSecond->setTournament($tournament);

                                $pointsThird->setUser($thirdPlace);
                                $pointsThird->setDatetime(new \DateTime);
                                $pointsThird->setAmount(200 * $multiplier);
                                $pointsThird->setTournament($tournament);

                                $pointsFourth->setUser($fourthPlace);
                                $pointsFourth->setDatetime(new \DateTime);
                                $pointsFourth->setAmount(100 * $multiplier);
                                $pointsFourth->setTournament($tournament);

                                $em->persist($pointsFirst);
                                $em->persist($pointsSecond);
                                $em->persist($pointsThird);
                                $em->persist($pointsFourth);

                                //Send Notifications
                                foreach($tournament->getPlayers() as $player) {
                                    $notification = new Notification();
                                    $notification->setUser($player);
                                    $notification->setSeen(false);
                                    $notification->setCreationDate(new \DateTime);
                                    if($player->getId()==$firstPlace->getId()) {
                                        $notification->setText("<p>You have won the first place in this <a href='/seetournament/".$tournament->getId()."'>tournament</a>.</p>");
                                    } else if($player->getId()==$secondPlace->getId()) {
                                        $notification->setText("<p>You have won the second place in this <a href='/seetournament/".$tournament->getId()."'>tournament</a>.</p>");
                                    } else if($player->getId()==$thirdPlace->getId()) {
                                        $notification->setText("<p>You have won the third place in this <a href='/seetournament/".$tournament->getId()."'>tournament</a>.</p>");
                                    } else if($player->getId()==$fourthPlace->getId()) {
                                        $notification->setText("<p>You have won the fourth place in this <a href='/seetournament/".$tournament->getId()."'>tournament</a>.</p>");
                                    } else {
                                        $notification->setText("<p>You haven't won in this <a href='/seetournament/".$tournament->getId()."'>tournament</a>. Please try again, never give up!</p>");
                                    }
                                    $em->persist($notification);
                                }

                                $notification = new Notification();
                                $notification->setUser($tournament->getCreatorUser());
                                $notification->setSeen(false);
                                $notification->setCreationDate(new \DateTime);
                                $notification->setText("<p>You have finished this <a href='/seetournament/".$tournament->getId()."'>tournament</a>.</p>");
                                $em->persist($notification);

                                $em->flush();
                                    
                                return $this->redirect("/seetournament/".$id);
                            }else{
                                return new Response("The same user can't be in two or more different places at the same time.");
                            }
                        } else {
                            return new Response("You can't add a user to a place if the user is not a tournament player.");
                        }
                        
                    } else {
                        return new Response("You can't finish a tournament before the start date.");
                    }
                    
                } else {
                    return new Response("You can't finish an already finished Tournament.");
                }
            } else {
                return new Response("You have to be the tournament creator to finish it.");
            }
        } else {
            return new Response("You can't finish a deleted Tournament.");
        }
        
    }

    #[Route('/edittournament/{id}', name: 'edittournament')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function editTournament($id, Request $request): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        if($tournament->getHidden()==false) {
            if($this->getUser()->getId() == $tournament->getCreatorUser()->getId()) {
                if(date("now") < $tournament->getStartDate()) {
                    $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
                    $totalPoints=0;
                    foreach($this->getUser()->getPoints() as $points) {
                        $totalPoints+=$points->getAmount();
                    }
                    $form = $this->createForm(TournamentNoPlacesType::class, $tournament);
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $this->getDoctrine()->getManager()->flush();

                        return $this->redirect('/seetournament/'.$id);
                    }
                    return $this->render('main/edittournament.html.twig', [
                        'tournament' => $tournament,
                        'form' => $form->createView(),
                        'unseen' => $unseen,
                        'totalPoints' => $totalPoints,
                    ]);
                } else {
                    return new Response("You can't delete a Tournament after the start date.");
                }
            } else {
                return new Response("You can't delete this Tournament because you're not the creator user.");
            }
        } else {
            return new Response("You can't delete an already deleted Tournament.");
        }
    }

    #[Route('/deletetournamentajax/{id}', name: 'deletetournamentajax', methods: ['GET'])]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function deleteTournamentAjax(Request $request,$id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        if($tournament->getHidden()==false) {
            if($this->getUser()->getId() == $tournament->getCreatorUser()->getId()) {
                if(date("now") < $tournament->getStartDate()) {
                    $tournament->setHidden(true);
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    return $this->redirect("/");
                } else {
                    return new Response("You can't delete a Tournament after the start date.");
                }
            } else {
                return new Response("You can't delete this Tournament because you're not the creator user.");
            }
        } else {
            return new Response("You can't delete an already deleted Tournament.");
        }
        
    }

    #[Route('/seeallvideogames', name: 'seeallvideogames')]
    public function seeAllVideoGames(): Response
    {
        $videogames=$this->getDoctrine()->getRepository(VideoGame::class)->findAll();
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
        return $this->render('main/seeallvideogames.html.twig', [
            'videogames' => $videogames,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    // #[Route('/seevideogame/{id}', name: 'seevideogame')]
    // public function seeVideoGame($id): Response
    // {
    //     $videogame=$this->getDoctrine()->getRepository(VideoGame::class)->find($id);
    //     if($this->getUser()) {
    //         $unseen=$this->getDoctrine()->getRepository(Notification::class)->findAllUnseenOfUser($this->getUser());
    //         $totalPoints=0;
    //         foreach($this->getUser()->getPoints() as $points) {
    //             $totalPoints+=$points->getAmount();
    //         }
    //     } else {
    //         $unseen = null;
    //         $totalPoints = null;
    //     }
    //     return $this->render('main/seevideogame.html.twig', [
    //         'videogame' => $videogame,
    //         'unseen' => $unseen,
    //         'totalPoints' => $totalPoints,
    //     ]);
    // }

    #[Route('/globalranking', name: 'globalranking')]
    public function globalRanking(): Response
    {
        $allProvinces=$this->getDoctrine()->getRepository(Province::class)->findAll();
        $allVideoGames=$this->getDoctrine()->getRepository(VideoGame::class)->findAll();
        $rankingUsers = $this->getDoctrine()->getRepository(User::class)->globalRanking();
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
        return $this->render('main/globalranking.html.twig', [
            'rankingUsers' => $rankingUsers,
            'allProvinces' => $allProvinces,
            'allVideoGames' => $allVideoGames,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/ranking/{provinceId}&{videogameId}&{year}', name: 'ranking')]
    public function ranking($provinceId,$videogameId,$year): Response
    {
        $allProvinces=$this->getDoctrine()->getRepository(Province::class)->findAll();
        $allVideoGames=$this->getDoctrine()->getRepository(VideoGame::class)->findAll();
        // var_dump("TODO:".$provinceId==null && $videogameId==null && $year==null);
        // var_dump($year=="null");
        // var_dump($videogameId=="null");
        // var_dump($provinceId=="null");
        // exit;
        if($provinceId=="null"&&$videogameId=="null"&&$year=="null") {
            return $this->redirectToRoute("globalranking");
        } else {
            $province=$this->getDoctrine()->getRepository(Province::class)->find($provinceId);
            $videogame=$this->getDoctrine()->getRepository(VideoGame::class)->find($videogameId);
            $rankingUsers = $this->getDoctrine()->getRepository(User::class)->ranking($province,$videogame,$year);
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
        return $this->render('main/ranking.html.twig', [
            'province' => $province,
            'videogame' => $videogame,
            'year' => $year,
            'allProvinces' => $allProvinces,
            'allVideoGames' => $allVideoGames,
            'rankingUsers' => $rankingUsers,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    

}
