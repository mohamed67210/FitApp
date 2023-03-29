<?php

namespace App\Controller\admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users', name: 'admin_users_')]
class AdminUsersController extends AbstractController
{
    #[Route('/coachs', name: 'coachs')]
    public function coachs(UserRepository $userRepository): Response
    {
        $coachs = $userRepository->findByRole('ROLE_COACH');
        return $this->render('admin/users/coachs.html.twig', [
            'coachs' => $coachs,
        ]);
    }
    #[Route('/membres', name: 'membres')]
    public function membres(UserRepository $userRepository): Response
    {
        $membres = $userRepository->findAllMembres('ROLE_COACH','ROLE_ADMIN');
        // dd($membres);
        return $this->render('admin/users/membres.html.twig', [
            'membres' => $membres,
        ]);
    }
}
