<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class Version_7_0_20220920000000_deduplicate_elasticsearch_indices_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_NAME = '_7_0_20220920000000_deduplicate_elasticsearch_indices';

    private ?Client $client;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItRemovesDuplicatedIndices(): void
    {
        $this->removeEventsApiDebugIndices();
        $this->createEventsApiDebugIndice();
        $this->createEventsApiDebugIndice();

        $this->assertCount(2, $this->getEventsApiDebugIndices());

        $this->reExecuteMigration(self::MIGRATION_NAME);

        $this->assertCount(1, $this->getEventsApiDebugIndices());
    }

    private function removeEventsApiDebugIndices(): void
    {
        $esClient = $this->getEsClient();

        $alias = self::getContainer()->getParameter('events_api_debug_index_name');
        $indices = $esClient->indices()->getAlias(['name' => $alias]);

        foreach (\array_keys($indices) as $indice) {
            $esClient->indices()->delete(['index' => $indice]);
        }
    }

    private function createEventsApiDebugIndice(): void
    {
        $esClient = $this->getEsClient();

        $alias = self::getContainer()->getParameter('events_api_debug_index_name');
        $configurationLoader = new Loader(
            self::getContainer()->getParameter('events_api_debug_elasticsearch_index_configuration_file'),
            new ParameterBag()
        );

        $configuration = $configurationLoader->load();
        $body = $configuration->buildAggregated();
        $body['aliases'] = [$alias => (object) []];

        $params = [
            'index' => \strtolower($alias.'_'.Uuid::uuid4()->toString()),
            'body' => $body,
        ];

        $esClient->indices()->create($params);
    }

    private function getEventsApiDebugIndices(): array
    {
        $esClient = $this->getEsClient();

        $alias = self::getContainer()->getParameter('events_api_debug_index_name');

        return $esClient->indices()->getAlias(['name' => $alias]);
    }

    private function getEsClient(): Client
    {
        return $this->client ??= $this->buildEsClient();
    }

    private function buildEsClient(): Client
    {
        $builder = self::getContainer()->get('akeneo_elasticsearch.client_builder');
        $builder->setHosts([self::getContainer()->getParameter('index_hosts')]);

        return $builder->build();
    }
}
