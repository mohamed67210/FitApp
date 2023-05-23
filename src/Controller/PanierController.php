<?php

namespace App\Controller;

use App\Entity\Programme;
use App\Repository\ProgrammeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

class PanierController extends AbstractController
{
    //afficher le panier
    /* #[Route('/panier', name: 'show_panier')]
    public function index(SessionInterface $session, ProgrammeRepository $programmeRepository): Response
    {
        $id = 25;
        $cart = $session->get('panier', []);
        dd($cart);
        $panierWithData = [];
        // recuperer les programme du pannier
        foreach ($cart as $id => $quentity) {
            $panierWithData[] = [
                'programme' => $programmeRepository->find($id),
                'qt' => $quentity
            ];
        }
        dd($panierWithData);
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
    }*/
    // afficher le panier dans la vue
    #[Route('/panier', name: 'show_panier')]
    public function index(SessionInterface $session, ProgrammeRepository $programmeRepository): Response
    {
        //recuperer des programmes du panier
        $panier = $session->get('panier',[]);
        $dataPanier= [];
        $total = 0;
        foreach ($panier as $id => $quentite) {
            $programme = $programmeRepository->find($id);
            $dataPanier[] = [
                'programme'=>$programme,
                'quentite'=> $quentite
            ];
            //si ya un prix promo
            if($programme->getPrixPromo() != null){
                $programmePrix = $programme->getPrixPromo();
            }
            else{
                $programmePrix = $programme->getPrix();
            }
            $total += $programmePrix;
        }
        return $this->render('panier/index.html.twig',compact('dataPanier','total'));
    }
    // ajouter un programme dans le panier passant par la session
    #[Route('/panier/add/{id}', name: 'add_panier')]
    public function AddToPanier(Programme $programme, SessionInterface $session): Response
    {
            if ($this->getUser()) {
                if ($this->getUser()->getroles()[0] == 'ROLE_USER') {
                    $id = $programme->getId();
                    $panier = $session->get('panier',[]);
                    if (!empty($panier[$id])) {
                        $this->addFlash('message','ce programme est deja dans votre panier !');
                        return $this->redirectToRoute('show_programme',['id'=>$id]);
                    }else{
                        $panier[$id]= $id;
                    }
                    //sauvegarder le panier dans la session
                    $session->set('panier',$panier);
                    return $this->redirectToRoute('show_panier');
                } else {
                    $this->addFlash('message', "Nous sommes désolé ! vous n'etes pas client donc vous n'avez pas accés a cette fonctionnalité !");
                    return $this->redirectToRoute('show_panier');
                }
            } else {
                return $this->redirectToRoute('app_login');
            }
        
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
