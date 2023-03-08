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
        $role = null;
        $programmes = $programmeRepository->findAll();
        $coachs = $userRepository->findByRole('ROLE_COACH');
        $membres = $userRepository->findByRole('[]');
        $categories = $categorieRepository->findBy([], ['id' => 'asc']);
        return $this->render('home/index.html.twig', [
            'categories' => $categories,
            'coachs' => $coachs,
            'membres' => $membres,
            'programmes' => $programmes
        ]);
    }
}
