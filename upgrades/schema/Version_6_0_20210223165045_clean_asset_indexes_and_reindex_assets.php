<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * Remove temporary asset alias/index created for PIM-9584. Reindex assets if mapping is not up to date.
 */
final class Version_6_0_20210223165045_clean_asset_indexes_and_reindex_assets extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
        Assert::notNull($this->container);

        $indexHosts = $this->container->getParameter('index_hosts');
        $clientBuilder = $this->container->get('akeneo_elasticsearch.client_builder')->setHosts([$indexHosts]);
        $nativeClient = $clientBuilder->build();

        $this->removeTemporaryIndex($nativeClient);

        if ($this->mappingIsAlreadyOk($nativeClient)) {
            $this->write('Mapping already ok. No need to reindex.');

            return;
        }

        // Reindex all assets
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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    private function removeTemporaryIndex(Client $nativeClient): void
    {
        $temporaryIndexName = sprintf('%s_temporary', $this->container->getParameter('asset_index_name'));
        $indices = $nativeClient->indices();

        if ($indices->existsAlias(['name' => $temporaryIndexName])) {
            $aliases = $indices->getAlias(['name' => $temporaryIndexName]);
            $temporaryIndexName = array_keys($aliases)[0];
        }

        if ($indices->exists(['index' => $temporaryIndexName])) {
            $result = $indices->delete(['index' => $temporaryIndexName]);
            Assert::true($result['acknowledged']);
        }
    }

    private function mappingIsAlreadyOk(Client $nativeClient): bool
    {
        $result = $nativeClient->indices()->getFieldMapping([
            'index' => $this->container->get('akeneo_assetmanager.client.asset')->getIndexName(),
            'fields' => ['code', 'asset_family_code'],
        ]);

        $currentIndexMappings = current($result)['mappings'];
        $codeNormalizer = $currentIndexMappings['code']['mapping']['code']['normalizer'] ?? null;
        $assetFamilyCodeNormalizer = $currentIndexMappings['asset_family_code']['mapping']['asset_family_code']['normalizer'] ?? null;

        return 'text_normalizer' === $codeNormalizer && 'text_normalizer' === $assetFamilyCodeNormalizer;
    }
}
