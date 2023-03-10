<?php

namespace App\Controller;

use App\Entity\Programme;
use App\Entity\User;
use App\Form\ProgrammeType;
use App\Repository\CategorieRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgrammeController extends AbstractController
{
    // afficher detail d'un programme
    #[Route('/programme/{id}', name: 'show_programme')]
    public function showProgramme(ProgrammeRepository $programmeRepository,Programme $programme): Response
    {
        return $this->render('programme/showProgramme.html.twig', [
            'programme' => $programme,
        ]);
    }
    // afficher tout les programmes
    #[Route('/programmes', name: 'show_programmes')]
    public function allProgrammesByCateg(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findBy([], ['id' => 'asc']);
        return $this->render('programme/allProgrammes.html.twig', [
            'categories' => $categories,
        ]);
    }

    // supprission d'un programme
    #[Route('/programme/delete/{id}', name: 'delete_programme')]
    public function deleteProgramme(ManagerRegistry $doctrine, Programme $programme): Response
    {
        // supprission d'imade de dossier image
        $image = $programme->getImage();
        if($image){
            // le chemin de l'image
            $nomImage = $this->getParameter('programmeImage_directory').'/'.$image;
            // verifier si le file existe dans le dossier
            if(file_exists($nomImage)){
                unlink($nomImage);
            }
        }
        $userId = $programme->getCoach()->getId();
        $entityManager = $doctrine->getManager();
        $programme =  $entityManager->getRepository(Programme::class)->remove($programme);
        $entityManager->flush();
        $this->addFlash('success', 'le programme est supprimé !');
        return  $this->redirectToRoute('show_user', ['id' => $userId]);
    }

    //ajouter un programme ou editer
    #[Route('/programme/edit/{id}', name: 'edit_programme')]
    #[Route('/programme/add', name: 'add_programme')]
    // #[IsGranted("ROLE_COACH",message:"vous n'avez pas le droit")]
    public function add(ManagerRegistry $doctrine, Programme $programme = null, Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COACH');
        if (!$programme) {
            $programme = new programme;
        }
        // dd($programme);

        $programme->setCoach($this->getUser());
        $userId = $programme->getCoach()->getId();
        // construire un formulaire qui va se baser sur le $builder dans ProgrammeType
        $form = $this->createForm(ProgrammeType::class, $programme);
        $form->handleRequest($request);
        // dd($form['image']->getData());
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
            return $this->redirectToRoute('show_user', ['id' => $userId]);
        }
        return $this->render('programme/formulaire.html.twig', [
            'formProgramme' => $form->createView(),
            'programme' =>$programme
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
