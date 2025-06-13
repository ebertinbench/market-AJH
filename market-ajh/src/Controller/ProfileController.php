<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GuildRepository;
use App\Entity\Guild;
use Doctrine\ORM\EntityManagerInterface;
#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(GuildRepository $guildRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirect('/welcome');
        }

        $guilds = $guildRepository->findAll();

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'nomdepage' => 'Profil Utilisateur',
            'user' => $this->getUser(),
            'guilds' => $guilds,
        ]);
    }
    #[Route('/change-password', name: 'profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $newPassword = $request->request->get('newPassword');

        if ($user && $newPassword) {
            $user->setPassword($hasher->hashPassword($user, $newPassword));
            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour.');
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/change-guild', name: 'profile_change_guild', methods: ['POST'])]
    public function changeGuild(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $guildId = $request->request->get('guild');

        if ($user && $guildId) {
            $guild = $em->getRepository(Guild::class)->find($guildId);
            if ($guild) {
                $user->setGuild($guild);
                $em->flush();
                $this->addFlash('success', 'Guilde mise à jour.');
            }
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/delete', name: 'profile_delete_account', methods: ['POST'])]
    public function deleteAccount(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user) {
            $em->remove($user);
            $em->flush();

            $this->addFlash('danger', 'Compte supprimé.');
        }

        return $this->redirectToRoute('app_logout'); // ou autre route de redirection
    }
}
