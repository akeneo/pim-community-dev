<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * This migration adds a mapped field (id) to connection_error index mapping
 */
final class Version_7_0_20221027152057_add_id_field_to_connection_error_index_mapping extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
        $indexHosts = $this->container->getParameter('index_hosts');
        $connectionErrorIndexName = $this->container->getParameter('connection_error_index_name');

        $builder = new ClientBuilder();

        $client = $builder->setHosts([$indexHosts])->build()->indices();

        $existingMapping = $client->getMapping(['index' => $connectionErrorIndexName])->asArray();
        if (!\is_array($existingMapping) || !isset(current($existingMapping)['mappings']['properties'])) {
            throw new \RuntimeException('Unable to retrieve existing mapping.');
        }

        if ('text' === (current($existingMapping)['mappings']['properties']['id']['type'] ?? '')) {
            $this->reindexWhenThereIsAlreadyAnId();
            return;
        }

        $client->putMapping([
            'index' => $connectionErrorIndexName,
            'body' => [
                'properties' => [
                    'id' => ['type' => 'keyword'],
                ],
            ],
        ]);
    }

    private function reindexWhenThereIsAlreadyAnId(): void
    {
        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $builder->setHosts([$this->container->getParameter('index_hosts')]);
        /** @var \Elastic\Elasticsearch\Client $client */
        $client = $builder->build();
        $alias = $this->container->getParameter('connection_error_index_name');
        $copy = sprintf('%s_copy', $alias);
        $indice = array_keys($client->indices()->getAlias(['name' => $alias])->asArray())[0];

        $mapping = $this->getMappingConfiguration();

        $client->indices()->create([
            'index' => $copy,
            'body' => $mapping,
        ]);

        $client->reindex([
            'refresh' => true,
            'body' => [
                'source' => [
                    'index' => $indice,
                ],
                'dest' => [
                    'index' => $copy,
                ],
            ],
        ]);

        $client->indices()->putAlias([
            'name' => $alias,
            'index' => $copy,
        ]);

        $client->indices()->delete([
            'index' => $indice,
        ]);

        $client->indices()->create([
            'index' => $indice,
            'body' => $mapping,
        ]);

        $client->reindex([
            'refresh' => true,
            'body' => [
                'source' => [
                    'index' => $copy,
                ],
                'dest' => [
                    'index' => $indice,
                ],
            ],
        ]);

        $client->indices()->putAlias([
            'name' => $alias,
            'index' => $indice,
        ]);

        $client->indices()->delete([
            'index' => $copy,
        ]);
    }

    private function getMappingConfiguration(): array
    {
        $ceDir = $this->container->getParameter('pim_ce_dev_src_folder_location');
        $path = "{$ceDir}/src/Akeneo/Connectivity/Connection/back/Infrastructure/Symfony/Resources/elasticsearch/connection_error_mapping.yml";

        return Yaml::parseFile($path);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        Assert::notNull($container);
        $this->container = $container;
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

}
