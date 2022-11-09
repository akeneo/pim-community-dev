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
class Version_7_0_20221027152057_add_id_field_to_connection_error_index_mapping_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_7_0_20221027152057_add_id_field_to_connection_error_index_mapping';

    private NativeClient $nativeClient;
    private Client $connectionErrorClient;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->nativeClient = $this->get('akeneo_elasticsearch.client_builder')->build();
        $this->connectionErrorClient = $this->get('akeneo_connectivity.client.connection_error');
    }

    public function test_it_adds_the_id_property_to_the_mapping(): void
    {
        $this->recreateConnectionErrorIndexWithoutIdInTheMapping();
        $properties = $this->getMappingProperties();
        self::assertArrayNotHasKey('id', $properties);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $properties = $this->getMappingProperties();
        self::assertArrayHasKey('id', $properties);
        self::assertSame(['type' => 'keyword'], $properties['id']);
    }

    public function test_it_changes_the_id_property_from_text_to_keyword(): void
    {
        $this->recreateConnectionErrorIndexWithoutIdInTheMapping();
        self::assertArrayNotHasKey('id', $this->getMappingProperties());

        $this->nativeClient->index([
            'index' => self::getContainer()->getParameter('connection_error_index_name'),
            'body' => [
                'id' => 'aa63292c-a06c-4c50-afb9-c98c97dc8a13',
                'connection_code' => 'foo',
                'content' => [],
                'error_datetime' => '2021-01-03T02:30:00+01:00',
            ],
        ]);
        $this->nativeClient->indices()->refresh();
        self::assertSame('text', $this->getMappingProperties()['id']['type']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $properties = $this->getMappingProperties();
        self::assertSame('keyword', $properties['id']['type']);

        $documents = $this->nativeClient->search([
            'index' => self::getContainer()->getParameter('connection_error_index_name'),
        ]);
        self::assertSame([
            'id' => 'aa63292c-a06c-4c50-afb9-c98c97dc8a13',
            'connection_code' => 'foo',
            'content' => [],
            'error_datetime' => '2021-01-03T02:30:00+01:00',
        ], $documents['hits']['hits'][0][('_source')]);
    }

    private function recreateConnectionErrorIndexWithoutIdInTheMapping(): void
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

        $newConfigFile = tempnam(sys_get_temp_dir(), 'migration_connection_error_id');
        file_put_contents($newConfigFile, Yaml::dump($config));

        $loader = new Loader([$newConfigFile], $this->get(ParameterBagInterface::class));
        $client = new Client(
            $this->get('akeneo_elasticsearch.client_builder'),
            $loader,
            [$this->getParameter('index_hosts')],
            $this->connectionErrorClient->getIndexName()
        );
        $client->resetIndex();
    }

    private function getMappingProperties(): array
    {
        $mapping = current($this->nativeClient->indices()->getMapping(
            ['index' => $this->connectionErrorClient->getIndexName()]
        ));

        return $mapping['mappings']['properties'] ?? [];
    }
}
