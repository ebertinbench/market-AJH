<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

final class OrderController extends AbstractController
{
    #[Route('/orders', name: 'orders_index', methods: ['GET'])]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        UserRepository $userRepository
    ): Response {
        // Récupère les filtres depuis les query params
        $playerId = $request->query->get('player');
        $playerId = ($playerId !== null && $playerId !== '') ? (int)$playerId : null;
        $status   = $request->query->get('status');

        // Charge les commandes filtrées
        $orders = $commandeRepository->findByFilters($playerId, $status);

        // Liste des joueurs pour le select
        $players = $userRepository->findAll();

        // Mapping clef => label des statuts (en français uniquement)
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
            'nomdepage'     => 'Commandes'
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

        // Si la commande a déjà un vendeur, empêcher la réassignation
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
        // Lorsqu'un vendeur abandonne, repasser le statut à "En attente"
        $commande->setStatut('En attente');
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Vendeur retiré avec succès. La commande est repassée en attente.');
        return $this->redirectToRoute('orders_index');
    }

    #[Route('/orders/{id}/advance-status', name: 'orders_advance_status', methods: ['POST'])]
    public function advanceStatus(
        int $id,
        CommandeRepository $commandeRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        // Ordre des statuts (valeurs en base)
        $statuses = [
            'En attente',
            'En attente de livraison',
            'Livrée',
        ];

        $currentStatus = $commande->getStatut();
        $currentIndex = array_search($currentStatus, $statuses);

        if ($currentIndex === false || $currentIndex >= count($statuses) - 1) {
            $this->addFlash('error', 'Impossible d\'avancer le statut.');
            return $this->redirectToRoute('orders_index');
        }

        $newStatus = $statuses[$currentIndex + 1];
        $commande->setStatut($newStatus);
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Statut avancé à "' . $newStatus . '".');
        return $this->redirectToRoute('orders_index');
    }

    #[Route('/orders/{id}/recede-status', name: 'orders_recede_status', methods: ['POST'])]
    public function recedeStatus(
        int $id,
        CommandeRepository $commandeRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        // Ordre des statuts (valeurs en base)
        $statuses = [
            'En attente',
            'En attente de livraison',
            'Livrée',
        ];

        $currentStatus = $commande->getStatut();
        $currentIndex = array_search($currentStatus, $statuses);

        if ($currentIndex === false || $currentIndex === 0) {
            $this->addFlash('error', 'Impossible de reculer le statut.');
            return $this->redirectToRoute('orders_index');
        }

        $newStatus = $statuses[$currentIndex - 1];
        $commande->setStatut($newStatus);
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Statut reculé à "' . $newStatus . '".');
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
}
