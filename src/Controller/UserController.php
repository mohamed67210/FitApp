<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/Coachs', name: 'show_users')]
    public function showCoachs(UserRepository $userRepository): Response
    {
        $coachs = $userRepository->findByRole('ROLE_COACH');

        return $this->render('user/showCoachs.html.twig', [
            'coachs' => $coachs,
        ]);
    }
}
