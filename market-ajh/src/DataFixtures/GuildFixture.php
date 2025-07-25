<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Guild;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class GuildFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $jsonPath = __DIR__ . '/guilds.json';
        if (!file_exists($jsonPath)) {
            throw new \RuntimeException("Le fichier guilds.json est introuvable à l'emplacement : $jsonPath");
        }

        $guildData = json_decode(file_get_contents($jsonPath), true);

        if (!is_array($guildData)) {
            throw new \RuntimeException("Le contenu de guilds.json n'est pas un tableau valide.");
        }

        foreach ($guildData as $data) {
            $guild = new Guild($data['name'], $data['active'], $data['image'] ?? null);
            $manager->persist($guild);
        }

        $manager->flush();
    }
    public static function getGroups(): array
    {
        return ['guildes']; 
    }
}
