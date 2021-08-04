<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\VersionProvider;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexAllRecordsOnTemporaryIndexCommand;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWrapper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
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
        if ($this->isSassVersion()) {
            $this->migrateSassVersion();
            return;
        }

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

    private function migrateSassVersion(): void
    {
        $this->container->get('akeneo_referenceentity.client.record_temporary')->resetIndex();
        $this->addSql(
            'INSERT INTO pim_configuration (`code`, `values`) VALUES (:code, :values);',
            [
                'code' => IndexAllRecordsOnTemporaryIndexCommand::CONFIGURATION_CODE,
                'values' => ['status' => 'todo', 'temporary_index_alias' => 'akeneo_referenceentity_record_v2'],
            ],
            ['values' => Types::JSON]
        );
    }
}
