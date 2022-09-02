<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

final class Version_7_0_20220104160000_add_table_reference_entity_value_mapping_Integration extends TestCase
{
    private const MIGRATION_LABEL = '_7_0_20220104160000_add_table_reference_entity_value_mapping';

    use ExecuteMigrationTrait;

    private NativeClient $nativeClient;
    private Client $productAndProductModelClient;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_does_nothing_when_mapping_is_already_set(): void
    {
        $this->productAndProductModelClient->resetIndex();
        $this->productAndProductModelClient->refreshIndex();
        self::assertTrue($this->mappingExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        self::assertTrue($this->mappingExists());
    }

    /** @test */
    public function it_adds_table_mapping(): void
    {
        $this->recreateProductIndexWithoutMapping();
        $this->productAndProductModelClient->refreshIndex();
        self::assertFalse($this->mappingExists(), 'Mapping exists, test is not relevant');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->productAndProductModelClient->refreshIndex();
        self::assertTrue($this->mappingExists());
    }

    private function mappingExists(): bool
    {
        $existingMapping = $this->nativeClient->indices()->getMapping([
            'index' => $this->getParameter('product_and_product_model_index_name'),
        ]);
        $existingDynamicTemplate = current($existingMapping)['mappings']['dynamic_templates'];
        $fieldMappings = array_merge(...$existingDynamicTemplate);

        return isset($fieldMappings['table_values_reference_entity']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $builder = $this->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->getParameter('index_hosts')];
        $this->nativeClient = $builder->setHosts($hosts)->build();

        $this->productAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    private function recreateProductIndexWithoutMapping(): void
    {
        $configFiles = $this->getParameter('elasticsearch_index_configuration_files');
        $config = [];
        foreach ($configFiles as $configFile) {
            $parsedConfigFile = Yaml::parseFile($configFile);
            foreach ($parsedConfigFile['mappings']['dynamic_templates'] ?? [] as $index => $template) {
                if (\array_key_exists('table_values_reference_entity', $template)) {
                    unset($parsedConfigFile['mappings']['dynamic_templates'][$index]);
                }
            }

            $config = \array_merge($config, $parsedConfigFile);
        }

        $newConfigFile = \tempnam(\sys_get_temp_dir(), 'migration_test_table_attribute');
        \file_put_contents($newConfigFile, Yaml::dump($config));

        $loader = new Loader([$newConfigFile], $this->get(ParameterBagInterface::class));
        $client = new Client(
            $this->get('akeneo_elasticsearch.client_builder'),
            $loader,
            [$this->getParameter('index_hosts')],
            $this->productAndProductModelClient->getIndexName()
        );
        $client->resetIndex();
    }
}
