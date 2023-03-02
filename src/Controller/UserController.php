<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        // if (($user->getRoles()) != "ROLE_COACH") {

        // } else {
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
            dd($user);
        };
        return $this->render('user/editUser.html.twig');
    }
}
