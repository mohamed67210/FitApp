<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    // supprimer un commentaire
    #[Route('/commentaire/delete/{id}', name: 'delete_commentaire')]
    public function deleteCommentaire(ManagerRegistry $doctrine, Commentaire $commentaire = null, CommentaireRepository $commentaireRepository): Response
    {
        $userConnect = $this->getUser();
        if ($userConnect) {
            if ($userConnect == $commentaire->getUser() || $userConnect->getRoles()[0] == 'ROLE_ADMIN')  {
                // 1 methode avec ManagerRegistry 
                // $entityManager = $doctrine->getManager();
                // $entityManager->remove($commentaire);
                // $entityManager->flush();
                // 2 methode avec commentaireRopository
                $commentaireRepository->remove($commentaire, true);
                $programme = $commentaire->getProgramme();
                // dd('commentaire deleted');
                return $this->redirectToRoute('show_programme', ['id' => $programme->getId()],);
            } else {
                $this->addFlash('message', 'acces réfusé !');
                return $this->redirectToRoute('app_login');
            }
        } else {
            $this->addFlash('message', 'acces réfusé !');
            return $this->redirectToRoute('app_login');
        }
    }
}
