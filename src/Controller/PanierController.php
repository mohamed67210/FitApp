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
        // recuperer les programme du pannier
        foreach ($panier as $id => $quentity) {
            $panierWithData[] = [
                'programme' => $programmeRepository->find($id),
                'qt' => $quentity
            ];
        }
        // calculer le prix total de tt les programme du pannier
        $total = 0;
        foreach ($panierWithData as $item) {
            $prixProgramme = $item['programme']->getPrix();
            $total += $prixProgramme;
        }
        // dd($panierWithData);
        return $this->render('panier/index.html.twig', [
            "panierItems" => $panierWithData,
            "total" => $total,
        ]);
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

    // suupriemr un programme de la session (panier)
    #[Route('/panier/remove/{id}', name: 'remove_panier')]
    public function remove($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }
        $session->set('panier', $panier);
        // dd(count($session->get('panier')));
        return $this->redirectToRoute('show_panier');
    }
}
