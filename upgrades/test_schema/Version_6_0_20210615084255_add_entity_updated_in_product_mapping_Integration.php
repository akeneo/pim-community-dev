<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

final class Version_6_0_20210615084255_add_entity_updated_in_product_mapping_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    const MIGRATION_LABEL = '_6_0_20210615084255_add_entity_updated_in_product_mapping';

    private NativeClient $nativeClient;
    private Client $productAndProductModelClient;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->nativeClient = $this->get('akeneo_elasticsearch.client_builder')->build();
        $this->productAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    /** @test */
    public function it_adds_the_entity_updated_property_to_the_mapping(): void
    {

        $this->recreateProductIndexWithoutEntityUpdatedMapping();
        $properties = $this->getProductMappingProperties();
        self::assertArrayNotHasKey('entity_updated', $properties);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $properties = $this->getProductMappingProperties();
        self::assertArrayHasKey('entity_updated', $properties);
        self::assertSame(['type' => 'date'], $properties['entity_updated']);
    }

    private function recreateProductIndexWithoutEntityUpdatedMapping(): void
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
        self::assertArrayHasKey('entity_updated', $config['mappings']['properties'], 'Test cannot be relevant: "entity_updated" is missing');
        unset($config['mappings']['properties']['entity_updated']);

        $newConfigFile = tempnam(sys_get_temp_dir(), 'migration_entity_updated');
        file_put_contents($newConfigFile, Yaml::dump($config));

        $loader = new Loader([$newConfigFile], $this->get(ParameterBagInterface::class));
        $client = new Client(
            $this->get('akeneo_elasticsearch.client_builder'),
            $loader,
            [$this->getParameter('index_hosts')],
            $this->productAndProductModelClient->getIndexName()
        );
        $client->resetIndex();
    }

    private function getProductMappingProperties(): array
    {
        $mapping = current($this->nativeClient->indices()->getMapping(
            ['index' => $this->productAndProductModelClient->getIndexName()]
        ));

        return $mapping['mappings']['properties'] ?? [];
    }
}
