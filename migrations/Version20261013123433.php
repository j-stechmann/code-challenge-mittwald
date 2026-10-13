<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261013123433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device ADD borrower_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E11CE312B FOREIGN KEY (borrower_id) REFERENCES employee (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_92FB68E11CE312B ON device (borrower_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP CONSTRAINT FK_92FB68E11CE312B');
        $this->addSql('DROP INDEX IDX_92FB68E11CE312B');
        $this->addSql('ALTER TABLE device DROP borrower_id');
    }
}
