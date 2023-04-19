<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/commande', name: 'admin_commandes_')]
class AdminCommandeController extends AbstractController
{
    #[Route('/', name: 'show')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();
        return $this->render('admin/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
