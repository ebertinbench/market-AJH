<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use App\Services\Wallpaper;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823103931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item CHANGE prix prix DOUBLE PRECISION DEFAULT 0');
        $this->addSql('ALTER TABLE user ADD wallpaper VARCHAR(255) NOT NULL DEFAULT \'\wallpaper1.png\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item CHANGE prix prix DOUBLE PRECISION DEFAULT \'0\'');
        $this->addSql('ALTER TABLE user DROP wallpaper');
    }
}
