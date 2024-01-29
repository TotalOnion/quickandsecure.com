<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240125105401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secret ADD created_by_id INT DEFAULT NULL, ADD read_by_id INT DEFAULT NULL, ADD must_be_logged_in_to_read TINYINT(1) NOT NULL, ADD description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE secret ADD CONSTRAINT FK_5CA2E8E5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE secret ADD CONSTRAINT FK_5CA2E8E5F5675CD0 FOREIGN KEY (read_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5CA2E8E5B03A8386 ON secret (created_by_id)');
        $this->addSql('CREATE INDEX IDX_5CA2E8E5F5675CD0 ON secret (read_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secret DROP FOREIGN KEY FK_5CA2E8E5B03A8386');
        $this->addSql('ALTER TABLE secret DROP FOREIGN KEY FK_5CA2E8E5F5675CD0');
        $this->addSql('DROP INDEX IDX_5CA2E8E5B03A8386 ON secret');
        $this->addSql('DROP INDEX IDX_5CA2E8E5F5675CD0 ON secret');
        $this->addSql('ALTER TABLE secret DROP created_by_id, DROP read_by_id, DROP must_be_logged_in_to_read, DROP description');
    }
}
