<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Wallpaper;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\News;
use App\Form\NewsPostType;
use Doctrine\ORM\EntityManagerInterface;



final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        Wallpaper $wallpaperService,
        EntityManagerInterface $em
    ): Response {
        $news = new News();
        $form = $this->createForm(NewsPostType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $news->setDateCreation(new \DateTime());
            $news->setEmetteur($this->getUser()); // ajout pour cohérence
            $em->persist($news);
            $em->flush();
            $this->addFlash('success', 'Nouvelle créée avec succès !');
            return $this->redirectToRoute('app_news');
        }

        return $this->render('news/create.html.twig', [
            'form' => $form->createView(),
            'nomdepage' => 'Gestion des nouvelles',
            'wallpaper' => $wallpaperService->getRandomWallpaperName(),
        ]);
    }


    #[Route('/news/create', name: 'app_news_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request, 
        Wallpaper $wallpaperService, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $news = new News();
        $form = $this->createForm(NewsPostType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $news->setDateCreation(new \DateTime());
            $news->setEmetteur($this->getUser()); // déjà présent
            $entityManager->persist($news);
            $entityManager->flush();
            $this->addFlash('success', 'Nouvelle créée avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('news/create.html.twig', [
            'form' => $form->createView(),
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    /**
     * Retire les nouvelles de plus de 7 jours
     * TODO: Implémenter la logique de suppression selon une valeur de chaque nouvelle
     */
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
