<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180406144140 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appointment ADD last_updated_user_id INT DEFAULT NULL, ADD last_updated_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84451DFD776 FOREIGN KEY (last_updated_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FE38F84451DFD776 ON appointment (last_updated_user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84451DFD776');
        $this->addSql('DROP INDEX IDX_FE38F84451DFD776 ON appointment');
        $this->addSql('ALTER TABLE appointment DROP last_updated_user_id, DROP last_updated_date');
    }
}
