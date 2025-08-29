<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GuildRepository;
use App\Repository\UserRepository;
use App\Entity\Guild;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Wallpaper;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(
        GuildRepository $guildRepository, 
        Wallpaper $wallpaperService
    ): Response {
        if (!$this->getUser()) {
            return $this->redirect('/welcome');
        }
        $guilds = $guildRepository->findAll();
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('profile/index.html.twig', [
            'nomdepage' => 'Profil Utilisateur',
            'user' => $user,
            'guilds' => $guilds,
            'commandes' => $user->getcommandesPassees(),
            'wallpaper' => $wallpaperService->getRandomWallpaperName(),
            'wallpaperservice' => $wallpaperService
        ]);
    }

    #[Route('/change-password', name: 'profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request, 
        UserPasswordHasherInterface $hasher, 
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
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
    public function changeGuild(Request $request, 
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
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

        return $this->redirectToRoute('app_logout');
    }

    #[Route('/addmembers', name: 'profile_add_members')]
    public function addMembers(UserRepository $userRepository, 
        GuildRepository $guildRepository,
        Wallpaper $wallpaperService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user || !$user->getChiefOf()) {
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('profile/add_members.html.twig', [
            'nomdepage' => 'Ajouter des membres',
            'user' => $this->getUser(),
            'users' => $userRepository->findAll(),
            'guilds' => $guildRepository->findAll(),
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/add-member', name: 'profile_add_member', methods: ['POST'])]
    public function addMember(Request $request, 
        UserRepository $userRepository, 
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user || !$user->getChiefOf()) {
            return $this->redirectToRoute('app_profile');
        }

        $memberId = $request->request->get('member_id');
        $member = $userRepository->find($memberId);
        $guild = $user->getChiefOf();

        if ($member && $guild) {
            // Vérifier que le membre n'est pas un chef de guilde
            if ($member->getChiefOf() !== null) {
                $this->addFlash('danger', 'Impossible d\'ajouter un chef de guilde à votre guilde. Le membre doit d\'abord quitter son rôle de chef.');
                return $this->redirectToRoute('profile_add_members');
            }
            
            // Vérifier que le membre n'est pas déjà dans une guilde
            if ($member->getGuild() !== null) {
                $this->addFlash('danger', 'Ce membre est déjà dans une guilde.');
                return $this->redirectToRoute('profile_add_members');
            }
            
            $member->setGuild($guild);
            $em->flush();
            $this->addFlash('success', 'Membre ajouté à la guilde.');
        } else {
            $this->addFlash('danger', 'Impossible d\'ajouter le membre.');
        }

        return $this->redirectToRoute('profile_add_members');
    }

    #[Route('/remove-member', name: 'profile_remove_member', methods: ['POST'])]
    public function removeMember(Request $request, 
        UserRepository $userRepository, 
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user || !$user->getChiefOf()) {
            return $this->redirectToRoute('app_profile');
        }

        $memberId = $request->request->get('member_id');
        $member = $userRepository->find($memberId);
        $guild = $user->getChiefOf();

        if ($member && $guild && $member->getGuild() === $guild) {
            // Le chef ne peut pas se retirer lui-même
            if ($member === $user) {
                $this->addFlash('danger', 'Le chef de guilde ne peut pas se retirer de sa propre guilde.');
                return $this->redirectToRoute('profile_add_members');
            }
            
            $member->quitGuild(); // Cette méthode retire aussi le rôle vendeur
            $em->flush();
            $this->addFlash('success', 'Membre retiré de la guilde.');
        } else {
            $this->addFlash('danger', 'Impossible de retirer le membre.');
        }

        return $this->redirectToRoute('profile_add_members');
    }

    #[Route('/discord', name: 'app_user_edit_discord', methods: ['POST'])]
    public function editDiscord(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $newDiscord = $request->request->get('discordPseudo');
        if ($user && $newDiscord && $this->isCsrfTokenValid('edit_discord' . $user->getId(), $request->request->get('_token'))) {
            $user->setPseudoDiscord($newDiscord);
            $entityManager->flush();
            $this->addFlash('success', 'Pseudo Discord mis à jour.');
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/minecraft', name: 'app_user_edit_minecraft', methods: ['POST'])]
    public function editMinecraft(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $newMinecraft = $request->request->get('minecraftPseudo');
        if ($user && $newMinecraft && $this->isCsrfTokenValid('edit_minecraft' . $user->getId(), $request->request->get('_token'))) {
            $user->setPseudoMinecraft($newMinecraft);
            $entityManager->flush();
            $this->addFlash('success', 'Pseudo Minecraft mis à jour.');
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/toggle-vendeur/{id}', name: 'profile_toggle_vendeur', methods: ['POST'])]
    public function toggleVendeurRole(
        User $user,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('toggle_vendeur' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('profile_add_members');
        }

        // Vérifier que l'utilisateur connecté est le chef de la guilde de l'utilisateur ciblé
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !$currentUser->getChiefOf() || $user->getGuild() !== $currentUser->getChiefOf()) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour modifier ce membre.');
            return $this->redirectToRoute('profile_add_members');
        }

        // Le chef ne peut pas se retirer le rôle vendeur à lui-même
        if ($user === $currentUser) {
            $this->addFlash('error', 'Le chef de guilde ne peut pas se retirer le rôle vendeur.');
            return $this->redirectToRoute('profile_add_members');
        }

        // Basculer le rôle vendeur
        if ($user->hasVendeurRole()) {
            $user->removeVendeurRole();
            $message = 'Rôle vendeur retiré à ' . $user->getUsername();
        } else {
            $user->addVendeurRole();
            $message = 'Rôle vendeur assigné à ' . $user->getUsername();
        }

        $entityManager->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('profile_add_members');
    }

    #[Route('/toggle-comptable/{id}', name: 'profile_toggle_comptable', methods: ['POST'])]
    public function toggleComptableRole(
        User $user,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('toggle_comptable' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('profile_add_members');
        }

        // Vérifier que l'utilisateur connecté est le chef de la guilde de l'utilisateur ciblé
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !$currentUser->getChiefOf() || $user->getGuild() !== $currentUser->getChiefOf()) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour modifier ce membre.');
            return $this->redirectToRoute('profile_add_members');
        }

        // Le chef ne peut pas se retirer le rôle comptable à lui-même
        if ($user === $currentUser) {
            $this->addFlash('error', 'Le chef de guilde ne peut pas se retirer le rôle comptable.');
            return $this->redirectToRoute('profile_add_members');
        }

        // Basculer le rôle comptable
        if ($user->hasComptableRole()) {
            $user->removeComptableRole();
            $message = 'Rôle comptable retiré à ' . $user->getUsername();
        } else {
            $user->addComptableRole();
            $message = 'Rôle comptable assigné à ' . $user->getUsername();
        }

        $entityManager->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('profile_add_members');
    }

    #[Route('/wallpaper', name: 'app_user_change_wallpaper', methods: ['POST'])]
    public function changeWallpaper(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $newWallpaper = $request->request->get('wallpaper');
        if ($user && $this->isCsrfTokenValid('change_wallpaper' . $user->getId(), $request->request->get('_token'))) {
            // Ensure $newWallpaper is a string, not null
            $user->setWallpaper($newWallpaper ?? '');
            $entityManager->flush();
            $this->addFlash('success', 'Fond d\'écran mis à jour.');
        }
        return $this->redirectToRoute('app_profile');
    }
}
