<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/**
 * This class will be removed after 4.0 version
 */
final class Version_4_0_20191223132437_data_quality_insights_add_dynamic_template_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    public function test_it_adds_dynamic_mapping()
    {
        $builder = $this->get('akeneo_elasticsearch.client_builder');
        $hosts = [$this->getParameter('index_hosts')];

        $client = $builder->setHosts($hosts)->build()->indices();

        $existingMapping = $client->getMapping(['index' => $this->getParameter('product_and_product_model_index_name')]);
        $this->executeMigration('_4_0_20191223132437_data_quality_insights_add_dynamic_template');
        $migratedMapping = $client->getMapping(['index' => $this->getParameter('product_and_product_model_index_name')]);
        
        Assert::assertCount(count($this->getDynamicTemplates($existingMapping)) + 2, $this->getDynamicTemplates($migratedMapping));
    }

    private function getDynamicTemplates(array $mapping)
    {
        return current($mapping)['mappings']['dynamic_templates'];
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
