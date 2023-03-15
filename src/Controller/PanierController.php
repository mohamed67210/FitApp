<?php

namespace App\Controller;

use App\Repository\ProgrammeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'show_panier')]
    public function index(SessionInterface $session, ProgrammeRepository $programmeRepository): Response
    {
        $id = 25;
        $panier = $session->get('panier', []);
        $panierWithData = [];
        foreach ($panier as $id => $quentity) {
            $panierWithData[] = [
                'programme' => $programmeRepository->find($id),
                'qt' => $quentity
            ];
        }

        dd($panierWithData);
    }

    // ajouter un programme dans le panier passant par la session
    #[Route('/panier/add/{id}', name: 'add_panier')]
    public function AddToPanier($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        $panier[$id] = 1;
        $session->set('panier', $panier);
        // dd(count($session->get('panier')));
        return $this->redirectToRoute('show_programme', ['id' => $id]);
    }
}
