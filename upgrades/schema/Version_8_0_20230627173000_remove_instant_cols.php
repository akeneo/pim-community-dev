<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Migration\V20230622175500OptimizeTableWithInstantColsMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_8_0_20230627173000_remove_instant_cols extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();
        if ($this->isSaaSVersion()){
            return;
        }

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

    private function getMigration(): V20230622175500OptimizeTableWithInstantColsMigration
    {
        return $this->container->get('Akeneo\Tool\Component\StorageUtils\Migration\V20230622175500OptimizeTableWithInstantColsMigration');
    }

    private function isSaaSVersion(): bool
    {
        /** @var VersionProviderInterface $versionProvider */
        $versionProvider = $this->container->get('pim_catalog.version_provider');

        return $versionProvider->isSaaSVersion();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
