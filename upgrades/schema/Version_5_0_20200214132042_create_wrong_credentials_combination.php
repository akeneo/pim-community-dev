<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200214132042_create_wrong_credentials_combination extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $createTableQuery = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection_wrong_credentials_combination
(
    connection_code     varchar(100) not null,
    username            varchar(100) not null,
    authentication_date datetime     not null,
    PRIMARY KEY (connection_code, username)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;

        $this->addSql($createTableQuery);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
