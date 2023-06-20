<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Programme;
use App\Form\ModuleType;
use App\Repository\CommandeRepository;
use App\Repository\ModuleRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ModuleController extends AbstractController
{

    // ajout d'un module
    #[Route('/add/{id}', name: 'add_module')]
    public function addModule($id, Programme $programme, SluggerInterface $slugger, ManagerRegistry $doctrine, Request $request, Module $module = null, ProgrammeRepository $programmeRepository): Response
    {
        // recuperer le programme
        $programme = $programmeRepository->findOneBy(['id' => $id]);
        $idProgramme = $programme->getId();
        // dd($idUser);
        $this->denyAccessUnlessGranted('ROLE_COACH');
        $module = new Module;
        $module->setProgramme($programme);
        // dd($module);
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            // upload video et miniature
            $uploadedFile = $form['video']->getData();
            // dd($uploadedFile);
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
            return $this->redirectToRoute('programme_modules', ['id' => $idProgramme]);
        }
        return $this->render('module/formulaire.html.twig', [
            'formModule' => $form->createView()
        ]);
    }

    // recuperer les modules d'un programme
    #[Route('/modules/{id}', name: 'programme_modules')]
    public function programmeModules($id, ModuleRepository $moduleRepository, Programme $programme = null, UserRepository $userRepository, CommandeRepository $commandeRepository): Response
    {
        if ($programme) {
            // recuperer l'user connecter 
        $user = $userRepository->findOneBy(['id' => $this->getUser()]);
        $programmeACheter = false;
        // recuperer la commande de user connecté 
        $command = $commandeRepository->findOneBy(['user' => $user, 'programme' => $programme]);
        if ($command && $programme == $command->getProgramme()) {
            $programmeACheter = true;
            $programmeModules = $moduleRepository->findBy(['programme' => $id]);
            // dd($programmeModules);
            return $this->render('module/index.html.twig', [
                'programme' => $programme,
                'modules' => $programmeModules,
                'programmeAcheter'=>$programmeACheter,
                'commande'=>$command
            ]);
        } elseif (($user == $programme->getCoach())) {
            $programmeModules = $moduleRepository->findBy(['programme' => $id]);
            // dd($programmeModules);
            return $this->render('module/index.html.twig', [
                'programme' => $programme,
                'modules' => $programmeModules,
                'programmeAcheter'=>$programmeACheter,
            ]);
        } else {
            $this->addFlash('message','accés refusé !');
            return $this->redirectToRoute("app_home");
        }
        }
        else{
            $this->addFlash('message','accés refusé !');
            return $this->redirectToRoute('app_home');
        }
    }

    // supprission d'un module
    #[Route('/module/delete/{id}', name: 'delete_module')]
    public function deleteModule(ManagerRegistry $doctrine, Module $module = null): Response
    {
        if ($module) {
            if ($this->getUser()) {
                // recuperer le programme de module pour verifier si c'est le bon coach 
                $programme = $module->getProgramme();
                $coach = $programme->getCoach();
                if ($this->getUser() == $coach) {
                    // supprission de video de dossier image
                    $video = $module->getVideo();
                    if ($video) {
                        // le chemin de l'image
                        $nomVideo = $this->getParameter('moduleVideo_directory') . '/' . $video;
                        // verifier si le file existe dans le dossier
                        if (file_exists($nomVideo)) {
                            unlink($nomVideo);
                        }
                    }
                    $programmeId = $module->getProgramme()->getId();
                    $entityManager = $doctrine->getManager();
                    $module =  $entityManager->getRepository(Module::class)->remove($module);
                    $entityManager->flush();
                    $this->addFlash('message', 'le module est supprimé !');
                    return  $this->redirectToRoute('programme_modules', ['id' => $programmeId]);
                }
                else{
                    $this->addFlash('message','accés refusé !');
                    return $this->redirectToRoute('app_login');
                }  
            }
            else{
                $this->addFlash('message','accés refusé !');
                return $this->redirectToRoute('app_login');
            }
        }
        else{
            $this->addFlash('message','accés refusé !');
            return $this->redirectToRoute('app_home');
        }
            
        
    }
}
