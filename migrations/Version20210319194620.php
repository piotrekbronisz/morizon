<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210319194620 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE city ADD alias VARCHAR(64) NOT NULL');
        $this->addSql("INSERT INTO CITY (id,created_at,updated_at,name,alias) values (1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Gdańsk', 'gdansk')");
        $this->addSql("INSERT INTO CITY (id,created_at,updated_at,name,alias) values (2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Kraków', 'krakow')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE city DROP alias');
    }
}
