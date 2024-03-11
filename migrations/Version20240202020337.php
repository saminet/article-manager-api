<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202020337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employment ADD person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE employment ADD CONSTRAINT FK_BF089C98217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_BF089C98217BBB47 ON employment (person_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employment DROP FOREIGN KEY FK_BF089C98217BBB47');
        $this->addSql('DROP INDEX IDX_BF089C98217BBB47 ON employment');
        $this->addSql('ALTER TABLE employment DROP person_id');
    }
}
