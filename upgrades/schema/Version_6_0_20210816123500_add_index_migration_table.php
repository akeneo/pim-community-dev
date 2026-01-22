<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

final class Version_6_0_20210816123500_add_index_migration_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS pim_index_migration(
                `index_alias` VARCHAR(100) NOT NULL,
                `hash` VARCHAR(100) NOT NULL,
                `values` JSON NOT NULL,
                INDEX migration_index (`index_alias`,`hash`),
                UNIQUE KEY `unique_idx` (`index_alias`,`hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
