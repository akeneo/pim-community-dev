<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_3_2_20190624081152_update_comment_foreign_key extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pim_comment_comment DROP FOREIGN KEY FK_2A32D03DF675F31B');
        $this->addSql('ALTER TABLE pim_comment_comment ADD CONSTRAINT FK_2A32D03DF675F31B FOREIGN KEY (author_id) REFERENCES oro_user (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pim_comment_comment DROP FOREIGN KEY FK_2A32D03DF675F31B');
        $this->addSql('ALTER TABLE pim_comment_comment ADD CONSTRAINT FK_2A32D03DF675F31B FOREIGN KEY (author_id) REFERENCES oro_user (id)');
    }
}
