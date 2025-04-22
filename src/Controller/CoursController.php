<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CoursController extends AbstractController
{
    #[Route('/formations/{slug}/cours', name: 'app_cours')]
    public function index(
        $slug,
        FormationRepository $formationRepository
    ): Response
    {
        $formation = $formationRepository->findOneBy(['slug' => $slug]);
        $date = new DateTimeImmutable();
        return $this->render('cours/index.html.twig', [
            'controller_name' => 'CoursController',
            'formation' => $formation,
            'date' => $date,
        ]);
    }
}
