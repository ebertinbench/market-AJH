<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemForm;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Wallpaper;

#[Route('/item')]
final class ItemController extends AbstractController
{
    #[Route(name: 'app_item_index', methods: ['GET'])]
    public function index(ItemRepository $itemRepository, Wallpaper $wallpaperService): Response
    {
        return $this->render('item/index.html.twig', [
            'items' => $itemRepository->findAll(),
            'nomdepage' => 'Liste des items',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/new', name: 'app_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Wallpaper $wallpaperService): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemForm::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $item->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement');
                }
            }

            $entityManager->persist($item);
            $entityManager->flush();
            $this->addFlash('success', 'L\'item a été créé avec succès !');
            return $this->redirectToRoute('app_item_index');
        }

        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form,
            'nomdepage' => 'Ajouter un item',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/{id}', name: 'app_item_show', methods: ['GET'])]
    public function show(Item $item, Wallpaper $wallpaperService): Response
    {
        return $this->render('item/show.html.twig', [
            'item' => $item,
            'nomdepage' => 'Détails de l\'item',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, 
        Item $item, 
        EntityManagerInterface $entityManager,
        Wallpaper $wallpaperService
        ): Response
    {
        $form = $this->createForm(ItemForm::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                if ($item->getImage()) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . basename($item->getImage());
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $item->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'L\'item a été modifié avec succès !');
            return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
            'nomdepage' => 'Modifier l\'item',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }

    #[Route('/{id}', name: 'app_item_delete', methods: ['POST'])]
    public function delete(Request $request, 
        Item $item, 
        EntityManagerInterface $entityManager
        ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->getPayload()->getString('_token'))) {
            if ($item->getImage()) {
                $imagePath = $this->getParameter('images_directory') . '/' . basename($item->getImage());
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
