<?php

declare(strict_types=1);

namespace Akeneo\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractIndexCommandIntegration extends TestCase
{
    /**
     * Resets all ES indexes registered in the PIM.
     */
    protected function runResetIndexesCommand(): int
    {
        $commandLauncher = new CommandLauncher($this->testKernel);
        $exitCode = $commandLauncher->execute('akeneo:elasticsearch:reset-indexes', null, ['inputs' => ['yes']]);

        return $exitCode;
    }

    /**
     * @param int $exitCode
     */
    protected function assertExitCodeSuccess(int $exitCode)
    {
        $this->assertSame(0, $exitCode);
    }

    /**
     * Checks that all registered indexes in the PIM are not empty.
     */
    protected function assertAllRegisteredIndexesNotEmpty(): void
    {
        $esClients = $this->get('akeneo_elasticsearch.registry.clients')->getClients();

        foreach ($esClients as $esClient) {
            $allDocuments = $esClient->search('pim_catalog_product', [
                '_source' => 'identifier',
                'query'   => [
                    'match_all' => new \StdClass(),
                ],
            ]);
            $this->assertGreaterThan(0, count($allDocuments['hits']['hits']));
        }
    }

    /**
     * Checks whether all registered indexes in the PIM are empty.
     */
    protected function assertIndexesEmpty(): void
    {
        $esClients = $this->get('akeneo_elasticsearch.registry.clients')->getClients();

        foreach ($esClients as $esClient) {
            $allDocuments = $esClient->search(
                'pim_catalog_product',
                [
                    'query' => [
                        'match_all' => new \StdClass(),
                    ],
                ]
            );
            $this->assertCount(0, $allDocuments['hits']['hits']);
        }
    }

    /**
     * asserts that the index of products and product models is not empty
     */
    protected function assertProductAndProductModelIndexNotEmpty()
    {
        $allDocuments = $this->get('akeneo_elasticsearch.client.product_and_product_model')->search(
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
     * @param int   $count
     */
    protected function assertProductAndProductModelIndexCount(int $count): void
    {
        $allDocuments = $this->get('akeneo_elasticsearch.client.product_and_product_model')->search(
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
