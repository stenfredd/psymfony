<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210504104919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_data (id INT NOT NULL, holder_id INT NOT NULL, nickname VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAADEEE62D0 ON user_data (holder_id)');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAADEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD user_data_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E96FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E96FF8BF36 ON users (user_data_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "users" DROP CONSTRAINT FK_1483A5E96FF8BF36');
        $this->addSql('DROP SEQUENCE user_data_id_seq CASCADE');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('DROP INDEX UNIQ_1483A5E96FF8BF36');
        $this->addSql('ALTER TABLE "users" DROP user_data_id');
    }
}
