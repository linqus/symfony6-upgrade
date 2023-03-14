<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314175236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE topic ADD updated_by_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME NULL, CHANGE updated_at updated_at DATETIME NULL');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_9D40DE1B896DBBDE ON topic (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B896DBBDE');
        $this->addSql('DROP INDEX IDX_9D40DE1B896DBBDE ON topic');
        $this->addSql('ALTER TABLE topic DROP updated_by_id, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
