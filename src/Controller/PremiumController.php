<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PremiumController extends AbstractController
{
    #[Route('/premium', name: 'app_premium')]
    public function index(): Response
    {
        return $this->render('premium/index.html.twig', [
            'controller_name' => 'PremiumController',
        ]);
    }
}
