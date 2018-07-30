<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180321142916 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D3EBB69D');
        $this->addSql('DROP INDEX IDX_8D93D649D3EBB69D ON user');
        $this->addSql('ALTER TABLE user CHANGE fornecedor_id supplier_id INT DEFAULT NULL, CHANGE nome name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6492ADD6D8C ON user (supplier_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492ADD6D8C');
        $this->addSql('DROP INDEX IDX_8D93D6492ADD6D8C ON user');
        $this->addSql('ALTER TABLE user CHANGE supplier_id fornecedor_id INT DEFAULT NULL, CHANGE name nome VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D3EBB69D FOREIGN KEY (fornecedor_id) REFERENCES supplier (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D3EBB69D ON user (fornecedor_id)');
    }
}
