<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Programme;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use App\Repository\ProgrammeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ModuleController extends AbstractController
{


    #[Route('/add/{id}', name: 'add_module')]
    public function addModule($id, Programme $programme, SluggerInterface $slugger, ManagerRegistry $doctrine, Request $request, Module $module = null, ProgrammeRepository $programmeRepository): Response
    {
        // recuperer le programme
        $programme = $programmeRepository->findOneBy(['id' => $id]);
        $idUser = $programme->getCoach()->getId();
        // dd($idUser);
        $this->denyAccessUnlessGranted('ROLE_COACH');
        $module = new Module;
        $module->setProgramme($programme);
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // upload video et miniature
            $uploadedFile = $form['video']->getData();
            $uploadedMiniature = $form['video']->getData();
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
                        $this->getParameter('moduleVideo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload

                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $module->setVideo(
                    $newFilename
                );
            }
            $programme = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($programme);
            $entityManager->flush();
            $this->addFlash('success', 'Le cour est enregistré !');
            return $this->redirectToRoute('show_user', ['id' => $idUser]);
        }
        return $this->render('module/formulaire.html.twig', [
            'formModule' => $form->createView()
        ]);
    }

    // recuperer les modules d'un programme
    #[Route('/modules/{id}', name: 'programme_modules')]
    public function programmeModules($id, ModuleRepository $moduleRepository): Response
    {
        $programmeModules = $moduleRepository->findBy(['programme' => $id]);
        // dd($programmeModules);
        return $this->render('module/index.html.twig', [
            'modules' => $programmeModules,
        ]);
    }
}
