<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Guild; 

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $guildNames = [
            'Ah-Jin',
            'Red Eclispe',
            'Moonlit Black Cats',
            'Eximore',
            "Haima",
            "Les Lames d'Or",
            "Odd-Eyes",
            "Healers of life",
            "Lost Souls",
        ];

        foreach ($guildNames as $name) {
            $guild = new Guild($name);
            $manager->persist($guild);
        }

        $manager->flush();
    }
}
