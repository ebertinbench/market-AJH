<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $filePath = __DIR__ . '/users.json';
        if (!file_exists($filePath)) {
            throw new RuntimeException("Le fichier users.json est introuvable.");
        }

        $usersData = json_decode(file_get_contents($filePath), true);
        if (!is_array($usersData)) {
            throw new RuntimeException("Le contenu de users.json est invalide.");
        }

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles($userData['roles']);
            $user->setWallpaper('wallpaper1.png');
            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user'];
    }
}
