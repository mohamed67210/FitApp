<?php

namespace App\Controller\admin;

use App\Entity\Diplome;
use App\Repository\DiplomeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/diplomes', name: 'admin_diplomes_')]
class AdminDiplomeController extends AbstractController
{
    // ----------------- afficher les diplomes non valider
    #[Route('/invalide', name: 'invalide')]
    public function index(DiplomeRepository $diplomeRepository): Response
    {
        $diplomes = $diplomeRepository->findBy(['isVerified' => false], ['id' => 'asc']);
        return $this->render('admin/diplomes/index.html.twig', [
            'diplomes' => $diplomes,
        ]);
    }

    // ----------------- activer les diplomes non valider
    #[Route('/activer/{id}', name: 'activer')]
    public function activerDiplome(ManagerRegistry $doctrine,DiplomeRepository $diplomeRepository,Diplome $diplome): Response
    {
        $diplome->setIsVerified(true);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($diplome);
        $entityManager->flush();
        $diplomes = $diplomeRepository->findBy(['isVerified' => false], ['id' => 'asc']);
        return $this->render('admin/diplomes/index.html.twig', [
            'diplomes' => $diplomes,
        ]);
    }

     // ----------------- supprimer les diplomes non valider
     #[Route('/supprimer/{id}', name: 'supprimer')]
     public function supprimerDiplome(Diplome $diplome = null,DiplomeRepository $diplomeRepository,ManagerRegistry $doctrine):Response
     {
        if ($diplome) {
            $image = $diplome->getImage();
            $diplomeRepository->remove($diplome);
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
            // supprission d'image de dossier image
            if ($image) {
            // le chemin de l'image
            $nomImage = $this->getParameter('diplomeImage_directory') . '/' . $image;
            // verifier si le file existe dans le dossier
            if (file_exists($nomImage)) {
                unlink($nomImage);
            }
        }
            $this->addFlash('message',"supprission validée");
            return $this->redirectToRoute('admin_diplomes_invalide');
        }
        else {
            $this->addFlash('message',"Accés refusé");
            return $this->redirectToRoute('admin_diplomes_invalide');

        }
        
     }
}
