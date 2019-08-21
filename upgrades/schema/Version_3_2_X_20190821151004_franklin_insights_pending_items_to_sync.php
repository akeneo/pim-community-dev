<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\Query\CreateTablePendingItemsQuery;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_3_2_X_20190821151004_franklin_insights_pending_items_to_sync extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->connection->executeQuery(CreateTablePendingItemsQuery::QUERY);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
DROP TABLE pimee_franklin_insights_pending_items
SQL;
        $this->connection->executeQuery($sqlQuery);
    }
}
