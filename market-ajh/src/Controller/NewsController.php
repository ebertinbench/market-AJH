<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Wallpaper;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;



final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(Request $request, Wallpaper $wallpaperService): Response
    {
        return $this->render('news/index.html.twig', [
            'controller_name' => 'NewsController',
            'nomdepage' => 'Gestion des nouvelles',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/news/create', name: 'app_news_create', methods: ['POST'])]
    public function create(Request $request, Wallpaper $wallpaperService, EntityManagerInterface $entityManager): Response
    {
        $contenu = $request->request->get('contenu');
        $titre = $request->request->get('titre');
        $user = $this->getUser();
        $dateCreation = new \DateTime();
        $news = new News($user, $contenu, $dateCreation, $titre);
        $entityManager->persist($news);
        $entityManager->flush();
        $this->addFlash('success', 'Nouvelle créée avec succès !');
        return $this->redirectToRoute('app_home');
    }

    public static function removeExpiredNews(EntityManagerInterface $entityManager): Response{
        $sevenDaysAgo = (new \DateTime())->modify('-7 days');
        $qb = $entityManager->createQueryBuilder();
        $qb->delete(News::class, 'n')
            ->where('n.dateCreation < :limit')
            ->setParameter('limit', $sevenDaysAgo);
        $qb->getQuery()->execute();

        return new Response('Expired news removed.');
    }
}
