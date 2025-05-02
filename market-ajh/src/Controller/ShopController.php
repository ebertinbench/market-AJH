<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemRepository;

final class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(): Response
    {
        return $this->render('shop/index.html.twig', [
            'controller_name' => 'ShopController',
        ]);
    }

    #[Route('/shop/list', name: 'app_shop_list')]
    public function list(ItemRepository $itemRepository ): Response
    {
        return $this->render('shop/list.html.twig', [
            'controller_name' => 'ShopController',
            'nomdepage' => 'Liste des items',
            'items' => $itemRepository->findAll(),
        ]);
    }
}
