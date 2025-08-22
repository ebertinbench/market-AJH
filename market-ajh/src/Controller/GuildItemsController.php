<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\GuildItems;
use App\Form\GuildItemsForm;
use App\Form\ItemSelectType;
use App\Repository\ItemRepository;
use App\Entity\User;
use App\Repository\GuildItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Wallpaper;

#[Route('/items')]
final class GuildItemsController extends AbstractController
{
    #[Route(name: 'app_guild_items_index', methods: ['GET'])]
    public function index(
        GuildItemsRepository $repo, 
        Wallpaper $wallpaperService
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $guild = $user->getGuild();
        return $this->render('items/index.html.twig', [
            'guildItems' => $repo->findBy(['guild' => $guild]), 
            'nomdepage'  => 'Gestion des items de guilde',
            'wallpaper'  => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/new', name: 'app_guild_items_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        GuildItemsRepository $repo,
        Wallpaper $wallpaperService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $guild = $user->getGuild();
        $form  = $this->createForm(ItemSelectType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $gi   = new GuildItems();
            $gi->setGuild($guild);
            $gi->setItem($data['item']);
            $gi->setPrice($data['price']);

            $em->persist($gi);
            $em->flush();
            $this->addFlash('success', 'L\'item a été ajouté avec succès !');
            return $this->redirectToRoute('app_guild_items_index');
        }

        return $this->render('items/new.html.twig', [
            'form'       => $form->createView(),
            'guildItems' => $repo->findBy(['guild' => $guild]),
            'nomdepage'  => 'Ajouter un item de guilde',
            'wallpaper'  => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/items/new', name: 'items_new', methods: ['GET', 'POST'])]
    public function itemsNew(
        Request $request,
        EntityManagerInterface $em,
        GuildItemsRepository $repo,
        Wallpaper $wallpaperService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $guild = $user->getGuild();

        $form = $this->createForm(ItemSelectType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $gi = new GuildItems();
            $gi->setGuild($guild);
            $gi->setItem($data['item']);
            $gi->setPrice($data['price']);
            $gi->setMiseEnVente(true);
            $em->persist($gi);
            $em->flush();
            $this->addFlash('success', 'L\'item a été ajouté avec succès !');
            return $this->redirectToRoute('items_new');
        }

        return $this->render('items/new.html.twig', [
            'form'       => $form->createView(),
            'guildItems' => $repo->findBy(['guild' => $guild]),
            'nomdepage'  => 'Ajouter un item de guilde',
            'wallpaper'  => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/delete/{id}', name: 'app_guild_items_delete', methods: ['POST'])]
    public function delete(
        int $id,
        GuildItemsRepository $repo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }

        $guildItem = $repo->find($id);
        if (!$guildItem) {
            $this->addFlash('error', 'Item introuvable.');
            return $this->redirectToRoute('app_guild_items_index');
        }
        $userGuild = $user->getGuild();
        if ($guildItem->getGuild() !== $userGuild) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_guild_items_index');
        }

        $em->remove($guildItem);
        $em->flush();

        $this->addFlash('success', 'Item supprimé avec succès !');
        return $this->redirectToRoute('app_guild_items_index');
    }

    #[Route('/search', name: 'app_items_search', methods: ['GET'])]
    public function search(
        Request $request,
        GuildItemsRepository $repo,
        Wallpaper $wallpaperService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $guild = $user->getGuild();
        $query = $request->query->get('q', '');

        if ($query) {
            $guildItems = $repo->createQueryBuilder('gi')
                ->join('gi.item', 'i')
                ->where('gi.guild = :guild')
                ->andWhere('i.nom LIKE :query')
                ->setParameter('guild', $guild)
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        } else {
            $guildItems = $repo->findBy(['guild' => $guild]);
        }

        return $this->render('items/index.html.twig', [
            'guildItems' => $guildItems,
            'nomdepage'  => 'Gestion des items de guilde',
            'wallpaper'  => $wallpaperService->getRandomWallpaperName()
        ]);
    }
    #[Route('/change-miseenvente', name:'app_guild_items_change_miseenvente', methods: ['POST'])]
    public function changeMiseEnVente(
        Request $request,
        GuildItemsRepository $repo,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }

        $id = $request->request->get('id');
        $guildItem = $repo->find($id);
        if (!$guildItem) {
            $this->addFlash('error', 'Item introuvable.');
            return $this->redirectToRoute('app_guild_items_index');
        }

        $userGuild = $user->getGuild();
        if ($guildItem->getGuild() !== $userGuild) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_guild_items_index');
        }

        $guildItem->setMiseEnVente(!$guildItem->isMiseEnVente());
        $em->flush();

        $this->addFlash('success', 'Mise en vente modifiée avec succès !');
        return $this->redirectToRoute('app_guild_items_index');
    }

    #[Route('/change-price', name: 'app_guild_items_change_price', methods: ['POST'])]
    public function changePrice(
        Request $request,
        GuildItemsRepository $repo,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isChief()) {
            return $this->redirectToRoute('app_home');
        }

        $id = $request->request->get('id');
        $newPrice = $request->request->get('price');
        $guildItem = $repo->find($id);
        if (!$guildItem) {
            $this->addFlash('error', 'Item introuvable.');
            return $this->redirectToRoute('app_guild_items_index');
        }

        $userGuild = $user->getGuild();
        if ($guildItem->getGuild() !== $userGuild) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_guild_items_index');
        }

        $guildItem->setPrice($newPrice);
        $em->flush();

        $this->addFlash('success', 'Prix modifié avec succès !');
        return $this->redirectToRoute('app_guild_items_index');
    }
}
