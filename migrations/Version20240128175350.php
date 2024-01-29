<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240128175350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email (id INT AUTO_INCREMENT NOT NULL, sending_user_id INT DEFAULT NULL, recipient_user_id INT DEFAULT NULL, identifier BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) NOT NULL, INDEX IDX_E7927C747D7C4E7D (sending_user_id), INDEX IDX_E7927C74B15EFB97 (recipient_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C747D7C4E7D FOREIGN KEY (sending_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C74B15EFB97 FOREIGN KEY (recipient_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email DROP FOREIGN KEY FK_E7927C747D7C4E7D');
        $this->addSql('ALTER TABLE email DROP FOREIGN KEY FK_E7927C74B15EFB97');
        $this->addSql('DROP TABLE email');
    }
}
