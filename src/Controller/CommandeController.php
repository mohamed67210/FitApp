<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Programme;
use App\Form\CommandeType;
use Doctrine\Persistence\ManagerRegistry;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/commande/nouveau/{id}', name: 'add_commande')]
    public function addCommande(ManagerRegistry $doctrine, Request $request, Programme $programme): Response
    {
        $user = $this->getUser();
        if ($user) {
            // dd($user->getRoles());
            if ($user->getRoles()[0] == 'ROLE_USER') {
                // dd($user->getRoles());
                $commande = new Commande();
                $form = $this->createForm(CommandeType::class, $commande);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $commande = $form->getData();
                    $commande->setUser($this->getUser());
                    $commande->setProgramme($programme);
                    $commande->setMontant($programme->getPrix());
                    // Créez un objet DateTime
                    $dateTime = new \DateTime();
                    // Convertissez l'objet DateTime en objet DateTimeImmutable
                    $dateTimeImmutable = \DateTimeImmutable::createFromMutable($dateTime);
                    // Utilisez l'objet DateTimeImmutable dans la méthode setCreateAt
                    $commande->setCreateAt($dateTimeImmutable);
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($commande);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_home');
                }
                return $this->render('commande/index.html.twig', [
                    'CommandeForm' => $form->createView(),
                ]);
            } else {
                // dd($programme->getId());
                $this->addFlash('message', "Nous sommes désolé car vous ne pouvez pas commander tant que vous n'etes pas connecté entant que simple client !");
                return $this->redirectToRoute('show_programme', ['id' => $programme->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_login");
        }
    }
    #[Route('/paeiment/{id}', name: 'paeiment')]
    public function startPayment(Programme $programme): Response
    {
        $user = $this->getUser();
        if ($user) {
            if ($user->getRoles()[0] == 'ROLE_USER') {
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
        } else {
            $this->addFlash('message', "vous devrez vous connecté pour pouvoir acheter un programme ");
            return $this->redirectToRoute('app_login');
        }
    }
}
