<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210507124332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_token DROP CONSTRAINT FK_9315F04EDEEE62D0');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04EDEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_activation_token ALTER token TYPE VARCHAR(128)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_token DROP CONSTRAINT fk_9315f04edeee62d0');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT fk_9315f04edeee62d0 FOREIGN KEY (holder_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_activation_token ALTER token TYPE VARCHAR(255)');
    }
}
