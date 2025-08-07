<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GuildRepository;
use App\Repository\UserRepository;
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
            'nomdepage' => 'Profil Utilisateur',
            'user' => $this->getUser(),
            'guilds' => $guilds,
        ]);
    }

    #[Route('/change-password', name: 'profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request, 
        UserPasswordHasherInterface $hasher, 
        EntityManagerInterface $em
        ): Response
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
    public function changeGuild(Request $request, 
        EntityManagerInterface $em
        ): Response
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

        return $this->redirectToRoute('app_logout');
    }

    #[Route('/addmembers', name: 'profile_add_members')]
    public function addMembers(UserRepository $userRepository, 
        GuildRepository $guildRepository
        ): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->getChiefOf()) {
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('profile/add_members.html.twig', [
            'nomdepage' => 'Ajouter des membres',
            'user' => $this->getUser(),
            'users' => $userRepository->findAll(),
            'guilds' => $guildRepository->findAll(),
        ]);
    }

    #[Route('/add-member', name: 'profile_add_member', methods: ['POST'])]
    public function addMember(Request $request, 
        UserRepository $userRepository, 
        EntityManagerInterface $em
        ): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->getChiefOf()) {
            return $this->redirectToRoute('app_profile');
        }

        $memberId = $request->request->get('member_id');
        $member = $userRepository->find($memberId);
        $guild = $user->getChiefOf();

        if ($member && $guild) {
            $member->setGuild($guild);
            $em->flush();
            $this->addFlash('success', 'Membre ajouté à la guilde.');
        } else {
            $this->addFlash('danger', 'Impossible d\'ajouter le membre.');
        }

        return $this->redirectToRoute('profile_add_members');
    }
}
