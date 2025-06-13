<?php

namespace App\Controller;

use App\Repository\GuildRepository;
use App\Repository\UserRepository;
use App\Entity\Guild;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class GuildController extends AbstractController
{
    #[Route('', name: 'app_guild')]
    public function index(GuildRepository $guildRepository, UserRepository $userRepository): Response
    {
        $guilds = $guildRepository->findAll();
        $users = $userRepository->findAll();
        return $this->render('guild/index.html.twig', [
            'controller_name' => 'GuildController',
            'nomdepage' => 'Gestion des guildes',
            'guilds' => $guilds,
            'users' => $users,
        ]);
    }

    #[Route('/assign-chief/{guildId}/{userId}', name: 'assign_guild_leader', methods: ['POST'])]
    public function assignChief(int $guildId, int $userId, EntityManagerInterface $entityManager): Response
    {
        $guild = $entityManager->getRepository(Guild::class)->find($guildId);
        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$guild || !$user) {
            throw $this->createNotFoundException('Guilde ou utilisateur introuvable.');
        }

        // Supprimer l'ancien chef s'il existe
        $ancienChef = $guild->getChef();
        if ($ancienChef !== null && $ancienChef !== $user) {
            $ancienChef->setChiefOf(null);
            $guild->setChef(null);
            $entityManager->persist($ancienChef);
            $entityManager->persist($guild);
            $entityManager->flush(); // 1er flush : libère l'ancien chef
        }

        // Supprimer l'ancienne guilde dont l'utilisateur est chef
        $ancienneGuilde = $user->getChiefOf();
        if ($ancienneGuilde !== null && $ancienneGuilde !== $guild) {
            $ancienneGuilde->setChef(null);
            $user->setChiefOf(null);
            $entityManager->persist($ancienneGuilde);
            $entityManager->persist($user);
            $entityManager->flush(); // 2e flush : désassocie complètement l'utilisateur
        }

        // Associer le nouveau chef à la guilde
        $guild->setChef($user);
        $user->setChiefOf($guild);
        $entityManager->persist($guild);
        $entityManager->persist($user);
        $entityManager->flush(); // 3e flush : crée la nouvelle relation

        $this->addFlash('success', 'Le chef de guilde a bien été assigné.');
        return $this->redirectToRoute('app_guild');
    }
}
