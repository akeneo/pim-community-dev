<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\Query\CreateTableAttributeAddedToFamilyQuery;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190618122030 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->executeQuery(CreateTableAttributeAddedToFamilyQuery::QUERY);
    }

    public function down(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
DROP TABLE pimee_franklin_insights_attribute_added_to_family
SQL;
        $this->connection->executeQuery($sqlQuery);
    }
}
