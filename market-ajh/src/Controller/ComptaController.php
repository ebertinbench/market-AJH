<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CommandeRepository;
use App\Services\Wallpaper;
use Dom\Entity;
use Doctrine\ORM\EntityManagerInterface;

final class ComptaController extends AbstractController
{
    #[Route('/compta', name: 'app_compta')]
    public function index(
        CommandeRepository $commandeRepository, 
        Wallpaper $wallpaperService
    ): Response
    {
        $commandes = $commandeRepository->findAll();

        return $this->render('compta/index.html.twig', [
            'controller_name' => 'ComptaController',
            'nomdepage' => 'Comptabilité générale ',
            'user' => $this->getUser(),
            'commandes' => $commandes,
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }
    
    #[Route('/compta/filter', name: 'app_compta_filter', methods: ['GET', 'POST'])]
    public function filter(
        Request $request, 
        CommandeRepository $commandeRepository, 
        Wallpaper $wallpaperService
    ): Response
    {
        $userParam = $request->query->get('user', '');
        $guildeParam = $request->query->get('guilde', '');
        $statutParam = $request->query->get('statut', '');
        $traitementComptaParam = $request->query->get('traitementCompta', '');

        $commandes = $commandeRepository->findByUserAndGuildeAndStatutAndTraitementCompta($userParam, $guildeParam, $statutParam , $traitementComptaParam);

        return $this->render('compta/index.html.twig', [
            'nomdepage' => 'Comptabilité générale ',
            'user' => $this->getUser(),
            'commandes' => $commandes,
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/compta/mark-traiteecompta', name: 'app_compta_mark_traiteecompta')]
    public function markTraiteeCompta(
        Request $request,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $id = $request->query->get('id');
        $commande = $commandeRepository->find($id);

        if ($commande && !$commande->isTraitementCompta()) {
            $commande->setTraitementCompta(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_compta');
    }

}
