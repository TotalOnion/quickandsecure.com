<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505094223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secret DROP INDEX UNIQ_5CA2E8E5989D9B62, ADD INDEX slug_idx (slug)');
        $this->addSql('ALTER TABLE secret CHANGE slug slug VARCHAR(7) DEFAULT NULL, CHANGE data data LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secret DROP INDEX slug_idx, ADD UNIQUE INDEX UNIQ_5CA2E8E5989D9B62 (slug)');
        $this->addSql('ALTER TABLE secret CHANGE slug slug VARCHAR(7) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE data data LONGBLOB NOT NULL');
    }
}
