<?php

namespace App\Controller;

use App\Entity\Diplome;
use App\Entity\Programme;
use App\Entity\User;
use App\Form\DiplomeType;
use App\Form\UserType;
use App\Repository\CommentaireRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    // supprission de compte
    #[Route('/delete', name: 'delete_compte')]
    public function deleteProgramme(ManagerRegistry $doctrine, Request $request, CommentaireRepository $commentaireRepository): Response
    {
        $user = $this->getUser();
        $commentaires = $commentaireRepository->findBy(['user' => $user]);
        $entityManager = $doctrine->getManager();
        foreach ($commentaires as $commentaire) {
            $commentaire->setUser(null);
            $entityManager->persist($commentaire);
        }
        $compte =  $entityManager->getRepository(User::class)->remove($user);
        $entityManager->flush();

        $request->getSession()->invalidate();
        $this->container->get('security.token_storage')->setToken(null);
        $this->addFlash('message', 'le compte est supprimé !');
        return  $this->redirectToRoute('app_home');
    }

    // recuperer tt les users qui ont le role COACH
    #[Route('/Coachs', name: 'show_coachs')]
    public function showCoachs(UserRepository $userRepository): Response
    {
        $coachs = $userRepository->findByRole('ROLE_COACH');
        return $this->render('user/showCoachs.html.twig', [
            'coachs' => $coachs,
        ]);
    }

    // afficher profile user connecté
    #[Route('/user/profile', name: 'show_profile')]
    public function showProfile(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (($user->getRoles()[0]) == "ROLE_COACH") {
            // on cree le forulaire d'ajout de diplomes
            $diplome = new Diplome;
            $form = $this->createForm(DiplomeType::class, $diplome);
            $form->handleRequest($request);
            $diplome->setUser($user);
            $diplome->setIsVerified(false);
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
                            $this->getParameter('diplomeImage_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload

                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $diplome->setImage(
                        $newFilename
                    );
                }
                // recuperer les données de diplome si il existe deja et si il est nul
                $diplome = $form->getData();

                // on recupere le managere doctrine
                $entityManager = $doctrine->getManager();

                // persist remplace prepare en pdo , on prepare l'objet diplome 
                $entityManager->persist($diplome);

                //on execute 
                $entityManager->flush();
                $this->addFlash('message', "Le diplome est enregistré ! il sera bientot validé par l'admistration ,merci pour votre patience");

                return $this->redirectToRoute('show_profile', ['id' => $diplome->getUser()]);
            }
            return $this->render('user/showUser.html.twig', [
                'user' => $user,
                'formDiplome' => $form->createView()
            ]);
        }
        return $this->render('user/showUser.html.twig', [
            'user' => $user,
        ]);
    }

    // recuperer detail d'un Coach
    #[Route('/user/{id}', name: 'show_user')]
    public function showUser(UserRepository $userRepository, $id): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        if (($user->getRoles()[0]) == "ROLE_ADMIN") {
            dd('erreur');
            // $this->redirectToRoute('app_home');
        };
        return $this->render('user/showUser.html.twig', [
            'user' => $user,
        ]);
        // }
    }

    // editer user
    #[Route('/user/edit/{id}', name: 'edit_user')]
    public function edit(ManagerRegistry $doctrine, User $user = null, Request $request, SluggerInterface $slugger): Response
    {
        // si l'id de l'user envoyer par l'url est le meme id de l'user connecté 
        if (($this->getUser()) == $user) {

            // construire le formulaire 
            // si le user est un client on affiche pas le champs biographie
            if ($user->getRoles()[0] != 'ROLE_COACH') {
                $form = $this->createForm(UserType::class, $user)->remove('biographie');
            }
            $form = $this->createForm(UserType::class, $user)->remove('password');
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
                $this->addFlash('message', 'votre profile est bien modifié 😄');
                return $this->redirectToRoute('show_user', ['id' => $user->getId()]);
            }
            return $this->render('user/editUser.html.twig', [
                'formUser' => $form->createView()
            ]);
        } else {
            $this->addFlash('message', 'accés refusé !');
            return $this->redirectToRoute("show_profile");
        }
    }

    // ajouter/supprimer un programme a la liste des favories
    #[Route('/user/favorie/{id}', name: 'add_favorie')]
    public function addToFavorie(Programme $programme, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $userconnecte = $this->getUser();
        if ($userconnecte) {
            if ($userconnecte->getRoles()[0] != 'ROLE_USER') {
                $this->addFlash('message', "Vous n'avez pas le droit de mettre en favorie tant que vous n'etes pas connecté entant que client ");
                return $this->redirectToRoute('show_programme', ['id' => $programme->getId()]);
            }
            $user = $userRepository->findOneBy(['id' => $userconnecte]);
            $favories = $user->getFavories();
            if ($favories->contains($programme)) {
                // supprimer le programme de la liste 
                $user->removeFavory($programme);
                // enregister dans la bdd
                $entityManager = $doctrine->getManager();
                // on execute
                $entityManager->flush();
                $this->addFlash('message', 'Le programme est retiré de votre liste de souhait !');
            } else {
                // ajouter le progtramme a la liste de user
                $user->addFavory($programme);
                // enregister dans la bdd
                $entityManager = $doctrine->getManager();
                // on execute
                $entityManager->flush();
                $this->addFlash('message', 'Le programme est bien enregistrer dans votre liste de souhait !');
            }

            return $this->redirectToRoute('show_programme', ['id' => $programme->getId()]);
        } else {
            // si l'user est pas connecté on le rederige vers la page de connexion
            $this->addFlash('message', 'Vous devez vous connecter !');
            return $this->redirectToRoute('app_login');
        }
    }
}
