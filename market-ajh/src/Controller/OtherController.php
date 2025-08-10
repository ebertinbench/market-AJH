<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/other')]
final class OtherController extends AbstractController
{
    #[Route('', name: 'app_other')]
    public function index(): Response
    {
        return $this->render('other/index.html.twig', [
            'controller_name' => 'OtherController',
        ]);
    }

    #[Route('/conditions-utilisation', name: 'app_conditions_utilisation')]
    public function conditionsUtilisation(): Response
    {
        return $this->render('other/conditions_utilisation.html.twig', [
            'controller_name' => 'OtherController',
            'nomdepage' => 'Conditions d\'utilisation'
        ]);
    }
}
