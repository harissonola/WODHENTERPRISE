<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminTransactController extends AbstractController
{
    #[Route('/admin/transactions', name: 'app_admin_transactions')]
    public function index(
        TransactionRepository $transactionRepository
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $transactions = $transactionRepository->findAll();

        return $this->render('admin/transactions/index.html.twig', [
            'transactions' => $transactions,
        ]);
    }
}
