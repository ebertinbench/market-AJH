<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\Message;
use App\Repository\MessageRepository;

class MessageSecurityService
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    /**
     * Check if user can access a conversation with another user
     */
    public function canAccessConversation(User $currentUser, User $otherUser): bool
    {
        // Users can start new conversations with anyone
        // Or access existing conversations they're part of
        return true; // For now, allow all registered users to message each other
    }

    /**
     * Check if user can read a specific message
     */
    public function canReadMessage(User $user, Message $message): bool
    {
        return $message->getSender() === $user || $message->getRecipient() === $user;
    }

    /**
     * Check if user has sent too many messages recently (anti-spam)
     */
    public function checkSpamProtection(User $user, int $maxMessagesPerHour = 50): bool
    {
        $oneHourAgo = new \DateTime('-1 hour');
        
        $recentMessagesCount = $this->messageRepository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.sender = :user')
            ->andWhere('m.sentAt >= :oneHourAgo')
            ->setParameter('user', $user)
            ->setParameter('oneHourAgo', $oneHourAgo)
            ->getQuery()
            ->getSingleScalarResult();

        return $recentMessagesCount < $maxMessagesPerHour;
    }

    /**
     * Sanitize message content
     */
    public function sanitizeContent(string $content): string
    {
        // Remove potential HTML/script tags
        $content = strip_tags($content);
        
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Trim
        $content = trim($content);
        
        return $content;
    }

    /**
     * Check if users are blocked from messaging each other
     * (Future feature - could implement user blocking)
     */
    public function areUsersBlocked(User $user1, User $user2): bool
    {
        // TODO: Implement user blocking feature
        return false;
    }
}
