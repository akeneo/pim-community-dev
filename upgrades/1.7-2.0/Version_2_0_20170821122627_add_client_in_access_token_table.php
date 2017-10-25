<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_2_0_20170821122627_add_client_in_access_token_table extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE pim_api_access_token ADD client INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pim_api_access_token ADD CONSTRAINT FK_BD5E4023C7440455 ' +
            ' FOREIGN KEY (client) REFERENCES pim_api_client (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BD5E4023C7440455 ON pim_api_access_token (client);');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
