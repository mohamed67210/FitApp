<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Programme;
use App\Form\CommandeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            if ($user->getRoles() == 'ROLE_USER') {
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
                $this->addFlash('error', "Nous sommes désolé car vous ne pouvez pas commander tant que vous n'etes pas connecté entant que simple client !");
                return $this->redirectToRoute('show_programme', ['id' => $programme->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_login");
        }
    }
}