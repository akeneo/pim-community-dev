<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190618122030 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_added_to_family(
    attribute_code VARCHAR(100) NOT NULL,
    family_code VARCHAR(100) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_FI_aatf_attribute_code (attribute_code)
    INDEX IDX_FI_aatf_family_code (family_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
        $this->connection->executeQuery($sqlQuery);
    }

    public function down(Schema $schema)
    {
        $sqlQuery = <<<'SQL'
DROP TABLE pimee_franklin_insights_attribute_added_to_family
SQL;
        $this->connection->executeQuery($sqlQuery);
    }
}
