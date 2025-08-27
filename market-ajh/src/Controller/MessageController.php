<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Services\MessageSecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/messages')]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageRepository $messageRepository,
        private UserRepository $userRepository,
        private MessageSecurityService $messageSecurityService
    ) {}

    #[Route('/', name: 'app_messages_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $conversations = $this->messageRepository->findUserConversations($user);

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
            'current_user' => $user,
            'nomdepage' => 'Messagerie'
        ]);
    }

    #[Route('/new', name: 'app_messages_new')]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $message = new Message();
        $message->setSender($user);

        $availableRecipients = $this->messageRepository->findAvailableRecipients($user);

        $form = $this->createForm(MessageType::class, $message, [
            'show_recipient' => true,
            'available_recipients' => $availableRecipients
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check spam protection
            if (!$this->messageSecurityService->checkSpamProtection($user)) {
                $this->addFlash('error', 'Vous envoyez trop de messages. Veuillez patienter avant d\'envoyer un nouveau message.');
                return $this->redirectToRoute('app_messages_new');
            }

            // Sanitize content
            $sanitizedContent = $this->messageSecurityService->sanitizeContent($message->getContent());
            $message->setContent($sanitizedContent);

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->addFlash('success', 'Message envoyé avec succès !');
            
            return $this->redirectToRoute('app_messages_conversation', [
                'userId' => $message->getRecipient()->getId()
            ]);
        }

        return $this->render('message/new.html.twig', [
            'form' => $form->createView(),
            'available_recipients' => $availableRecipients,
            'nomdepage' => 'Nouvelle conversation'
        ]);
    }

    #[Route('/conversation/{userId}', name: 'app_messages_conversation')]
    public function conversation(int $userId, Request $request): Response
    {
        $user = $this->getUser();
        $recipient = $this->userRepository->find($userId);

        if (!$recipient) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($recipient === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas vous envoyer un message à vous-même');
            return $this->redirectToRoute('app_messages_index');
        }

        // Get conversation messages and verify user is part of the conversation
        $messages = $this->messageRepository->findConversation($user, $recipient);
        
        // Security check: If no messages exist between users, verify this is a legitimate attempt to start a conversation
        if (empty($messages)) {
            // This is a new conversation - allow it
        } else {
            // Verify that the current user is actually part of this conversation
            $userIsPartOfConversation = false;
            foreach ($messages as $message) {
                if ($message->getSender() === $user || $message->getRecipient() === $user) {
                    $userIsPartOfConversation = true;
                    break;
                }
            }
            
            if (!$userIsPartOfConversation) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette conversation');
            }
        }

        // Mark messages from this user as read
        $this->messageRepository->markAsRead($recipient, $user);

        // Create form for new message
        $newMessage = new Message();
        $newMessage->setSender($user);
        $newMessage->setRecipient($recipient);

        $form = $this->createForm(MessageType::class, $newMessage, [
            'show_recipient' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check spam protection
            if (!$this->messageSecurityService->checkSpamProtection($user)) {
                $this->addFlash('error', 'Vous envoyez trop de messages. Veuillez patienter avant d\'envoyer un nouveau message.');
                return $this->redirectToRoute('app_messages_conversation', ['userId' => $userId]);
            }

            // Sanitize content
            $sanitizedContent = $this->messageSecurityService->sanitizeContent($newMessage->getContent());
            $newMessage->setContent($sanitizedContent);

            $this->entityManager->persist($newMessage);
            $this->entityManager->flush();

            $this->addFlash('success', 'Message envoyé !');
            
            return $this->redirectToRoute('app_messages_conversation', [
                'userId' => $userId
            ]);
        }

        return $this->render('message/conversation.html.twig', [
            'messages' => $messages,
            'recipient' => $recipient,
            'current_user' => $user,
            'nomdepage' => 'Conversation',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users', name: 'app_messages_users')]
    public function users(): Response
    {
        $user = $this->getUser();
        $availableUsers = $this->messageRepository->findAvailableRecipients($user);

        return $this->render('message/users.html.twig', [
            'users' => $availableUsers,
            'current_user' => $user,
            'nomdepage' => 'Utilisateurs',
        ]);
    }

    #[Route('/mark-read/{messageId}', name: 'app_messages_mark_read', methods: ['POST'])]
    public function markAsRead(int $messageId): Response
    {
        $user = $this->getUser();
        $message = $this->entityManager->getRepository(Message::class)->find($messageId);

        if (!$message || $message->getRecipient() !== $user) {
            throw $this->createNotFoundException('Message non trouvé');
        }

        $message->setIsRead(true);
        $this->entityManager->flush();

        return new Response('OK');
    }
}
