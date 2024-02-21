<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221184728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_event DROP FOREIGN KEY FK_A6E34B28A832C1C9');
        $this->addSql('DROP TABLE email_event');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_event (id INT AUTO_INCREMENT NOT NULL, email_id INT NOT NULL, timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', event VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_A6E34B28A832C1C9 (email_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE email_event ADD CONSTRAINT FK_A6E34B28A832C1C9 FOREIGN KEY (email_id) REFERENCES email (id)');
    }
}
