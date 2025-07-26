<?php

namespace App\Controller;
use App\Entity\Item;
use App\Entity\GuildItems;
use App\Form\GuildItemsForm;
use App\Form\ItemSelectType;
use App\Repository\ItemRepository;
use App\Repository\GuildItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/items')]
final class GuildItemsController extends AbstractController
{
    #[Route(name: 'app_guild_items_index', methods: ['GET'])]
    public function index(GuildItemsRepository $repo): Response
    {
        if (!$this->getUser()->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $user  = $this->getUser();
        $guild = $user->getGuild();
        return $this->render('items/index.html.twig', [
            'guildItems' => $repo->findBy(['guild' => $guild]),  // pour lister ceux déjà ajoutés
            'nomdepage' => 'Gestion des items de guilde',
        ]);
    }

    #[Route('/new', name: 'app_guild_items_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        GuildItemsRepository $repo
    ): Response {
        if (!$this->getUser()->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        // Récupérer la guilde courante
        $user  = $this->getUser();
        $guild = $user->getGuild();

        // Construire le formulaire
        $form = $this->createForm(ItemSelectType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données
            $data = $form->getData();  // ['item'=>Item, 'price'=>float]

            // Créer l'entité de jointure
            $gi = new GuildItems();
            $gi->setGuild($guild);
            $gi->setItem($data['item']);
            $gi->setPrice($data['price']);

            $em->persist($gi);
            $em->flush();


            return $this->redirectToRoute('app_guild_items_index');
        }

        return $this->render('items/new.html.twig', [
            'form'       => $form->createView(),
            'guildItems' => $repo->findBy(['guild' => $guild]),  // pour lister ceux déjà ajoutés
            'nomdepage'  => 'Ajouter un item de guilde',
        ]);
    }
    #[Route('/items/new', name: 'items_new', methods: ['GET', 'POST'])]
    public function itemsNew(
        Request $request,
        EntityManagerInterface $em,
        GuildItemsRepository $repo
    ): Response {
        if (!$this->getUser()->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $user  = $this->getUser();
        $guild = $user->getGuild();

        $form = $this->createForm(ItemSelectType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $gi = new GuildItems();
            $gi->setGuild($guild);
            $gi->setItem($data['item']);
            $gi->setPrice($data['price']);

            $em->persist($gi);
            $em->flush();


            return $this->redirectToRoute('items_new');
        }

        return $this->render('items/new.html.twig', [
            'form'       => $form->createView(),
            'guildItems' => $repo->findBy(['guild' => $guild]),
            'nomdepage'  => 'Ajouter un item de guilde',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_guild_items_delete', methods: ['POST'])]
    public function delete(
        int $id,
        GuildItemsRepository $repo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        if (!$this->getUser()->isChief()) {
            return $this->redirectToRoute('app_home');
        }

        $guildItem = $repo->find($id);
        if (!$guildItem) {
            $this->addFlash('error', 'Item introuvable.');
            return $this->redirectToRoute('app_guild_items_index');
        }

        // Vérifier que l'item appartient à la guilde de l'utilisateur
        $userGuild = $this->getUser()->getGuild();
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
        GuildItemsRepository $repo
    ): Response {
        if (!$this->getUser()->isChief()) {
            return $this->redirectToRoute('app_home');
        }
        $user  = $this->getUser();
        $guild = $user->getGuild();
        $query = $request->query->get('q', '');

        if ($query) {
            // Recherche par nom d'item (relation avec Item)
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
        ]);
    }
}
