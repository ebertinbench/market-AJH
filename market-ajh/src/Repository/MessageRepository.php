<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Find conversation between two users
     */
    public function findConversation(User $user1, User $user2): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.recipient = :user2) OR (m.sender = :user2 AND m.recipient = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all conversations for a user (returns last message with each user)
     */
    public function findUserConversations(User $user): array
    {
        // Get all messages involving the user
        $messages = $this->createQueryBuilder('m')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        // Group by conversation partner
        $conversations = [];
        foreach ($messages as $message) {
            $partner = ($message->getSender() === $user) ? $message->getRecipient() : $message->getSender();
            $partnerId = $partner->getId();
            
            if (!isset($conversations[$partnerId])) {
                $conversations[$partnerId] = [
                    'partner' => $partner,
                    'lastMessage' => $message,
                    'unreadCount' => 0
                ];
            }
        }

        // Count unread messages for each conversation
        foreach ($conversations as $partnerId => &$conversation) {
            $unreadCount = $this->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.sender = :partner AND m.recipient = :user AND m.isRead = false')
                ->setParameter('partner', $conversation['partner'])
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleScalarResult();
            
            $conversation['unreadCount'] = $unreadCount;
        }

        return array_values($conversations);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(User $sender, User $recipient): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.isRead', ':read')
            ->where('m.sender = :sender AND m.recipient = :recipient AND m.isRead = false')
            ->setParameter('read', true)
            ->setParameter('sender', $sender)
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->execute();
    }

    /**
     * Get users that current user can message (all users except themselves)
     */
    public function findAvailableRecipients(User $currentUser): array
    {
        return $this->getEntityManager()
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->setParameter('currentUser', $currentUser)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
