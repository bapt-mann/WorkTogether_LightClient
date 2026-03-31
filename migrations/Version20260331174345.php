<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260331174345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit_histo ADD update_date DATETIME NOT NULL, ADD unit_description VARCHAR(255) NOT NULL, ADD rental_id INT DEFAULT NULL, DROP start_date, DROP end_date, DROP size, CHANGE label changes VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE unit_histo ADD CONSTRAINT FK_62951B21A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id)');
        $this->addSql('CREATE INDEX IDX_62951B21A7CF2329 ON unit_histo (rental_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit_histo DROP FOREIGN KEY FK_62951B21A7CF2329');
        $this->addSql('DROP INDEX IDX_62951B21A7CF2329 ON unit_histo');
        $this->addSql('ALTER TABLE unit_histo ADD start_date DATE NOT NULL, ADD end_date DATE DEFAULT NULL, ADD label VARCHAR(255) NOT NULL, ADD size NUMERIC(10, 2) NOT NULL, DROP update_date, DROP changes, DROP unit_description, DROP rental_id');
    }
}
