<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elastic\Elasticsearch\Client as NativeClient;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_7_0_20221026154157_add_id_to_events_api_debug_index_mapping_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_7_0_20221026154157_add_id_to_events_api_debug_index_mapping';

    private NativeClient $nativeClient;
    private Client $eventsApiDebugClient;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->nativeClient = $this->get('akeneo_elasticsearch.client_builder')->build();
        $this->eventsApiDebugClient = $this->get('akeneo_connectivity.client.events_api_debug');
    }

    public function test_it_adds_the_id_property_to_the_mapping(): void
    {
        $this->recreateIndexWithoutIdFieldInTheMapping();
        $properties = $this->getIndexMappingProperties();
        self::assertArrayNotHasKey('id', $properties);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $properties = $this->getIndexMappingProperties();
        self::assertArrayHasKey('id', $properties);
        self::assertSame(['type' => 'keyword'], $properties['id']);
    }

    public function test_it_changes_the_id_property_from_text_to_keyword(): void
    {
        $this->recreateIndexWithoutIdFieldInTheMapping();
        self::assertArrayNotHasKey('id', $this->getIndexMappingProperties());

        $this->nativeClient->index([
            'index' => self::getContainer()->getParameter('events_api_debug_index_name'),
            'body' => [
                'id' => 'aa63292c-a06c-4c50-afb9-c98c97dc8a13',
                'timestamp' => '1667946703',
                'level' => 'notice',
                'message' => 'foobar',
                'connection_code' => 'foo',
                'context' => [],
            ],
        ]);
        $this->nativeClient->indices()->refresh();
        self::assertSame('text', $this->getIndexMappingProperties()['id']['type']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $properties = $this->getIndexMappingProperties();
        self::assertSame('keyword', $properties['id']['type']);

        $documents = $this->nativeClient->search([
            'index' => self::getContainer()->getParameter('events_api_debug_index_name'),
        ])->asArray();
        self::assertSame([
            'id' => 'aa63292c-a06c-4c50-afb9-c98c97dc8a13',
            'timestamp' => '1667946703',
            'level' => 'notice',
            'message' => 'foobar',
            'connection_code' => 'foo',
            'context' => [],
        ], $documents['hits']['hits'][0][('_source')]);
    }

    private function recreateIndexWithoutIdFieldInTheMapping(): void
    {
        $configFiles = $this->getParameter('elasticsearch_index_configuration_files');
        $config = [];
        foreach ($configFiles as $configFile) {
            $config = array_merge_recursive($config, Yaml::parseFile($configFile));
        }

        self::assertArrayHasKey('mappings', $config);
        self::assertIsArray($config['mappings']);
        self::assertArrayHasKey('properties', $config['mappings']);
        self::assertIsArray($config['mappings']['properties']);
        self::assertArrayHasKey('id', $config['mappings']['properties'], 'Test cannot be relevant: "id" is missing');
        unset($config['mappings']['properties']['id']);

        $newConfigFile = tempnam(sys_get_temp_dir(), 'migration_events_api_debug_id');
        file_put_contents($newConfigFile, Yaml::dump($config));

        $loader = new Loader([$newConfigFile], $this->get(ParameterBagInterface::class));
        $client = new Client(
            $this->get('akeneo_elasticsearch.client_builder'),
            $loader,
            [$this->getParameter('index_hosts')],
            $this->eventsApiDebugClient->getIndexName()
        );
        $client->resetIndex();
    }

    private function getIndexMappingProperties(): array
    {
        $mapping = current($this->nativeClient->indices()->getMapping(
            ['index' => $this->eventsApiDebugClient->getIndexName()]
        )->asArray());

        return $mapping['mappings']['properties'] ?? [];
    }
}
