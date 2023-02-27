<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
