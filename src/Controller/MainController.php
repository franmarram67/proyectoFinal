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
    public function signUpToTournament($id): Response
    {
        try {
            $tournament=$this->getDoctrine()->getRepository(Tournament::class)->find($id);
            if($this->getUser()) {
                if($this->getUser()->getVerified() == true) {
                    if($this->getUser()->getId() != $tournament->getCreatorUser()->getId()) {
                        if(date() < $tournament->getStartDate()) {
                            if(count($tournament->getPlayers()) < 20) {
                                $entityManager = $this->getDoctrine()->getManager();
                                $tournament->addPlayer($this->getUser());
                                $entityManager->flush();
                                return $this->redirectToRoute('main');
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
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /*var_dump($form->get('plainPassword')->getData());
            exit;*/
            // encode the plain password
            if($form->get('plainPassword')->getData()!=null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

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

            $email = $form->get('email')->getData();
            $dni = $form->get('dni')->getData();
            $name = $form->get('name')->getData();
            $surname = $form->get('surname')->getData();
            $province = $form->get('province')->getData();

            if($email) {
                $user->setEmail($email);
            }
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
