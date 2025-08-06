<?php

namespace App\Controller;
use Psr\Log\LoggerInterface;
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

    #[Route('/shop/order', name: 'shop_order', methods: ['POST'])]
    public function order(
        LoggerInterface $logger,
        \Symfony\Component\HttpFoundation\Request $request,
        \App\Repository\GuildItemsRepository $guildItemsRepository,
        \Doctrine\ORM\EntityManagerInterface $em
    ): Response
    {
        // Récupérer la quantité postée
        $quantity = $request->request->get('quantity');
        $itemId = $request->request->get('item_id');
        $statut = "En attente"; 
        $idClient = $this->getUser(); 
        $idVendeur = null; 
        $dateCommande = new \DateTime();    

        // Vérifier que l'itemId correspond à une entité GuildItems
        $guildItem = $guildItemsRepository->find($itemId);
        if ($guildItem) {
            $logger->info('itemId correspond à une entité GuildItems', [
                'item_id' => $itemId,
                'guild_item_name' => $guildItem->getItem()->getNom(),
            ]);
        } else {
            $logger->warning('itemId ne correspond à aucune entité GuildItems', [
                'item_id' => $itemId,
            ]);
        }

        $logger->info('Nouvelle commande détectée', [
            'quantité'      => $quantity,
            'item_id'       => $itemId,
            'statut'        => $statut,
            'idClient'      => $idClient ? $idClient->getId() : null,
            'idVendeur'     => $idVendeur,
            'dateCommande'  => $dateCommande->format('Y-m-d H:i:s'),
        ]);

        // Création et persistance de la commande
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
        // Redirige vers la page précédente (celle du formulaire)
        return $this->redirect($request->headers->get('referer'));
    }


}
