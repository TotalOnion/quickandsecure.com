<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240219152339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX associated_entity_id_idx ON event_log (associated_entity_id)');
        $this->addSql('CREATE INDEX entity_class_name_idx ON event_log (entity_class_name)');
        $this->addSql('CREATE INDEX event_idx ON event_log (event)');
        $this->addSql('CREATE INDEX timestamp_idx ON event_log (timestamp)');
        $this->addSql('ALTER TABLE secret ADD title VARCHAR(255) DEFAULT NULL, ADD expires_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX associated_entity_id_idx ON event_log');
        $this->addSql('DROP INDEX entity_class_name_idx ON event_log');
        $this->addSql('DROP INDEX event_idx ON event_log');
        $this->addSql('DROP INDEX timestamp_idx ON event_log');
        $this->addSql('ALTER TABLE secret DROP title, DROP expires_on');
    }
}
