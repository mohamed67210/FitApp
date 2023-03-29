<?php

namespace App\Controller\admin;

use App\Repository\ProgrammeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/programmes', name: 'admin_programme_')]
class AdminProgrammeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProgrammeRepository $programmeRepository): Response
    {
        $programmes = $programmeRepository->findBy([], ['id' => 'asc']);
        return $this->render('admin/programmes/index.html.twig', [
            'programmes' => $programmes,
        ]);
    }
}
