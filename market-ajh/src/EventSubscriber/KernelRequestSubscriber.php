<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;

class KernelRequestSubscriber implements EventSubscriberInterface
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onMaintenance', \PHP_INT_MAX - 1000],
            ],
        ];
    }

    public function onMaintenance(RequestEvent $event): void
    {
        /** @var bool $isMaintenance */
        $isMaintenance = \filter_var($_ENV['MAINTENANCE_MODE'] ?? '0', \FILTER_VALIDATE_BOOLEAN);

        if ($isMaintenance) {
            $request = $event->getRequest();
            
            // Vérifier si l'utilisateur a un cookie de bypass valide
            if ($this->hasValidBypassCookie($request)) {
                return; // Ne pas afficher la page de maintenance
            }
            
            // Permettre l'accès à la route de bypass
            if ($request->getPathInfo() === '/maintenance-bypass') {
                return;
            }
            
            $event->setResponse(new Response(
                $this->twig->render('maintenance.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE,
            ));
            $event->stopPropagation();
        }
    }
    
    private function hasValidBypassCookie($request): bool
    {
        $bypassCookie = $request->cookies->get('maintenance_bypass');
        
        if (!$bypassCookie) {
            return false;
        }
        
        // Générer le hash attendu pour l'heure actuelle
        $expectedCode = $_ENV['MAINTENANCE_BYPASS_CODE'] ?? '';
        $expectedHash = hash('sha256', $expectedCode . date('Y-m-d-H'));
        
        return $bypassCookie === $expectedHash;
    }
}