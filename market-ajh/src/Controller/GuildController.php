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


final class GuildController extends AbstractController
{
    #[Route('/guild', name: 'app_guild')]
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
    #[Route('/quit', name: 'guild_quit')]
    public function quitGuild(EntityManagerInterface $em): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $user->quitGuild();

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Tu as quitté la guilde.');

        return $this->redirectToRoute('app_home'); // ou vers une autre page
    }
    #[Route('/guild/create', name: 'guild_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
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
                // 1. Récupérer le nom original
                $originalFilename = $imageFile->getClientOriginalName();

                // 2. Déplacer dans public/images/guildes
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/guildes',
                        $originalFilename
                    );
                } catch (FileException $e) {
                    return $this->redirectToRoute('app_guild');
                }

                // 3. Enregistrer le chemin relatif dans l’entité
                $guild->setImage('images/guildes/' . $originalFilename);
            }

            $entityManager->persist($guild);
            $entityManager->flush();

            return $this->redirectToRoute('app_guild');
        }

        return $this->render('guild/create.html.twig');
    }


    
    #[Route('/guild/delete/{id}', name: 'guild_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        $guild = $entityManager->getRepository(Guild::class)->find($id);

        if (!$guild) {
            $this->addFlash('error', 'Guilde introuvable.');
            return $this->redirectToRoute('app_guild');
        }

        $entityManager->remove($guild);
        $entityManager->flush();
        return $this->redirectToRoute('app_guild');
    }
}
