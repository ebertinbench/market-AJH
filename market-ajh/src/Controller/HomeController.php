<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found or not logged in.');
        }

        return $this->render('home/index.html.twig', [
            'user' => $user,
            "nomdepage" => "Page utilisateur",
        ]);
    }

    #[Route('/', name: 'app_redirect_home')]
    public function redirectToHome(): Response
    {
        return $this->redirectToRoute('app_welcome');
    }

    #[Route('/welcome', name: 'app_welcome')]
    public function welcome(): Response
    {
        return $this->render('home/welcome.index.html.twig', [
            "nomdepage" => "Accueil",
        ]);
    }
}
