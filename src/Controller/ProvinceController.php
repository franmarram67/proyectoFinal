<?php

namespace App\Controller;

use App\Entity\Province;
use App\Form\ProvinceType;
use App\Repository\ProvinceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// Cargar imagen
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/province')]
/**
 * @IsGranted("ROLE_ADMIN")
 */
class ProvinceController extends AbstractController
{
    #[Route('/', name: 'province_index', methods: ['GET'])]
    public function index(ProvinceRepository $provinceRepository): Response
    {
        return $this->render('province/index.html.twig', [
            'provinces' => $provinceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'province_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $province = new Province();
        $form = $this->createForm(ProvinceType::class, $province);
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
                $province->setImage($newFilename);
            }

            // ... persist the $article variable or any other work y código que estaba

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($province);
            $entityManager->flush();

            return $this->redirectToRoute('province_index');
        }

        return $this->render('province/new.html.twig', [
            'province' => $province,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'province_show', methods: ['GET'])]
    public function show(Province $province): Response
    {
        return $this->render('province/show.html.twig', [
            'province' => $province,
        ]);
    }

    #[Route('/{id}/edit', name: 'province_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Province $province, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProvinceType::class, $province);
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
                $province->setImage($newFilename);
            }

            // ... persist the $article variable or any other work y código que estaba

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('province_index');
        }

        return $this->render('province/edit.html.twig', [
            'province' => $province,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'province_delete', methods: ['POST'])]
    public function delete(Request $request, Province $province): Response
    {
        if ($this->isCsrfTokenValid('delete'.$province->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($province);
            $entityManager->flush();
        }

        return $this->redirectToRoute('province_index');
    }
}
