<?php

namespace App\Controller;

use App\Repository\GuildRepository;
use App\Repository\UserRepository;
use App\Entity\Guild;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Services\Wallpaper;

final class GuildController extends AbstractController
{
    #[Route('/guild', name: 'app_guild')]
    public function index(
        GuildRepository $guildRepository, 
        UserRepository $userRepository,
        Wallpaper $wallpaperService
    ): Response {
        $guilds = $guildRepository->findAll();
        $users = $userRepository->findAll();
        return $this->render('guild/index.html.twig', [
            'controller_name' => 'GuildController',
            'nomdepage'       => 'Gestion des guildes',
            'guilds'          => $guilds,
            'users'           => $users,
            'wallpaper'       => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/assign-chief/{guildId}/{userId}', name: 'assign_guild_leader', methods: ['POST'])]
    public function assignChief(
        int $guildId, 
        int $userId, 
        EntityManagerInterface $entityManager
    ): Response {
        $guild = $entityManager->getRepository(Guild::class)->find($guildId);
        $user  = $entityManager->getRepository(User::class)->find($userId);

        if (!$guild || !$user) {
            throw $this->createNotFoundException('Guilde ou utilisateur introuvable.');
        }

        $ancienChef = $guild->getChef();
        if ($ancienChef !== null && $ancienChef !== $user) {
            $ancienChef->setChiefOf(null);
            $guild->setChef(null);
            $entityManager->persist($ancienChef);
            $entityManager->persist($guild);
            $entityManager->flush();
        }

        $ancienneGuilde = $user->getChiefOf();
        if ($ancienneGuilde !== null && $ancienneGuilde !== $guild) {
            $ancienneGuilde->setChef(null);
            $user->setChiefOf(null);
            $entityManager->persist($ancienneGuilde);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $guild->setChef($user);
        $user->setChiefOf($guild);
        $user->setGuild($guild); // S'assurer que le chef est aussi membre de la guilde
        // Le chef de guilde obtient automatiquement les rôles vendeur et comptable
        $user->addVendeurRole();
        $user->addComptableRole();
        $entityManager->persist($guild);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Le chef de guilde a bien été assigné.');
        return $this->redirectToRoute('app_guild');
    }

    #[Route('/quit', name: 'guild_quit')]
    public function quitGuild(EntityManagerInterface $em): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $user->quitGuild();

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Tu as quitté la guilde.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/guild/create', name: 'guild_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $entityManager,
        Wallpaper $wallpaperService
    ): Response {
        if ($request->isMethod('POST')) {
            $name          = $request->request->get('name');
            $allowedToSell = (bool) $request->request->get('allowedtosell', false);
            /** @var UploadedFile|null $imageFile */
            $imageFile     = $request->files->get('image');

            if (!$name) {
                $this->addFlash('error', 'Le nom de la guilde est requis.');
                return $this->redirectToRoute('app_guild');
            }

            $guild = new Guild();
            $guild->setName($name)
                  ->setAllowedToSell($allowedToSell);

            if ($imageFile instanceof UploadedFile) {
                $originalFilename = $imageFile->getClientOriginalName();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/guildes',
                        $originalFilename
                    );
                } catch (FileException $e) {
                    return $this->redirectToRoute('app_guild');
                }
                $guild->setImage('images/guildes/' . $originalFilename);
            }
            $this->addFlash('success', 'La guilde a bien été créée.');
            $entityManager->persist($guild);
            $entityManager->flush();

            return $this->redirectToRoute('app_guild');
        }

        return $this->render('guild/create.html.twig', [
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/guild/delete/{id}', name: 'guild_delete', methods: ['POST'])]
    public function delete(
        int $id, 
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $guild = $entityManager->getRepository(Guild::class)->find($id);

        if (!$guild) {
            $this->addFlash('error', 'Guilde introuvable.');
            return $this->redirectToRoute('app_guild');
        }
        $this->addFlash('success', 'La guilde a bien été supprimée.');
        $entityManager->remove($guild);
        $entityManager->flush();
        return $this->redirectToRoute('app_guild');
    }

    #[Route("/guild/reverse-allow-to-sell/{id}", name: "guild_reverse_allow_to_sell", methods: ['POST'])]
    public function reverseAllowToSell(
        int $id, 
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        /**
         * @var Guild $guild
         */
        $guild = $entityManager->getRepository(Guild::class)->find($id);

        if (!$guild) {
            $this->addFlash('error', 'Guilde introuvable.');
            return $this->redirectToRoute('app_guild');
        }

        $guild->setAllowedToSell(!$guild->isAllowedToSell());
        $entityManager->persist($guild);
        $entityManager->flush();

        $this->addFlash('success', 'La guilde a bien été mise à jour.');
        return $this->redirectToRoute('app_guild');
    }
}
