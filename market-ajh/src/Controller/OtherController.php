<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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

    #[Route('/contact', name:'app_contact', methods:['GET'])]
    public function contact(): Response
    {
        return $this->render('other/contact.html.twig', [
            'controller_name' => 'OtherController',
            'nomdepage' => 'Contact'
        ]);
    }
    #[Route('/contact', name: 'app_contact_submit', methods: ['POST'])]
    public function contactSubmit(MailerInterface $mailer): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        
        $subject = $request->request->get('subject');
        $message = $request->request->get('message');
        $email = (new Email())
            ->from('your_email@example.com')
            ->to('contact@market-ajh.fr')
            ->subject($subject)
            ->text($message);

        $mailer->send($email);

        $this->addFlash('success', 'Votre message a été envoyé avec succès!');
        
        return $this->redirectToRoute('app_contact');
    }
}
