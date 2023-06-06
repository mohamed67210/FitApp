<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Programme;
use App\Form\CommandeType;
use App\Repository\ProgrammeRepository;
use App\Repository\UserRepository;
use DateTime;
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
  
    //paeiment d'un seul programme sans passer par panier session
    #[Route('/paeiment/{id}', name: 'paeiment')]
    public function startPayment(Programme $programme = null,UserRepository $userRepository): Response
    {
        if ($programme && ($programme->isIsValid())== true) {
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
                            $prix = $programme->getPrix() ;
                        } else {
                            $prix = $programme->getPrixPromo() ;
                        }
                        $prixEnCentimes = (int) round($prix * 100);
                        Stripe::setApiKey('sk_test_51MyBkxH7jmQ7y8JFjhlj5nkQbrcZlmFYQTuIJ1s8wjxBbm2U8oy9MzpfT3I7b437smvqQYR9pvKPdpuKAeOlxlT400XvRAT6Yc');
                        $session = Session::create([
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => 'EUR',
                                        'product_data' => [
                                            'name' => $programme->getIntitule(),
                                        ],
                                        'unit_amount' => $prixEnCentimes
                                    ],
                                    'quantity' => 1,
                                ]
                            ],
                            'mode' => 'payment',
                            'success_url' => 'http://127.0.0.1:8000/validate/'.$programme->getId().'',
                            'cancel_url' => 'http://127.0.0.1:8000/programme/'.$programme->getId().'',
                            'billing_address_collection' => 'required',
                            "customer_email" => $user->getEmail(),
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

    // --------------------paeiment de un ou plusieurs programmes a partir du panier en utilisant session
    #[Route('/paeiment', name: 'panier_paeiment')]
    public function PanierPaiement(SessionInterface $session,ProgrammeRepository $programmeRepository):Response
    {
        //recuperer la session panier
        $montantTotal = 0;
        $panier = $session->get('panier',[]);
        foreach ($panier as $programmeId) {
            $programme = $programmeRepository->find($programmeId);
            if ($programme->getPrixPromo() == null) {
                $prixProgramme = $programme->getPrix();
            }else{
                $prixProgramme = $programme->getPrixPromo();
            }
            // dd($programme);
            $montantTotal += $prixProgramme;
        // dd($montantTotal);

        }
        $montantEnCentimes = (int) round($montantTotal * 100);
        Stripe::setApiKey('sk_test_51MyBkxH7jmQ7y8JFjhlj5nkQbrcZlmFYQTuIJ1s8wjxBbm2U8oy9MzpfT3I7b437smvqQYR9pvKPdpuKAeOlxlT400XvRAT6Yc');
        $session = Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => 'le montant total de votre commande est :',
                        ],
                        'unit_amount' => $montantEnCentimes 
                    ],
                    'quantity' => 1,
                ]
            ],
            // dd($programme),
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/validate',
            'cancel_url' => 'http://127.0.0.1:8000/',
            'billing_address_collection' => 'required',
            "metadata" => [
                'programme_id' => $programme,
            ]
        ]);
        return $this->redirect($session->url);
    }

    // creer la commande apres la validation de peiement par stripe
    #[Route('/validate/{id}', name: 'app_validate')]
    public function createCommande(Programme $programme = null,ManagerRegistry $doctrine,Commande $commande = null,UserRepository $userRepository): Response
    {
        if ($this->getUser()) {
            if ($programme) {
                // recuperer le prix du programme
                if ($programme->getPrixPromo() == null) {
                    $montant = $programme->getPrix();
                }
                else{
                    $montant = $programme->getPrixPromo();
                }
                // recuperer l'adresse de user
                $user = $userRepository->findOneBy(['id'=>$this->getUser()]);
                $email = $user->getEmail();
                $commande = new Commande();
                $commande->setUser($this->getUser());
                $commande->setProgramme($programme);
                $commande->setAdresseFacturation($email);
                $commande->setMontant($montant);
                $commande->setCreateAt(new \DateTime());
                
                $entityManager = $doctrine->getManager();
                $entityManager->persist($commande);
                $entityManager->flush();
                $this->addFlash('message','Félécitaion vous venez de vous inscrire a un nouveau programme ! ');
                return $this->redirectToRoute('show_profile');
            } else {
                return $this->render('programme/allProgrammes.html.twig', [
                    'controller_name' => 'ProgrammeController',
                ]);
            }
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }

    // creation de la commande en validant le stipe passant par session
    #[Route('/validate',name:('app_validate_session'))]
    public function createCommandSession(SessionInterface $session,ProgrammeRepository $programmeRepository,ManagerRegistry $doctrine,Commande $commande = null):Response
    {
        $userConnect =$this->getUser();
        if ($userConnect) {
            // recuperer panier et verifier si elle n'est pas vide
            $panier = $session->get('panier',[]);
            foreach ($panier as $programmeId) {
                $programme =$programmeRepository->findOneBy(['id'=>$programmeId]);
                // recuperer le prix du programme
                if ($programme->getPrixPromo() == null) {
                    $montant = $programme->getPrix();
                }
                else{
                    $montant = $programme->getPrixPromo();
                }
                // dd($programme);
                $commande = new Commande;
                $commande->setProgramme($programme);
                $commande->setCreateAt(new \DateTime());
                $commande->setUser($userConnect);
                $commande->setAdresseFacturation('hello@test.fr');
                $commande->setMontant($montant);
                
                $entityManager = $doctrine->getManager();
                $entityManager->persist($commande);
                $entityManager->flush();
            }
            $session->clear();
            $this->addFlash('message','votre commande est bien validé, merci ! ');
            return $this->redirectToRoute('show_profile');

        }
        else {
        return $this->redirectToRoute('app_login');
        }
    }
}
