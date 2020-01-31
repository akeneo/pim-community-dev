<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_4_0_20200128151004_franklin_insights_pending_items_to_sync extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $query = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_quality_highlights_pending_items
(
    entity_type varchar(20) not null,
    entity_id varchar(100) not null,
    action varchar(20) null,
    lock_id varchar(60) default '' not null,
    UNIQUE KEY(entity_type, entity_id, lock_id)

) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
SQL;

        $this->connection->executeQuery($query);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
