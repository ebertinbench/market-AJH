<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Guild;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class ItemFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $jsonPath = __DIR__ . '/items.json';
        $data = json_decode(file_get_contents($jsonPath), true);

        foreach ($data['items'] as $itemData) {
            $item = new \App\Entity\Item(
                isset($itemData['image']) ? basename($itemData['image']) : null,
                $itemData['palier'] ?? null,
                $itemData['description'] ?? null,
                $itemData['nom'] ?? null,
                $itemData['prix'] ?? 0
            );
            $manager->persist($item);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['items'];
    }

}