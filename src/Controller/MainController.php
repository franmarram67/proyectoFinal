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

use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use App\Form\ChangeEmailType;
use App\Form\FinishTournamentType;

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
                                if(count($tournament->getPlayers()) < 20) {
                                    
                                    $entityManager = $this->getDoctrine()->getManager();
                                    $tournament->addPlayer($this->getUser());
                                    $entityManager->flush();
                                    //return $this->redirectToRoute('main');

                                    $request->getSession()->getFlashBag()->add('notice','success');
                                    $referer = $request->headers->get('referer');
                                    return $this->redirect($referer);
                                    
                                    
                                } else {
                                    return new Response("You can't Sign Up to this Tournament because there's no more places left. Max 20 players per tournament.");
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
                'tournament' => $tournament,
                'unseen' => $unseen,
                'totalPoints' => $totalPoints,
            ]);
        } else {
            return new Response("You can't see this tournament because it has been deleted.");
        }
        
    }

    #[Route('/seeprovince/{id}', name: 'seeprovince')]
    public function seeProvince($id): Response
    {
        $province=$this->getDoctrine()->getRepository(Province::class)->find($id);
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
        return $this->render('main/seeprovince.html.twig', [
            'province' => $province,
            'unseen' => $unseen,
            'totalPoints' => $totalPoints,
        ]);
    }

    #[Route('/mypoints', name: 'mypoints')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function myPoints(): Response
    {
        $points=$this->getDoctrine()->getRepository(Points::class)->findAllOrderedByDatetime($this->getUser());
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
            'points' => $points,
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

    #[Route('/seeallprovinces', name: 'seeallprovinces')]
    public function seeAllProvinces(): Response
    {
        $provinces=$this->getDoctrine()->getRepository(Province::class)->findAll();
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
        return $this->render('main/seeallprovinces.html.twig', [
            'provinces' => $provinces,
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

                                $pointsFirst->setUser($firstPlace);
                                $pointsFirst->setDatetime(new \DateTime);
                                $pointsFirst->setAmount(500);
                                $pointsFirst->setTournament($tournament);

                                $pointsSecond->setUser($secondPlace);
                                $pointsSecond->setDatetime(new \DateTime);
                                $pointsSecond->setAmount(350);
                                $pointsSecond->setTournament($tournament);

                                $pointsThird->setUser($thirdPlace);
                                $pointsThird->setDatetime(new \DateTime);
                                $pointsThird->setAmount(200);
                                $pointsThird->setTournament($tournament);

                                $pointsFourth->setUser($fourthPlace);
                                $pointsFourth->setDatetime(new \DateTime);
                                $pointsFourth->setAmount(100);
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
                                        $notification->setText("<h4><a href='/seetournament/".$tournament->getId()."'>".$tournament->getTitle()."</a> - by ".$tournament->getCreatorUser()->getUsername()."</h4><p>You won the first place!!! You are rewarded 500 points.</p>");
                                    } else if($player->getId()==$secondPlace->getId()) {
                                        $notification->setText("<h4><a href='/seetournament/".$tournament->getId()."'>".$tournament->getTitle()."</a> - by ".$tournament->getCreatorUser()->getUsername()."</h4><p>You won the second place!!! You are rewarded 350 points.</p>");
                                    } else if($player->getId()==$thirdPlace->getId()) {
                                        $notification->setText("<h4><a href='/seetournament/".$tournament->getId()."'>".$tournament->getTitle()."</a> - by ".$tournament->getCreatorUser()->getUsername()."</h4><p>You won the third place!!! You are rewarded 200 points.</p>");
                                    } else if($player->getId()==$fourthPlace->getId()) {
                                        $notification->setText("<h4><a href='/seetournament/".$tournament->getId()."'>".$tournament->getTitle()."</a> - by ".$tournament->getCreatorUser()->getUsername()."</h4><p>You won the fourth place!!! You are rewarded 100 points.</p>");
                                    } else {
                                        $notification->setText("<h4><a href='/seetournament/".$tournament->getId()."'></a> - by ".$tournament->getCreatorUser()->getUsername()."</h4><p>You didn't win this time... ;(. Try again next time. You got this!!!</p>");
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

    #[Route('/deletetournament/{id}', name: 'deletetournament')]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function deleteTournament($id): Response
    {
        $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
        if($tournament->getHidden()==false) {
            if($this->getUser()->getId() == $tournament->getCreatorUser()->getId()) {
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
                    return $this->render('main/deletetournament.html.twig', [
                        'tournament' => $tournament,
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
                if(date("now") > $tournament->getStartDate()) {
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

    

}
