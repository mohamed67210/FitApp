<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Commentaire;
use App\Entity\Programme;
use App\Entity\User;
use App\Form\CommentaireType;
use App\Form\ProgrammeType;
use App\Repository\CategorieRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgrammeController extends AbstractController
{


    // afficher tous les programmes active
    #[Route('/programmes', name: 'show_programmes')]
    public function allProgrammesByCateg(CategorieRepository $categorieRepository): Response
    {
        // on recupere les categories pour afficher leurs programmes dans la vue
        $categories = $categorieRepository->findBy([], ['id' => 'asc']);
        return $this->render('programme/allProgrammes.html.twig', [
            'categories' => $categories,
        ]);
    }

    // supprission d'un programme
    #[Route('/programme/delete/{id}', name: 'delete_programme')]
    public function deleteProgramme(ManagerRegistry $doctrine, Programme $programme): Response
    {
        // supprission d'image de dossier image
        $image = $programme->getImage();
        if ($image) {
            // le chemin de l'image
            $nomImage = $this->getParameter('programmeImage_directory') . '/' . $image;
            // verifier si le file existe dans le dossier
            if (file_exists($nomImage)) {
                unlink($nomImage);
            }
        }
        $entityManager = $doctrine->getManager();
        $programme =  $entityManager->getRepository(Programme::class)->remove($programme);
        $entityManager->flush();
        $this->addFlash('message', 'le programme est supprimé !');
        return  $this->redirectToRoute('show_profile');
    }

    //ajouter un programme 
    #[Route('/programme/add', name: 'add_programme')]
    public function add(ManagerRegistry $doctrine, Programme $programme = null, Request $request, SluggerInterface $slugger): Response
    {
        if($this->getUser()){
            
            // vérifier si l'utilisateur actuellement connecté possède le rôle "ROLE_COACH" 
            $this->denyAccessUnlessGranted('ROLE_COACH');
            $programme = new programme;   
            // dd($programme);
            $programme->setCoach($this->getUser());
            $userId = $programme->getCoach()->getId();
            // construire un formulaire qui va se baser sur le $builder dans ProgrammeType
            $form = $this->createForm(ProgrammeType::class, $programme);
            $form->handleRequest($request);
            // dd($form['image']->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                // ------------upload image
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
                // recuperer les données de programme 
                $programme = $form->getData();
                // verifier si le prix promos est inferieur au prix de base 
                if ($programme->getPrixPromo() != null && ($programme->getPrix() < $programme->getPrixPromo())) {
                    $this->addFlash('message','Le prix promo doit etres inferieure au pris de base');
                    return $this->render('programme/formulaire.html.twig', [
                        'formProgramme' => $form->createView(),
                        'programme' => $programme,
                    ]);
                }else {
                // isValid sera en false pour chaque nouveau programme
                $programme->setIsValid(false);
                // on recupere le managere doctrine
                $entityManager = $doctrine->getManager();
                // persist remplace prepare en pdo , on prepare l'objet Programmme 
                $entityManager->persist($programme);
                //on execute 
                $entityManager->flush();
                $this->addFlash('message', "Félicitation !Le Programme est enregistré, pour l'instant il est pas en ligne,il sera dabord examiné pour étres validé ");
                return $this->redirectToRoute('show_user', ['id' => $userId]);
                }
                
            }
            return $this->render('programme/formulaire.html.twig', [
                'formProgramme' => $form->createView(),
                'programme' => $programme
            ]);
        }else {
            $this->addFlash('message','Accés refusé !');
            return $this->redirectToRoute('app_login');
        }
    }

    // editer un programme par son coach
    #[Route('/programme/edit/{id}', name: 'edit_programme')]
    public function edit(ManagerRegistry $doctrine, Programme $programme = null, Request $request, SluggerInterface $slugger): Response
    {
        if ($programme) {
            $this->denyAccessUnlessGranted('ROLE_COACH');
            $edit = true;
            // Vérifier si l'utilisateur actuel est le créateur du programme
            if ($programme->getCoach() !== $this->getUser()) {
                $this->addFlash('message', 'accés réfusé ff!');
                return $this->redirectToRoute('show_profile');
            }
            $programme->setCoach($this->getUser());
            $userId = $programme->getCoach()->getId();
            // construire un formulaire qui va se baser sur le $builder dans ProgrammeType
            $form = $this->createForm(ProgrammeType::class, $programme);
            $form->handleRequest($request);
            // dd($form['image']->getData());
            if ($programme->getCoach() !== $this->getUser()) {
                $this->addFlash('message', 'accés réfusé !');
                return $this->redirectToRoute('show_profile');
            } else {
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
                    // si le programme est active on le laisse active
                    if ($programme->isIsValid() === true) {
                        $programme->setIsValid(true);
                    }
                    // verifier si le prix promos est inferieur au prix de base 
                    if ($programme->getPrixPromo() != null && ($programme->getPrix() <= $programme->getPrixPromo())) {
                        $this->addFlash('message','Le prix promo doit etres inferieure au pris de base');
                        return $this->render('programme/formulaire.html.twig', [
                            'formProgramme' => $form->createView(),
                            'programme' => $programme
                        ]);
                    }
                    // on recupere le managere doctrine
                    $entityManager = $doctrine->getManager();
                    // persist remplace prepare en pdo , on prepare l'objet Programmme 
                    $entityManager->persist($programme);
                    //on execute 
                    $entityManager->flush();

                    $this->addFlash('message', "Le Programme est bien edité");
                    return $this->redirectToRoute('show_profile');
                }
            }
            return $this->render('programme/formulaire.html.twig', [
                'formProgramme' => $form->createView(),
                'programme' => $programme,
                'edit' =>$edit
            ]);
        } else {
            $this->addFlash('message', 'Accés refusé !');
            return $this->redirectToRoute('show_profile');
        }
    }


    // afficher detail d'un programme,avec affichage de formulaire de commentaire
    #[Route('/programme/{id}', name: 'show_programme')]
    public function showProgramme(ManagerRegistry $doctrine, Programme $programme = null, Commentaire $commentaire = null, Request $request,UserRepository $userRepository): Response
    {
        if ($programme) {
            // on verifie si le programme est deja valider par l'admin ou si l'user connecté est le proprié de programme
            if (($programme->isIsValid()) == true || ($this->getUser())== $programme->getCoach()) {
                $commentaire = new Commentaire;
            // dd(new DateTimeImmutable('now'));
            $form = $this->createForm(CommentaireType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($this->getUser()) {
                    $commentaire = $form->getData();
                    $commentaire->setProgramme($programme);
                    $commentaire->setUser($this->getUser());
                    $commentaire->setCreateAt(new \DateTime());
                    $entityManager = $doctrine->getManager();
                    // persist remplace prepare en pdo , on prepare l'objet Programmme 
                    $entityManager->persist($commentaire);
                    //on execute 
                    $entityManager->flush();

                    $this->addFlash('message', 'Le commentaire est enregistré !');
                    // on  retourne vers la page accueil
                    return $this->redirectToRoute('show_programme', ['id' => $programme->getId()]);
                } else {
                    $this->addFlash('message', 'accés refusé !');
                    return $this->redirectToRoute('show_programmes');
                }
            }
            // verifier si l'user connecté  a deja acheter le programme pour l'affiche des bouton d'achat
            $programmeAchete = false;
            $userConnect = $this->getUser();
            if ($userConnect) {
                $user = $userRepository->findOneBy(['id'=> $userConnect]);
                // dd($user);
                $commandes =$user->getCommandes();
                
                foreach($commandes as $commande){
                    if (($commande->getProgramme() === $programme)) {
                        $programmeAchete = true;
                    }
                    else {
                        $programmeAchete = false;
                    }
                }
            }
            
            return $this->render('programme/showProgramme.html.twig', [
                'programme' => $programme,
                'programmeAchete' => $programmeAchete,
                'commentaireForm' => $form->createView()
            ]);
            }
            else {
                $this->addFlash('message','Accés refusé !');
                return $this->redirectToRoute('app_home');
            }
        } else {
            return $this->redirectToRoute("show_programmes");
        }
    }
    
}
