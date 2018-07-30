<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180321142633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, fornecedor_id INT DEFAULT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, nome VARCHAR(255) NOT NULL, INDEX IDX_8D93D649D3EBB69D (fornecedor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D3EBB69D FOREIGN KEY (fornecedor_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE appointment CHANGE data date DATE NOT NULL, CHANGE hora hour TIME NOT NULL, CHANGE duracao duration VARCHAR(10) NOT NULL, CHANGE contacto contact VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE appointment CHANGE date data DATE NOT NULL, CHANGE hour hora TIME NOT NULL, CHANGE duration duracao VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE contact contacto VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
