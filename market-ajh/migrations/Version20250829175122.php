<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829175122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ROLE_COMPTABLE to existing guild chiefs';
    }

    public function up(Schema $schema): void
    {
        // Add ROLE_COMPTABLE to all existing guild chiefs
        $this->addSql("
            UPDATE user 
            SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_COMPTABLE')
            WHERE id IN (
                SELECT chef_id FROM guild WHERE chef_id IS NOT NULL
            ) 
            AND NOT JSON_CONTAINS(roles, '\"ROLE_COMPTABLE\"')
        ");
    }

    public function down(Schema $schema): void
    {
        // Remove ROLE_COMPTABLE from all users
        $this->addSql("
            UPDATE user 
            SET roles = JSON_REMOVE(roles, JSON_UNQUOTE(JSON_SEARCH(roles, 'one', 'ROLE_COMPTABLE')))
            WHERE JSON_CONTAINS(roles, '\"ROLE_COMPTABLE\"')
        ");
    }
}
