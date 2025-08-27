<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\RoleFormType;
use App\Form\PasswordFormType;
use App\Services\Wallpaper;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository, 
        Wallpaper $wallpaperService
    ): Response {
        return $this->render('user/index.html.twig', [
            'users'     => $userRepository->findAll(),
            'nomdepage' => 'Gestion des utilisateurs',
            'wallpaper' => $wallpaperService->getRandomWallpaperName(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        Wallpaper $wallpaperService,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user'      => $user,
            'form'      => $form,
            'nomdepage' => 'Ajouter un utilisateur',
            'wallpaper' => $wallpaperService->getRandomWallpaperName(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(
        User $user,
        Wallpaper $wallpaperService
    ): Response {
        return $this->render('user/show.html.twig', [
            'user'      => $user,
            'nomdepage' => 'Détails de l\'utilisateur',
            'wallpaper' => $wallpaperService->getRandomWallpaperName(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        Wallpaper $wallpaperService
    ): Response {
        $roleForm = $this->createForm(RoleFormType::class, $user);
        $roleForm->handleRequest($request);

        if ($roleForm->isSubmitted() && $roleForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rôles mis à jour.');
            return $this->redirectToRoute('app_user_index', ['id' => $user->getId()]);
        }

        $passwordForm = $this->createForm(PasswordFormType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $newPassword = $passwordForm->get('plainPassword')->getData();
            $hashed = $hasher->hashPassword($user, $newPassword);
            $user->setPassword($hashed);
            $em->flush();
            $this->addFlash('success', 'Mot de passe mis à jour.');
            return $this->redirectToRoute('app_user_index', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'roleForm'     => $roleForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'user'         => $user,
            'nomdepage'    => 'Modifier l\'utilisateur',
            'wallpaper'    => $wallpaperService->getRandomWallpaperName(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/discord', name: 'app_user_edit_discord', methods: ['POST'])]
    public function editDiscord(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $newDiscord = $request->request->get('discordPseudo');
        if ($newDiscord && $this->isCsrfTokenValid('edit_discord' . $user->getId(), $request->request->get('_token'))) {
            $user->setPseudoDiscord($newDiscord);
            $entityManager->flush();
            $this->addFlash('success', 'Pseudo Discord mis à jour.');
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/{id}/minecraft', name: 'app_user_edit_minecraft', methods: ['POST'])]
    public function editMinecraft(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        
        $newMinecraft = $request->request->get('minecraftPseudo');
        if ($newMinecraft && $this->isCsrfTokenValid('edit_minecraft' . $user->getId(), $request->request->get('_token'))) {
            $user->setPseudoMinecraft($newMinecraft);
            $entityManager->flush();
            $this->addFlash('success', 'Pseudo Minecraft mis à jour.');
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/{id}/wallpaper', name: 'app_user_change_wallpaper', methods: ['POST'])]
    public function changeWallpaper(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $newWallpaper = $request->request->get('wallpaper');
        // Ensure $newWallpaper is a string, not null
        $user->setWallpaper($newWallpaper ?? '');
        $entityManager->flush();
        $this->addFlash('success', 'Fond d\'écran mis à jour.');
        return $this->redirectToRoute('app_profile');
    }
}
