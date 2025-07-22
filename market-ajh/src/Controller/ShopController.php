<?php

namespace App\Controller;

use App\Entity\Guild;
use App\Repository\GuildRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'shop_index')]
    public function index(GuildRepository $guildRepository): Response
    {
        // on ne propose que les guildes qui ont allowedToSell = true
        $guilds = $guildRepository->findAllowedToSell();

        return $this->render('shop/index.html.twig', [
            'guilds' => $guilds,
            'nomdepage' => 'Boutique',
        ]);
    }

    #[Route('/shop/{id}', name: 'shop_show', requirements: ['id' => '\d+'])]
    public function show(int $id, GuildRepository $guildRepository): Response
    {
        // récupère la guilde
        $guild = $guildRepository->find($id);
        if (!$guild) {
            throw $this->createNotFoundException('Guilde non trouvée.');
        }

        // on suppose que l’entité Guild a bien un getGuildItems()
        $guildItems = $guild->getGuildItems();

        return $this->render('shop/list.html.twig', [
            'guild'      => $guild,
            'guildItems' => $guildItems,
            'nomdepage'  => 'Boutique de ' . $guild->getName(),
        ]);
    }
}
