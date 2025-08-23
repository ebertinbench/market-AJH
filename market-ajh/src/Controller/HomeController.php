<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Wallpaper;
use App\Entity\User;
use App\Entity\News;
use App\Enum\NewsType;
use Doctrine\ORM\EntityManagerInterface;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(
        Wallpaper $wallpaperService, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found or not logged in.');
        }
        NewsController::removeExpiredNews($entityManager);
        return $this->render('home/index.html.twig', [
            'user'      => $user,
            'nomdepage' => 'Page utilisateur',
            'wallpaper' => $wallpaperService->getRandomWallpaperName(),
            'news_staff' => $entityManager->getRepository(News::class)->findByType(NewsType::STAFF),
            'news_feature' => $entityManager->getRepository(News::class)->findByType(NewsType::FEATURE),
            'news_maintenance' => $entityManager->getRepository(News::class)->findByType(NewsType::MAINTENANCE)
        ]);
    }

    #[Route('/', name: 'app_redirect_home')]
    public function redirectToHome(Wallpaper $wallpaperService): Response
    {
        return $this->redirectToRoute('app_welcome');
    }

    #[Route('/welcome', name: 'app_welcome')]
    public function welcome(Wallpaper $wallpaperService): Response
    {
        return $this->render('home/welcome.index.html.twig', [
            'nomdepage' => 'Accueil',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }
}
