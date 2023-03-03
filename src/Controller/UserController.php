<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    // recuperer tt les users qui ont le role COACH
    #[Route('/Coachs', name: 'show_caochs')]
    public function showCoachs(UserRepository $userRepository): Response
    {
        $coachs = $userRepository->findByRole('ROLE_COACH');

        return $this->render('user/showCoachs.html.twig', [
            'coachs' => $coachs,
        ]);
    }

    // recuperer detail d'un user
    #[Route('/user/{id}', name: 'show_user')]
    public function showUser(UserRepository $userRepository, $id): Response
    {

        $user = $userRepository->findOneBy(['id' => $id]);
        return $this->render('user/showUser.html.twig', [
            'user' => $user,
        ]);
        // }
    }

    // editer user
    #[Route('/user/edit/{id}', name: 'edit_user')]
    public function edit(ManagerRegistry $doctrine, User $user, Request $request, SluggerInterface $slugger): Response
    {
        // si l'id de l'user envoyer par l'url est le meme id de l'user connecté 
        if (($this->getUser()) == $user) {

            // construire le formulaire 
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // recuperer l'image
                $uploadFile = $form['image']->getData();
                if ($uploadFile) {
                    $originaleFilename = pathinfo($uploadFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originaleFilename);
                    $newFilename = $safeFilename . '_' . uniqid() . '.' . $uploadFile->guessExtension();
                    try {
                        $uploadFile->move(
                            $this->getParameter('userImage_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                    }

                    $user->setImage($newFilename);
                }
                // recuperer donnees de formulaire
                $user = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'votre profile est bien modifié 😄');
                return $this->redirectToRoute('show_user', ['id' => $user->getId()]);
            }
            return $this->render('user/editUser.html.twig', [
                'formUser' => $form->createView()
            ]);
        }
    }
}
