<?php

declare(strict_types=1);

namespace Akeneo\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;

/**
 * Checks that the reset index command resets all indexes registered in the PIM.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetIndexesCommandIntegration extends AbstractIndexCommandIntegration
{
    public function testCommandResetsAllIndexes()
    {
        $this->assertAllRegisteredIndexesNotEmpty();
        $exitCode = $this->runResetIndexesCommand();
        $this->assertExitCodeSuccess($exitCode);
        $this->assertIndexesEmpty();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
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
}
