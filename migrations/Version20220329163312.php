<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329163312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE maxfield_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE maxfield (id INT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, json_data JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_79AB10BB7E3C61F9 ON maxfield (owner_id)');
        $this->addSql('ALTER TABLE maxfield ADD CONSTRAINT FK_79AB10BB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES system_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE maxfield_id_seq CASCADE');
        $this->addSql('DROP TABLE maxfield');
    }
}
