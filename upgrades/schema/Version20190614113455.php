<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190614113455 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_created(
    attribute_code VARCHAR(100) NOT NULL,
    attribute_type VARCHAR(255) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)', 
    INDEX (attribute_code),
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
        $this->connection->executeQuery($sqlQuery);
    }

    public function down(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
DROP TABLE pimee_franklin_insights_attribute_created
SQL;
        $this->connection->executeQuery($sqlQuery);
    }
}
