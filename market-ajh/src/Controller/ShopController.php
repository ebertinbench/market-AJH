<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Repository\GuildRepository;
use App\Repository\GuildItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Autowire;
use App\Services\Wallpaper;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'shop_index')]
    public function index(GuildRepository $guildRepository, Wallpaper $wallpaperService): Response
    {
        $guilds = $guildRepository->findAllowedToSell();

        return $this->render('shop/index.html.twig', [
            'guilds'     => $guilds,
            'nomdepage'  => 'Boutique',
            'wallpaper'  => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/shop/{id}', name: 'shop_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id, 
        GuildRepository $guildRepository,
        Wallpaper $wallpaperService
    ): Response
    {
        // récupère la guilde
        $guild = $guildRepository->find($id);
        if (!$guild) {
            throw $this->createNotFoundException('Guilde non trouvée.');
        }
        $guildItems = $guild->getGuildItems();

        return $this->render('shop/list.html.twig', [
            'guild'      => $guild,
            'guildItems' => $guildItems,
            'nomdepage'  => 'Boutique de ' . $guild->getName(),
            'wallpaper'  => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/shop/order', name: 'shop_order', methods: ['POST'])]
    public function order(
        #[Autowire(service: 'monolog.logger.commandes')]
        LoggerInterface $commandesLogger,
        Request $request,
        GuildItemsRepository $guildItemsRepository,
        EntityManagerInterface $em
    ): Response {
        $quantity      = $request->request->get('quantity');
        $itemId        = $request->request->get('item_id');
        $statut        = 'En attente';
        $idClient      = $this->getUser();
        $idVendeur     = null;
        $dateCommande  = new \DateTime();
        $guildItem     = $guildItemsRepository->find($itemId);

        if ($guildItem) {
            $commandesLogger->info('itemId correspond à une entité GuildItems', [
                'item_id'         => $itemId,
                'guild_item_name' => $guildItem->getItem()->getNom(),
            ]);
        } else {
            $commandesLogger->warning('itemId ne correspond à aucune entité GuildItems', [
                'item_id' => $itemId,
            ]);
        }

        $commandesLogger->info('Nouvelle commande détectée', [
            'idClient'     => $idClient?->getId(),
            'item_id'      => $itemId,
            'statut'       => $statut,
            'idClient'     => $idClient ? $idClient->getId() : null,
            'idVendeur'    => $idVendeur,
            'dateCommande' => $dateCommande->format('Y-m-d H:i:s'),
        ]);

        $commande = new \App\Entity\Commande(
            $idClient,
            $idVendeur,
            $quantity,
            $statut,
            $dateCommande,
            null,
            null,
            null,
            $guildItem
        );
        $em->persist($commande);
        $em->flush();

        $this->addFlash('success', 'Votre commande a bien été enregistrée.');

        return $this->redirect($request->headers->get('referer'));
    }
}
