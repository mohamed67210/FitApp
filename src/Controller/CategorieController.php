<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categorie/{id}', name: 'categorie_programmes')]
    public function index(CategorieRepository $categorieRepository, Request $request): Response
    {
        // chercher la categorie avec le id 
        $id =  $request->attributes->get('_route_params');
        $oneCategorie = $categorieRepository->findOneBy(['id' => $id]);
        return $this->render('categorie/categorieProgrammes.html.twig', [
            'categorie' => $oneCategorie,
        ]);
    }
}
