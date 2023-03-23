<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323110319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module DROP miniature');
        $this->addSql('ALTER TABLE programme CHANGE is_valid is_valid TINYINT(1) DEFAULT FALSE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module ADD miniature LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE programme CHANGE is_valid is_valid TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
