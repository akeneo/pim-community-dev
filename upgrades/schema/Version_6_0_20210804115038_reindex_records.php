<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\VersionProvider;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWrapper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

final class Version_6_0_20210804115038_reindex_records extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $this->skipIf($this->isSassVersion(), 'This migration is only for non Sass version');

        $this->disableMigrationWarning();

        /** @var UpdateIndexMappingWrapper $indexMappingUpdater*/
        $indexMappingUpdater = $this->container->get('akeneo_referenceentity.infrastructure.elasticsearch.update_index_mapping');
        $indexMappingUpdater->updateIndexMapping();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    private function isSassVersion(): bool
    {
        /** @var VersionProvider $versionProvider */
        $versionProvider = $this->container->get('pim_catalog.version_provider');

        return $versionProvider->isSaaSVersion();
    }
}
