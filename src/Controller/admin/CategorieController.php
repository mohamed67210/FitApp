<?php

namespace App\Controller\admin;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories', name: 'admin_categorie_')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findBy([], ['id' => 'asc']);
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    #[Route('/add', name: 'add')]
    public function addCategorie(ManagerRegistry $doctrine, Request $request, Categorie $categorie = null, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $categorie = new Categorie;
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // upload image
            $uploadedFile = $form['image']->getData();
            // dd($uploadedFile);
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
                // dd($newFilename);

                // Move the file to the directory where Programme images are stored
                try {
                    $uploadedFile->move(
                        $this->getParameter('categorieImage_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload

                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $categorie->setImage(
                    $newFilename
                );
            }

            $categorie = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'categorie est enregistré !');
            return $this->redirectToRoute('admin_categorie_index');
        }
        return $this->render('admin/categories/index.html.twig', [

            'CategorieForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function editCategorie(CategorieRepository $categorieRepository, ManagerRegistry $doctrine, Request $request, Categorie $categorie, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $categories = $categorieRepository->findBy([], ['id' => 'asc']);
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // upload image
            $uploadedFile = $form['image']->getData();
            // dd($uploadedFile);
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
                // dd($newFilename);

                // Move the file to the directory where Programme images are stored
                try {
                    $uploadedFile->move(
                        $this->getParameter('categorieImage_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload

                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $categorie->setImage(
                    $newFilename
                );
            }
            $categorie = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash('success', "Vous venez d'editer la categorie : " . $categorie->getIntitule() . " !");
            return $this->redirectToRoute('admin_categorie_index');
        }
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
            'CategorieForm' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function deleteCategorie(ManagerRegistry $doctrine, Categorie $categorie): Response
    {
        // supprission d'imade de dossier image
        $image = $categorie->getImage();
        if ($image) {
            // le chemin de l'image
            $nomImage = $this->getParameter('categorieImage_directory') . '/' . $image;
            // verifier si le file existe dans le dossier
            if (file_exists($nomImage)) {
                unlink($nomImage);
            }
        }

        $entityManager = $doctrine->getManager();
        $categorie =  $entityManager->getRepository(Categorie::class)->remove($categorie);
        $entityManager->flush();
        $this->addFlash('success', 'La categorie est supprimé !');
        return  $this->redirectToRoute('admin_categorie_index');
    }
}
