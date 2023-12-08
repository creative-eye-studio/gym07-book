<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231207210109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE planning ADD date_time_start DATETIME NOT NULL, ADD date_time_end DATETIME NOT NULL, DROP jour, DROP heure_debut, DROP heure_fin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE planning ADD jour DATE NOT NULL, ADD heure_debut TIME NOT NULL, ADD heure_fin TIME NOT NULL, DROP date_time_start, DROP date_time_end');
    }
}
