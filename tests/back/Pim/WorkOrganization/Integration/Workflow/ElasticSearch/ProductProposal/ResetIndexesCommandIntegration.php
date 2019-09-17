<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal;

use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration\AbstractProductProposalTestCase;

/**
 * Checks that the reset index command reset proposal index.
 */
class ResetIndexesCommandIntegration extends AbstractProductProposalTestCase
{
    public function testCommandResetsAllIndexes()
    {
        $this->assertIndexesNotEmpty();
        $this->runResetIndexesCommand();
        $this->assertIndexesEmpty();
    }

    private function assertIndexesNotEmpty(): void
    {
        $esClient = $this->get('akeneo_elasticsearch.client.product_proposal');

        $allDocuments = $esClient->search([
            '_source' => 'identifier',
            'query' => [
                'match_all' => new \StdClass(),
            ],
        ]);

        $this->assertGreaterThan(0, count($allDocuments['hits']['hits']));
    }

    private function runResetIndexesCommand(): void
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $exitCode = $commandLauncher->execute('akeneo:elasticsearch:reset-indexes', null, ['inputs' => ['yes']]);
        $this->assertSame(0, $exitCode);
    }

    private function assertIndexesEmpty(): void
    {
        $esClient = $this->get('akeneo_elasticsearch.client.product_proposal');

        $allDocuments = $esClient->search([
            'query' => [
                'match_all' => new \StdClass(),
            ],
        ]);
        $this->assertEquals(0, count($allDocuments['hits']['hits']));
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            ['identifier' => 'product_1'],
        ];

        $this->indexDocuments($products);
    }
}
