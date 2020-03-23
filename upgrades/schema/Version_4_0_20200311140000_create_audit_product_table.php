<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20200311140000_create_audit_product_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_audit_product(
    connection_code VARCHAR(100) NOT NULL,
    event_datetime DATETIME NOT NULL,
    event_count INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    updated DATETIME NOT NULL,
    PRIMARY KEY (event_datetime, connection_code, event_type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;

        $this->addSql($sql);
        $this->addSql('DROP TABLE IF EXISTS akeneo_connectivity_connection_audit');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
