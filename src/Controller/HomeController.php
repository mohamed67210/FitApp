<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategorieRepository $categorieRepository, UserRepository $userRepository,ProgrammeRepository $programmeRepository): Response
    {
        // recuperer les dernier 5 programmes 
        $lastFive = $programmeRepository->findBy(['isValid' =>true],['id'=>'DESC'],8,0);
        $programmes = $programmeRepository->findAll();
        $coachs = $userRepository->findByRole('ROLE_COACH');
        $membres = $userRepository->findByRole('[]');
        $categories = $categorieRepository->findBy([], ['id' => 'asc']);
        return $this->render('home/index.html.twig', [
            'categories' => $categories,
            'coachs' => $coachs,
            'membres' => $membres,
            'programmes' => $programmes,
            'lastProgrammes' => $lastFive
        ]);
    }

    #[Route('/erreur', name: 'app_404')]
    public function erreurPage():Response
    {
        return $this->render('page_erreur/erreur404.html.twig');
    }
}
