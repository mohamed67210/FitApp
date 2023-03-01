<?php

namespace App\Controller;

use App\Entity\Programme;
use App\Form\ProgrammeType;
use App\Repository\ProgrammeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgrammeController extends AbstractController
{
    //ajouter une session ou editer
    #[Route('/programme/edit/{id}', name: 'edit_programme')]
    #[Route('/programme/add', name: 'add_programme')]
    public function add(ManagerRegistry $doctrine, Programme $programme = null, Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COACH');
        if (!$programme) {
            $programme = new programme;
        }
        // construire un formulaire qui va se baser sur le $builder dans ProgrammeType
        $form = $this->createForm(ProgrammeType::class, $programme);
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
                        $this->getParameter('programmeImage_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $e->getMessage('erreur');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $programme->setImage(
                    $newFilename
                );
            }

            // recuperer les données de programme si il existe deja et si il est nul
            $programme = $form->getData();

            // on recupere le managere doctrine
            $entityManager = $doctrine->getManager();

            // persist remplace prepare en pdo , on prepare l'objet Programmme 
            $entityManager->persist($programme);

            //on execute 
            $entityManager->flush();
            $this->addFlash('success', 'Le Programme est enregistré !');

            // on  retourne vers la page accueil
            return $this->redirectToRoute('app_home');
        }
        return $this->render('programme/formulaire.html.twig', [
            'formProgramme' => $form->createView()
        ]);
    }

    #[Route('/programme', name: 'app_programme')]
    public function index(ProgrammeRepository $programmeRepository): Response
    {
        return $this->render('programme/allProgrammes.html.twig', [
            'controller_name' => 'ProgrammeController',
        ]);
    }
}
