<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20220920000000_deduplicate_elasticsearch_indices extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;
    private ?Client $client;

    public function up(Schema $schema): void
    {
        $aliases = [
            $this->container->getParameter('events_api_debug_index_name'),
            $this->container->getParameter('connection_error_index_name'),
        ];

        foreach ($aliases as $alias) {
            $this->removeDuplicatedIndices($alias);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function removeDuplicatedIndices(string $alias): void
    {
        $esClient = $this->getEsClient();
        $indices = $esClient->indices()->getAlias(['name' => $alias]);

        if (\count($indices) <= 1) {
            return;
        }

        /** @var array<string> $duplicates */
        $duplicates = \array_slice(\array_keys($indices), 1);

        foreach ($duplicates as $duplicate) {
            $esClient->indices()->delete(['index' => $duplicate]);
        }
    }

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    private function getEsClient(): Client
    {
        return $this->client ??= $this->buildEsClient();
    }

    private function buildEsClient(): Client
    {
        $builder = $this->container->get('akeneo_elasticsearch.client_builder');
        $builder->setHosts([$this->container->getParameter('index_hosts')]);

        return $builder->build();
    }
}
