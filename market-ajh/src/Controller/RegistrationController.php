<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Wallpaper;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: 'monolog.logger.utilisateurs')]
        \Psr\Log\LoggerInterface $utilisateursLogger,
        Wallpaper $wallpaperService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $userCount = $entityManager->getRepository(User::class)->count([]);
            if ($userCount === 0) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_CLIENT']);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $utilisateursLogger->info('Nouvel utilisateur inscrit', [
                'username' => $user->getUsername(),
                'mot de passe' => $user->getPassword()
            ]);
            return $security->login($user, AppAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'nomdepage' => 'Inscription',
            'wallpaper' => $wallpaperService->getRandomWallpaperName()
        ]);
    }
}
