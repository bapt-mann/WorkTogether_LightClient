<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205105411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accountant (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bay (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, size SMALLINT NOT NULL, state_id INT NOT NULL, INDEX IDX_E12D55225D83CC1 (state_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bay_histo (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, label VARCHAR(255) NOT NULL, size SMALLINT NOT NULL, bay_id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_2370A044DF9BA23B (bay_id), INDEX IDX_2370A0445D83CC1 (state_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, technician_id INT NOT NULL, unit_id INT DEFAULT NULL, bay_id INT DEFAULT NULL, INDEX IDX_D11814ABE6C5D496 (technician_id), INDEX IDX_D11814ABF8BD700D (unit_id), INDEX IDX_D11814ABDF9BA23B (bay_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, price NUMERIC(10, 5) NOT NULL, units_number SMALLINT NOT NULL, reduction SMALLINT DEFAULT NULL, duration_in_days SMALLINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rental (id INT AUTO_INCREMENT NOT NULL, purchase_date DATE NOT NULL, company_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, offer_id INT NOT NULL, INDEX IDX_1619C27D979B1AD6 (company_id), INDEX IDX_1619C27D9395C3F3 (customer_id), INDEX IDX_1619C27D53C674EE (offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE state (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, size NUMERIC(10, 2) NOT NULL, bay_id INT NOT NULL, rental_id INT DEFAULT NULL, state_id INT NOT NULL, INDEX IDX_DCBB0C53DF9BA23B (bay_id), INDEX IDX_DCBB0C53A7CF2329 (rental_id), INDEX IDX_DCBB0C535D83CC1 (state_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE unit_histo (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, label VARCHAR(255) NOT NULL, size NUMERIC(10, 2) NOT NULL, unit_id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_62951B21F8BD700D (unit_id), INDEX IDX_62951B215D83CC1 (state_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE unit_rental_histo (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, unit_id INT NOT NULL, rental_id INT NOT NULL, INDEX IDX_A0DA0952F8BD700D (unit_id), UNIQUE INDEX UNIQ_A0DA0952A7CF2329 (rental_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE bay ADD CONSTRAINT FK_E12D55225D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE bay_histo ADD CONSTRAINT FK_2370A044DF9BA23B FOREIGN KEY (bay_id) REFERENCES bay (id)');
        $this->addSql('ALTER TABLE bay_histo ADD CONSTRAINT FK_2370A0445D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABE6C5D496 FOREIGN KEY (technician_id) REFERENCES technician (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABDF9BA23B FOREIGN KEY (bay_id) REFERENCES bay (id)');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53DF9BA23B FOREIGN KEY (bay_id) REFERENCES bay (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C535D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE unit_histo ADD CONSTRAINT FK_62951B21F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE unit_histo ADD CONSTRAINT FK_62951B215D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE unit_rental_histo ADD CONSTRAINT FK_A0DA0952F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE unit_rental_histo ADD CONSTRAINT FK_A0DA0952A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bay DROP FOREIGN KEY FK_E12D55225D83CC1');
        $this->addSql('ALTER TABLE bay_histo DROP FOREIGN KEY FK_2370A044DF9BA23B');
        $this->addSql('ALTER TABLE bay_histo DROP FOREIGN KEY FK_2370A0445D83CC1');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABE6C5D496');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABF8BD700D');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABDF9BA23B');
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D979B1AD6');
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D9395C3F3');
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D53C674EE');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53DF9BA23B');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53A7CF2329');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C535D83CC1');
        $this->addSql('ALTER TABLE unit_histo DROP FOREIGN KEY FK_62951B21F8BD700D');
        $this->addSql('ALTER TABLE unit_histo DROP FOREIGN KEY FK_62951B215D83CC1');
        $this->addSql('ALTER TABLE unit_rental_histo DROP FOREIGN KEY FK_A0DA0952F8BD700D');
        $this->addSql('ALTER TABLE unit_rental_histo DROP FOREIGN KEY FK_A0DA0952A7CF2329');
        $this->addSql('DROP TABLE accountant');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE bay');
        $this->addSql('DROP TABLE bay_histo');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE rental');
        $this->addSql('DROP TABLE state');
        $this->addSql('DROP TABLE technician');
        $this->addSql('DROP TABLE unit');
        $this->addSql('DROP TABLE unit_histo');
        $this->addSql('DROP TABLE unit_rental_histo');
        $this->addSql('DROP TABLE user');
    }
}
