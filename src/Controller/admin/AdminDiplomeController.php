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
    #[Route('/invalide', name: 'invalide')]
    public function index(DiplomeRepository $diplomeRepository): Response
    {
        $diplomes = $diplomeRepository->findBy(['isVerified' => false], ['id' => 'asc']);
        return $this->render('admin/diplomes/index.html.twig', [
            'diplomes' => $diplomes,
        ]);
    }

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
}
