<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210331105730_remove_datagrid_view_unique_label_constraint
    extends AbstractMigration
    implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $selectUniqueConstraint = <<< SQL
SELECT DISTINCT CONSTRAINT_NAME
FROM information_schema.TABLE_CONSTRAINTS
WHERE table_name = 'pim_datagrid_view' AND constraint_type = 'UNIQUE' AND TABLE_SCHEMA = 'akeneo_pim';
SQL;
        $uniqueConstraintKeyName = $this->dbalConnection()->executeQuery($selectUniqueConstraint)->fetch(FetchMode::COLUMN);

        $this->addSql(<<<SQL
ALTER TABLE pim_datagrid_view
DROP index $uniqueConstraintKeyName
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function dbalConnection(): DbalConnection
    {
        return $this->container->get('database_connection');
    }
}
