<?php

declare(strict_types=1);

namespace Akeneo\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;

/**
 * Checks that the reset index command resets all indexes registered in the PIM.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetIndexesCommandIntegration extends TestCase
{
    public function testCommandResetsAllIndexes()
    {
        $this->assertIndexesNotEmpty();
        $this->runResetIndexesCommand();
        $this->assertIndexesEmpty();
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function assertIndexesNotEmpty(): void
    {
        $esClients = $this->get('akeneo_elasticsearch.registry.clients')->getClients();

        foreach ($esClients as $esClient) {
            $allDocuments = $esClient->search('pim_catalog_product', [
                '_source' => 'identifier',
                'query' => [
                    'match_all' => new \StdClass(),
                ],
            ]);
            $this->assertGreaterThan(0, count($allDocuments['hits']['hits']));
        }
    }

    private function runResetIndexesCommand(): void
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $exitCode = $commandLauncher->execute('akeneo:elasticsearch:reset-indexes', null, ['inputs' => ['yes']]);
        $this->assertSame(0, $exitCode);
    }

    private function assertIndexesEmpty(): void
    {
        $esClients = $this->get('akeneo_elasticsearch.registry.clients')->getClients();

        foreach ($esClients as $esClient) {
            $allDocuments = $esClient->search('pim_catalog_product', [
                'query' => [
                    'match_all' => new \StdClass(),
                ],
            ]);
            $this->assertEquals(0, count($allDocuments['hits']['hits']));
        }
    }
}
