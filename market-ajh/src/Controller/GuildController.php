<?php

namespace App\Controller;

use App\Repository\GuildRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Attribute\Route;
#[Route('/guild')]
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
    #[Route('/assign-guild-leader', name: 'assign_guild_leader', methods: ['POST'])]
    public function assignGuildLeader(
        Request $request,
        GuildRepository $guildRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
    $guildId = $request->request->get('guild_id');
    $userId = $request->request->get('user_id');

    $guild = $guildRepository->find($guildId);
    $user = $userRepository->find($userId);

    if ($guild && $user) {
        $guild->setChef($user);
        $user->setChiefOf($guild); // Optionnel : si tu veux forcer la bidirectionnalité manuellement
        $em->flush();
        $this->addFlash('success', 'Le chef de guilde a été assigné avec succès.');
    } else {
        $this->addFlash('error', 'Impossible d\'assigner le chef de guilde.');
    }

    return $this->redirectToRoute('app_guild');
}

}
