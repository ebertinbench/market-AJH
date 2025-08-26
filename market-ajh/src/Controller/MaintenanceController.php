<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController
{
    #[Route('/maintenance-bypass', name: 'maintenance_bypass', methods: ['POST'])]
    public function bypassMaintenance(Request $request): Response
    {
        $code = $request->request->get('maintenance_bypass');
        $expectedCode = $_ENV['MAINTENANCE_BYPASS_CODE'] ?? '';
        
        if ($code === $expectedCode) {
            // Créer un cookie qui expire dans 1 heure (3600 secondes)
            $response = $this->redirectToRoute('app_home');
            $response->headers->setCookie(
                new Cookie(
                    name: 'maintenance_bypass',
                    value: hash('sha256', $code . date('Y-m-d-H')), // Hash sécurisé basé sur le code et l'heure
                    expire: time() + 3600, // 1 heure
                    path: '/',
                    secure: $request->isSecure(),
                    httpOnly: true,
                    sameSite: 'Lax'
                )
            );
            
            return $response;
        }
        
        // Si le code est incorrect, retourner à la page de maintenance avec un message d'erreur
        return $this->render('maintenance.html.twig', [
            'error' => 'Code d\'accès incorrect'
        ]);
    }
}
