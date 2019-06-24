<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\Query\CreateTableAttributeCreatedQuery;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190614113455 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->executeQuery(CreateTableAttributeCreatedQuery::QUERY);
    }

    public function down(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
DROP TABLE pimee_franklin_insights_attribute_created
SQL;
        $this->connection->executeQuery($sqlQuery);
    }
}
