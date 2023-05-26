<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Programme;
use App\Form\CommandeType;
use App\Repository\ProgrammeRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande/{id}', name: 'app_commande')]
    public function index(Request $request, Programme $programme): Response
    {
        dd($this->getUser());
        //on verifie si ya un user connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute("app_login");
        } else {
            $form = $this->createForm(CommandeType::class, null, ['user' => $this->getUser()]);
            return $this->render('commande/index.html.twig', [
                'CommandeForm' => $form->createView(),
                'programme' => $programme
            ]);
        }
    }

    //paeiment d'un seul programme sans passer par panier session
    #[Route('/paeiment/{id}', name: 'paeiment')]
    public function startPayment(Programme $programme = null,UserRepository $userRepository): Response
    {
        if ($programme) {
            $userConnecte = $this->getUser();
            $user = $userRepository->findOneBy(['id'=> $userConnecte]);
            if ($userConnecte) {
                // verifier si l'user a deja acheter ce programme
                $programmeAcheter = false;
                $commandes = $user->getCommandes();
                foreach ($commandes as $commande) {
                    // dd($commande->getProgramme());
                    if(($commande->getProgramme()) === $programme){
                        $programmeAcheter = true;
                    }
                    else {
                        $programmeAcheter = false;
                    }
                }
                if ($programmeAcheter === false) {
                    if ($userConnecte->getRoles()[0] == 'ROLE_USER') {
                        // recuperer le prix en promos si il existe
                        if ($programme->getPrixPromo() == null) {
                            $prix = $programme->getPrix() . '00';
                        } else {
                            $prix = $programme->getPrixPromo() . '00';
                        }
                        Stripe::setApiKey('sk_test_51MyBkxH7jmQ7y8JFjhlj5nkQbrcZlmFYQTuIJ1s8wjxBbm2U8oy9MzpfT3I7b437smvqQYR9pvKPdpuKAeOlxlT400XvRAT6Yc');
                        $session = Session::create([
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => 'EUR',
                                        'product_data' => [
                                            'name' => $programme->getIntitule(),
                                            // 'images' => $productImageUrl,
                                        ],
                                        'unit_amount' => $prix
                                    ],
                                    'quantity' => 1,
                                ]
                            ],
                            // dd($programme),
                            'mode' => 'payment',
                            'success_url' => 'http://127.0.0.1:8000/stripe/webhook',
                            'cancel_url' => 'http://127.0.0.1:8000/',
                            'billing_address_collection' => 'required',
                            "metadata" => [
                                'programme_id' => $programme,
                            ]
                        ]);
                        return $this->redirect($session->url);
                    } else {
                        $this->addFlash('message', 'vous devrez vous connecter entant que client pour pouvoir acheter un programme');
                        return $this->redirectToRoute('show_programme', ['id' => $programme->getId()]);
                    }
                }
                else {
                    $this->addFlash('message', "vous avez déja acheté ce programme , vous pouvez le retrouver dans votre liste des programmes sur votre espace personelle ");
                    return $this->redirectToRoute('show_programme',['id'=>$programme->getId()]);
                }
                
            } else {
                $this->addFlash('message', "vous devrez vous connecté pour pouvoir acheter un programme ");
                return $this->redirectToRoute('app_login');
            }
        }else{
            $this->addFlash('message','accés refusé ');
            return $this->redirectToRoute('app_home');
        }
        
    }
    #[Route('/paeiment', name: 'panier_paeiment')]
    public function PanierPaiement(SessionInterface $session,ProgrammeRepository $programmeRepository):Response
    {
        //recuperer la session panier
        $montantTotal = 0;
        $panier = $session->get('panier',[]);
        foreach ($panier as $programmeId) {
            $programme = $programmeRepository->find($programmeId);
            if ($programme->getPrixPromo() == null) {
                $prixProgramme = $programme->getPrix().'00';
            }else{
                $prixProgramme = $programme->getPrixPromo().'00';
            }
            // dd($programme);
            $montantTotal += $prixProgramme;
        }
        Stripe::setApiKey('sk_test_51MyBkxH7jmQ7y8JFjhlj5nkQbrcZlmFYQTuIJ1s8wjxBbm2U8oy9MzpfT3I7b437smvqQYR9pvKPdpuKAeOlxlT400XvRAT6Yc');
        $session = Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => 'le montant total de votre commande est :',
                        ],
                        'unit_amount' => $montantTotal 
                    ],
                    'quantity' => 1,
                ]
            ],
            // dd($programme),
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/stripe/webhook',
            'cancel_url' => 'http://127.0.0.1:8000/',
            'billing_address_collection' => 'required',
            "metadata" => [
                'programme_id' => $programme,
            ]
        ]);
        return $this->redirect($session->url);
    }
}
