<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/events', name: 'app_admin_events_')]
class AdminEventsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        EventRepository $eventRepository
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        
        $events = $eventRepository->findAll();

        return $this->render('admin/events/index.html.twig', [
            'controller_name' => 'AdminEventsController',
            'events' => $events,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Génération du slug
            if (!empty($form->get('name')->getData()) && empty($form->get('slug')->getData())) {
                $slugger = new AsciiSlugger();
                $event->setSlug($slugger->slug($form->get('name')->getData())->lower());
            }

            $createdAt = new DateTimeImmutable();

            $event->setCreatedAt($createdAt);

            // Gestion des images
            $image = $form->get('image')->getData();
            if ($image) {

                $newFilename = uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('events_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin_events_add');
                }

                $event->setImage($newFilename);

                $em->persist($event);
                $em->flush();
            }

            $this->addFlash("success", "Evenement ajouter avec succes.");
            return $this->redirectToRoute("app_admin_events_index");
        }


        return $this->render('admin/events/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(
        $id,
        Request $request,
        EntityManagerInterface $em,
        EventRepository $eventRepository
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $event = $eventRepository->find($id);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Génération du slug
            if (!empty($form->get('name')->getData()) && $form->get('slug')->getData()) {
                $slugger = new AsciiSlugger();
                $event->setSlug($slugger->slug($form->get('name')->getData())->lower());
            }

            // Gestion des images
            $image = $form->get('image')->getData();
            if ($image) {
                $newFilename = uniqid() . '.' . $image->guessExtension();
                try {
                    $image->move(
                        $this->getParameter('formations_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin_events_edit', ['id' => $event->getImage()]);
                }
                $event->setImage($newFilename);
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash("success", "Evenement modifiée avec succès.");
            return $this->redirectToRoute("app_admin_events_index");
        }

        return $this->render('admin/events/add.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }



    #[Route('/delete/{id}', name: 'delete')]
    public function delete(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        // Récupérer le event à partir de son ID
        $event = $eventRepository->find($id);

        // Vérifier si le event existe
        if (!$event) {
            $this->addFlash('error', 'Evenement non trouvé.');
            return $this->redirectToRoute('app_admin_events_index');
        }


        // Supprimer les images associées (si nécessaire)
        if ($event->getImage()) {
            $imagePath = $this->getParameter('events_img_directory') . '/' . $event->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath); // Supprime le fichier image
            }
        }

        // Supprimer le event
        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'Evenement supprimé avec succès.');
        return $this->redirectToRoute('app_admin_events_index');
    }
}
