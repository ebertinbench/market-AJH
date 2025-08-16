<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\AvisCommande;
use App\Repository\AvisCommandeRepository;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Services\Wallpaper;

final class OrderController extends AbstractController
{
    #[Route('/orders', name: 'orders_index', methods: ['GET'])]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        UserRepository $userRepository,
        Wallpaper $wallpaperService
    ): Response {
        $playerId = $request->query->get('player');
        $playerId = ($playerId !== null && $playerId !== '') ? (int)$playerId : null;
        $status   = $request->query->get('status');
        $orders = $commandeRepository->findByFilters($playerId, $status);
        $players = $userRepository->findAll();
        $statuses = [
            'En attente'                => 'En attente',
            'En attente de livraison'   => 'En attente de livraison',
            'Livrée'                    => 'Livrée',
            'Avortée'                   => 'Avortée',
        ];

        return $this->render('order/index.html.twig', [
            'orders'        => $orders,
            'players'       => $players,
            'statuses'      => $statuses,
            'currentPlayer' => $playerId,
            'currentStatus' => $status,
            'nomdepage'     => 'Commandes',
            'wallpaper'     => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/orders/{id}/assign-seller', name: 'orders_assign_seller', methods: ['POST'])]
    public function assignSeller(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository,
        UserRepository $userRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }
        if ($commande->getIdVendeur() !== null) {
            $this->addFlash('error', 'Cette commande a déjà un vendeur assigné.');
            return $this->redirectToRoute('orders_index');
        }

        $sellerId = $request->request->get('seller_id');
        $seller = $userRepository->find($sellerId);
        if (!$seller) {
            $this->addFlash('error', 'Vendeur non trouvé.');
            return $this->redirectToRoute('orders_index');
        }

        $commande->setIdVendeur($seller);
        $commande->setStatut('En attente de livraison');
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Vendeur assigné avec succès.');
        return $this->redirectToRoute('orders_index');
    }

    #[Route('/orders/{id}/remove-seller', name: 'orders_remove_seller', methods: ['POST'])]
    public function removeSeller(
        int $id,
        CommandeRepository $commandeRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        $commande->setIdVendeur(null);
        $commande->setStatut('En attente');
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Vendeur retiré avec succès. La commande est repassée en attente.');
        return $this->redirectToRoute('orders_index');
    }

    
    #[Route('/orders/{id}/abort', name: 'orders_abort', methods: ['POST'])]
    public function abortOrder(
        int $id,
        CommandeRepository $commandeRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        $commande->setStatut('Avortée');
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande marquée comme avortée.');
        return $this->redirectToRoute('orders_index');
    }

    #[Route('/orders/{id}/mark-delivered', name: 'orders_mark_delivered', methods: ['POST'])]
    public function markDelivered(
        int $id,
        CommandeRepository $commandeRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        $commande->setStatut('Livrée');
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande marquée comme livrée.');
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/orders/{id}/give-advice', name: 'orders_give_advice', methods: ['POST'])]
    public function giveAdvice(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository,
        AvisCommandeRepository $avisCommandeRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }
        $note = $request->request->get('note');
        if($note == '') {
            $this->addFlash('error', 'Veuillez sélectionner une note.');
            return $this->redirectToRoute('app_profile');
        }
        $avis = new AvisCommande();
        $avis->setIdCommande($commande);
        $avis->setIdClient($this->getUser());
        $avis->setIdVendeur($commande->getIdVendeur());
        $avis->setNote((int)$note);
        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Avis donné avec succès.');
        return $this->redirectToRoute('app_profile');
    }
}