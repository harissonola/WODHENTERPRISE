<?php

namespace App\Controller;

use App\Entity\Premium;
use App\Repository\FormationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formations', name: 'app_formations_')]
class FormationsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        FormationRepository $formationRepository,
        EntityManagerInterface $em
    ): Response
    {
        $formations = $formationRepository->findAll();

        $date = new DateTimeImmutable();

        return $this->render('formations/index.html.twig', [
            'controller_name' => 'FormationsController',
            'formations' => $formations,
            'date' => $date,
        ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(
        $slug,
        FormationRepository $formationRepository,
    ): Response
    {

        $date = new DateTimeImmutable();

        $user = $this->getUser();

        $formation = $formationRepository->findOneBy(['slug' => $slug]);

        

        return $this->render('formations/details.html.twig', [
            'controller_name' => 'FormationsController',
            'formation' => $formation,
            'date' => $date,
        ]);
    }
}
