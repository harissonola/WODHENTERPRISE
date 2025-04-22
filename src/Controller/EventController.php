<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(
        EventRepository $eventRepository
    ): Response
    {
        $events = $eventRepository->findAll();
        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
            'events' => $events
        ]);
    }

    #[Route('/event/{slug}', name: 'app_event_details')]
    public function details(
        $slug,
        EventRepository $eventRepository
    ): Response
    {
        $event = $eventRepository->findOneBy(['slug' => $slug]);
        
        return $this->render('event/details.html.twig', [
            'controller_name' => 'EventController',
            'event' => $event,
        ]);
    }
}
