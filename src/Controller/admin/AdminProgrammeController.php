<?php

namespace App\Controller\admin;

use App\Entity\Programme;
use App\Repository\ProgrammeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/programmes', name: 'admin_programme_')]
class AdminProgrammeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProgrammeRepository $programmeRepository): Response
    {
        $programmes = $programmeRepository->findBy([], ['id' => 'DESc']);
        return $this->render('admin/programmes/index.html.twig', [
            'programmes' => $programmes,
        ]);
    }

    #[Route('/active/{id}', name: 'active')]
    public function activeProgramme(ManagerRegistry $doctrine, Programme $programme, ProgrammeRepository $programmeRepository): Response
    {
        $programme->setisValid(true);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($programme);
        $entityManager->flush();
        $programmes = $programmeRepository->findBy([], ['id' => 'DESC']);
        $this->addFlash("success", "vous venez d'activé le programme : " . $programme->getIntitule() . " !");
        return $this->render('admin/programmes/index.html.twig', [
            'programmes' => $programmes,
        ]);
    }
    #[Route('/desactive/{id}', name: 'desactive')]
    public function desactiveProgramme(ManagerRegistry $doctrine, Programme $programme, ProgrammeRepository $programmeRepository): Response
    {
        $programme->setisValid(false);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($programme);
        $entityManager->flush();
        $programmes = $programmeRepository->findBy([], ['id' => 'DESC']);
        $this->addFlash('success', 'vous venez de desactivé le programme : ' . $programme->getIntitule() . ' !');

        return $this->render('admin/programmes/index.html.twig', [
            'programmes' => $programmes,
        ]);
    }
}
