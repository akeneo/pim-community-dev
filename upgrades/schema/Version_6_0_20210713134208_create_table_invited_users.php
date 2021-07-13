<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210713134208_create_table_invited_users extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
CREATE TABLE akeneo_free_trial_invited_user (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    status VARCHAR(15) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
