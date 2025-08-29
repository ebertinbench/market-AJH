<?php

require 'vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Charger l'environnement
(new Dotenv())->bootEnv(__DIR__.'/.env');

// Créer le kernel
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();

// Obtenir le conteneur
$container = $kernel->getContainer();

// Obtenir l'EntityManager
$entityManager = $container->get('doctrine.orm.entity_manager');

// Test : Récupérer tous les chefs de guilde et leur assigner le rôle vendeur
$guildRepository = $entityManager->getRepository(\App\Entity\Guild::class);
$guilds = $guildRepository->findAll();

echo "=== Test d'assignation du rôle ROLE_VENDEUR aux chefs de guilde ===\n";

foreach ($guilds as $guild) {
    $chef = $guild->getChef();
    if ($chef) {
        echo "Guilde: " . $guild->getName() . "\n";
        echo "Chef: " . $chef->getUsername() . "\n";
        echo "Rôles avant: " . implode(', ', $chef->getRoles()) . "\n";
        
        // Ajouter le rôle vendeur
        $chef->addVendeurRole();
        
        echo "Rôles après: " . implode(', ', $chef->getRoles()) . "\n";
        echo "A le rôle vendeur: " . ($chef->hasVendeurRole() ? 'Oui' : 'Non') . "\n";
        echo "---\n";
    }
}

// Persister les changements
$entityManager->flush();

echo "Tous les chefs de guilde ont maintenant le rôle ROLE_VENDEUR.\n";
