<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505142132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_activation_token DROP CONSTRAINT FK_BF8D6ADCDEEE62D0');
        $this->addSql('ALTER TABLE email_activation_token ADD CONSTRAINT FK_BF8D6ADCDEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_data DROP CONSTRAINT FK_D772BFAADEEE62D0');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAADEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_data DROP CONSTRAINT fk_d772bfaadeee62d0');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT fk_d772bfaadeee62d0 FOREIGN KEY (holder_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_activation_token DROP CONSTRAINT fk_bf8d6adcdeee62d0');
        $this->addSql('ALTER TABLE email_activation_token ADD CONSTRAINT fk_bf8d6adcdeee62d0 FOREIGN KEY (holder_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
