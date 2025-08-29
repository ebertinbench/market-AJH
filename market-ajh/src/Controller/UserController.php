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
            $user->setWallpaper($wallpaperService->getRandomWallpaperName());
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

    #[Route('/toggle-vendeur/{id}', name: 'app_user_toggle_vendeur', methods: ['POST'])]
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

    #[Route('/toggle-comptable/{id}', name: 'app_user_toggle_comptable', methods: ['POST'])]
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
}
