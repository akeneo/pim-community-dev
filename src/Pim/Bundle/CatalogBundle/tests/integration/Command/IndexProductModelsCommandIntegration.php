<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Command;

use Akeneo\Bundle\ElasticsearchBundle\tests\integration\AbstractIndexCommandIntegration;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;

/**
 * Checks that the index product models command indexes all product models and their descendants in the right indexes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelsCommandIntegration extends AbstractIndexCommandIntegration
{
    public function testIndexesAllProductModelsAndTheirDescendants(): void
    {
        $this->assertIndexesEmpty();
        $exitCode = $this->runIndexProductModelsCommand();
        $this->assertExitCodeSuccess($exitCode);
        $this->assertProductModelIndexNotEmpty();
        $this->assertProductAndProductModelIndexNotEmpty();
    }

    public function testIndexesProductModelsAndTheirDescendantsWithIdentifiers(): void
    {
        $this->assertIndexesEmpty();
        $this->runIndexProductModelsCommand(['model-braided-hat', 'model-tshirt-divided']);
        $this->assertProductModelIndexNotEmpty();
        $this->assertProductAndProductModelIndexNotEmpty();
        $this->assertProductModelIndexCount(5);
        $this->assertProductAndProductModelIndexCount(19);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->runResetIndexesCommand();
    }

    /**
     * Runs the index product command.
     */
    private function runIndexProductModelsCommand(array $productModelIdentifiers = []): int
    {
        $options = $this->getIndexCommandOptions($productModelIdentifiers);
        $commandLauncher = new CommandLauncher($this->testKernel);

        return $commandLauncher->execute('pim:product-model:index', null, $options);
    }

    /**
     * @param array $productModelCodes
     *
     * @return array
     */
    private function getIndexCommandOptions(array $productModelCodes): array
    {
        $options = ['arguments' => ['--all' => true]];
        if (!empty($productModelCodes)) {
            $options = ['arguments' => ['codes' => $productModelCodes]];
        }

        return $options;
    }

    /**
     * asserts that the index of product models is not empty
     */
    private function assertProductModelIndexNotEmpty()
    {
        $allDocuments = $this->get('akeneo_elasticsearch.client.product_model')->search(
            'pim_catalog_product',
            [
                'query' => [
                    'match_all' => new \StdClass(),
                ],
            ]
        );
        $this->assertNotCount(0, $allDocuments['hits']['hits']);
    }

    /**
     * Checks whether the given indexes name are not empty, if none given will check that all registered indexes in the
     * PIM are not empty.
     *
     * @param int   $count
     * @param array $indexesName
     */
    protected function assertIndexesCount(int $count, array $indexesName = []): void
    {
        $esClients = $this->get('akeneo_elasticsearch.registry.clients')->getClients();

        foreach ($esClients as $esClient) {
            if (!in_array($esClient->getIndexName(), $indexesName) && !empty($indexesName)) {
                continue;
            }

            $allDocuments = $esClient->search('pim_catalog_product', [
                '_source' => 'identifier',
                'query'   => [
                    'match_all' => new \StdClass(),
                ],
            ]);
            $this->assertEquals($count, $allDocuments['hits']['total']);
        }
    }

    /**
     * @param int $count
     */
    protected function assertProductModelIndexCount(int $count): void
    {
        $allDocuments = $this->get('akeneo_elasticsearch.client.product_model')->search(
            'pim_catalog_product',
            [
                '_source' => 'identifier',
                'query'   => [
                    'match_all' => new \StdClass(),
                ],
            ]
        );
        $this->assertEquals($count, $allDocuments['hits']['total']);
    }
}
