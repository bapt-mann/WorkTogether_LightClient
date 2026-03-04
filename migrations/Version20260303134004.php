<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303134004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit_rental_histo DROP INDEX UNIQ_A0DA0952A7CF2329, ADD INDEX IDX_A0DA0952A7CF2329 (rental_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit_rental_histo DROP INDEX IDX_A0DA0952A7CF2329, ADD UNIQUE INDEX UNIQ_A0DA0952A7CF2329 (rental_id)');
    }
}
