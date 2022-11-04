<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
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

    public function test_it_adds_the_entity_updated_property_to_the_mapping(): void
    {
        $this->recreateIndexWithoutIdFieldInTheMapping();
        $properties = $this->getIndexMappingProperties();
        self::assertArrayNotHasKey('id', $properties);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $properties = $this->getIndexMappingProperties();
        self::assertArrayHasKey('id', $properties);
        self::assertSame(['type' => 'keyword'], $properties['id']);
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
        ));

        return $mapping['mappings']['properties'] ?? [];
    }
}
