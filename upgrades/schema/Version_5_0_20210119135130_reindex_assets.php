<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\VersionProvider;
use Akeneo\Platform\VersionProviderInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reindex all assets using new index.
 * This migration can take a long time. We disable it for Serenity environment, another process is applied
 * to avoid the service interruption.
 */
final class Version_5_0_20210119135130_reindex_assets extends AbstractMigration implements ContainerAwareInterface
{
    private const SERENITY_EDITION = 'Serenity';

    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        if ($this->currentEditionIsSerenity()) {
            $this->disableMigrationWarning();

            return;
        }

        $this->write('Start to reindex all assets. This operation can take a long time...');

        $this->container
            ->get('akeneo_assetmanager.infrastructure.elasticsearch.update_index_mapping')
            ->updateIndexMapping();

        $this->write('Done');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function currentEditionIsSerenity(): bool
    {
        return strtolower(static::SERENITY_EDITION) === strtolower(
            $this->container->get('pim_catalog.version_provider')->getEdition()
        );
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
