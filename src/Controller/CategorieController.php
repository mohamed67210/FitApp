<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    // afficher les programmes d'une categorie
    #[Route('/categorie/{id}', name: 'categorie_programmes')]
    public function index(CategorieRepository $categorieRepository, Request $request): Response
    {
        // chercher la categorie avec le id 
        $id =  $request->attributes->get('_route_params');
        $categorie = $categorieRepository->findOneBy(['id' => $id]);
        if ($categorie) {
            $programmes = $categorie->getProgrammes();
            if (count($programmes)>0) {
                return $this->render('categorie/categorieProgrammes.html.twig', [
                    'categorie' => $categorie,
                ]);
            }
            else{
                $this->addFlash('message',"Désolé cette catégorie n'as pas de programme pour le moment");
                return $this->redirectToRoute('app_home');
            }
        }
        else{
            $this->addFlash('message',"accés refusé !");
            return $this->redirectToRoute('app_home');
        }
        
    }
}
