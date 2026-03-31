<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330215724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE payment ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE report ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE vehicle ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP updated_at');
        $this->addSql('ALTER TABLE payment ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE report ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE "user" ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE vehicle DROP updated_at');
    }
}
