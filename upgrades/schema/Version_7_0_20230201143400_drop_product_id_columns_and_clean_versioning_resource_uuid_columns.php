<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20230201143400_drop_product_id_columns_and_clean_versioning_resource_uuid_columns extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function up(Schema $schema): void
    {
        $this->getMigration()->migrateNotZdd();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getMigration(): V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns
    {
        return $this->container->get('Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns');
    }
}
