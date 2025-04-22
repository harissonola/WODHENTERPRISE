<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/cours', name: 'app_admin_cours_')]
class AdminCoursController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        CoursRepository $coursRepository,
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $cours = $coursRepository->findAll();

        return $this->render('admin/cours/index.html.twig', [
            'controller_name' => 'AdminCoursController',
            'cours' => $cours,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $cours = new Cours();
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cours->setCreatedAt(new \DateTimeImmutable());

            // dd($form->get('name')->getData());

            // Génération du slug
            if (!empty($form->get('name')->getData())) {
                $slugger = new AsciiSlugger();
                $cours->setSlug($slugger->slug($form->get('name')->getData())->lower());
            }

            // Gestion des images
            $image = $form->get('miniatur')->getData();
            if ($image) {

                $newFilename = uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('cours_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin_formations_add');
                }

                $cours->setMiniatur($newFilename);

                $em->persist($cours);
                $em->flush();
            }

            // Gestion des video
            $video = $form->get('video')->getData();
            if ($video) {

                $newVideoFilename = uniqid() . '.' . $video->guessExtension();

                try {
                    $video->move(
                        $this->getParameter('cours_video_directory'),
                        $newVideoFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la video.');
                    return $this->redirectToRoute('app_admin_cours_add');
                }

                $cours->setVideo($newVideoFilename);

                $em->persist($cours);
                $em->flush();
            }

            $this->addFlash("success", "Cours ajouter avec succes.");
            return $this->redirectToRoute("app_admin_cours_index");
        }


        return $this->render('admin/cours/add.html.twig', [
            'controller_name' => 'AdminCoursController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        Cours $cours
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Génération du slug
            if (!empty($form->get('name')->getData())) {
                $slugger = new AsciiSlugger();
                $cours->setSlug($slugger->slug($form->get('name')->getData())->lower());
            }

            // Gestion des images
            $image = $form->get('miniatur')->getData();
            if ($image) {

                $newFilename = uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('cours_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin_formations_add');
                }

                $cours->setMiniatur($newFilename);

                $em->flush();
            }

            // Gestion des video
            $video = $form->get('video')->getData();
            if ($video) {

                $newVideoFilename = uniqid() . '.' . $video->guessExtension();

                try {
                    $video->move(
                        $this->getParameter('cours_video_directory'),
                        $newVideoFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la video.');
                    return $this->redirectToRoute('app_admin_cours_add');
                }

                $cours->setVideo($newVideoFilename);

                $em->flush();
            }

            $em->flush();

            $this->addFlash("success", "Cours modifiee avec succes.");
            return $this->redirectToRoute("app_admin_cours_index");
        }


        return $this->render('admin/cours/add.html.twig', [
            'controller_name' => 'AdminCoursController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(
        int $id,
        CoursRepository $coursRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        // Récupérer le cours à partir de son ID
        $cours = $coursRepository->find($id);

        // Vérifier si le formation existe
        if (!$cours) {
            $this->addFlash('error', 'Cours non trouvé.');
            return $this->redirectToRoute('app_admin_product');
        }


        // Supprimer les images associées (si nécessaire)
        if ($cours->getMiniatur()) {
            $imagePath = $this->getParameter('cours_img_directory') . '/' . $cours->getMiniatur();
            if (file_exists($imagePath)) {
                unlink($imagePath); // Supprime le fichier image
            }
        }

        // Supprimer les videos associées (si nécessaire)
        if ($cours->getVideo()) {
            $videoPath = $this->getParameter('cours_video_directory') . '/' . $cours->getVideo();
            if (file_exists($videoPath)) {
                unlink($videoPath); // Supprime le fichier image
            }
        }

        // Supprimer le cours
        $entityManager->remove($cours);
        $entityManager->flush();

        $this->addFlash('success', 'Cours supprimé avec succès.');
        return $this->redirectToRoute('app_admin_cours_index');
    }
}
