<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiplomeController extends AbstractController
{
    #[Route('/diplome/add/{id}', name: 'add_diplome')]
    public function addDiplome(): Response
    {
        
        return $this->render('diplome/index.html.twig', [
            'controller_name' => 'DiplomeController',
        ]);
    }
}
