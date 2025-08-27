<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827155116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dmthread');
        $this->addSql('ALTER TABLE item CHANGE prix prix DOUBLE PRECISION DEFAULT 0');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FE2904019`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FF675F31B`');
        $this->addSql('DROP INDEX IDX_B6BD307FE2904019 ON message');
        $this->addSql('DROP INDEX IDX_B6BD307FF675F31B ON message');
        $this->addSql('ALTER TABLE message ADD is_read TINYINT(1) NOT NULL, ADD sender_id INT NOT NULL, ADD recipient_id INT NOT NULL, DROP edited_at, DROP is_deleted, DROP thread_id, DROP author_id, DROP delivered_at, DROP read_at, CHANGE created_at sent_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FE92F8F78 ON message (recipient_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dmthread (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, last_read_at_a DATETIME DEFAULT NULL, last_read_at_b DATETIME DEFAULT NULL, user_a_id INT NOT NULL, user_b_id INT NOT NULL, INDEX IDX_5B00B12D53EAB07F (user_b_id), INDEX IDX_5B00B12D415F1F91 (user_a_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE item CHANGE prix prix DOUBLE PRECISION DEFAULT \'0\'');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE92F8F78');
        $this->addSql('DROP INDEX IDX_B6BD307FF624B39D ON message');
        $this->addSql('DROP INDEX IDX_B6BD307FE92F8F78 ON message');
        $this->addSql('ALTER TABLE message ADD edited_at DATETIME DEFAULT NULL, ADD is_deleted TINYINT(1) DEFAULT 0 NOT NULL, ADD thread_id INT NOT NULL, ADD author_id INT NOT NULL, ADD delivered_at DATETIME DEFAULT NULL, ADD read_at DATETIME DEFAULT NULL, DROP is_read, DROP sender_id, DROP recipient_id, CHANGE sent_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FE2904019` FOREIGN KEY (thread_id) REFERENCES dmthread (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FF675F31B` FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_B6BD307FE2904019 ON message (thread_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
    }
}
