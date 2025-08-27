<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to create message table for messaging system
 */
final class Version20250827162000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create message table for user messaging system';
    }

    public function up(Schema $schema): void
    {
        // Create message table
        $this->addSql('CREATE TABLE message (
            id INT AUTO_INCREMENT NOT NULL, 
            sender_id INT NOT NULL, 
            recipient_id INT NOT NULL, 
            content LONGTEXT NOT NULL, 
            sent_at DATETIME NOT NULL, 
            is_read TINYINT(1) NOT NULL DEFAULT 0, 
            INDEX IDX_B6BD307FF624B39D (sender_id), 
            INDEX IDX_B6BD307FE92F8F78 (recipient_id), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints first
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE92F8F78');
        
        // Drop the table
        $this->addSql('DROP TABLE message');
    }
}
