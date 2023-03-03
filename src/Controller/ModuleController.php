<?php

namespace App\Controller;

use App\Repository\ModuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModuleController extends AbstractController
{
    #[Route('/modules/{id}', name: 'programme_modules')]
    public function programmeModules($id,ModuleRepository $moduleRepository): Response
    {
        $programmeModules = $moduleRepository->findBy(['programme'=>$id]);
        // dd($programmeModules);
        return $this->render('module/index.html.twig', [
            'modules' => $programmeModules,
        ]);
    }
}
