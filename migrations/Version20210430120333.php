<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210430120333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE login_failed_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE login_failed (id INT NOT NULL, target_id INT NOT NULL, ip VARCHAR(15) NOT NULL, client VARCHAR(255) NOT NULL, failed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_69EF84ED158E0B66 ON login_failed (target_id)');
        $this->addSql('ALTER TABLE login_failed ADD CONSTRAINT FK_69EF84ED158E0B66 FOREIGN KEY (target_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE login_failed_id_seq CASCADE');
        $this->addSql('DROP TABLE login_failed');
    }
}
