<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * For each integration tests implemented in the subclass, this abstract:
 * - Resets the index (eg, removes the index configuration from ES and the documents indexed)
 * - It also provides function to make ES queries to that index and make sure the expected index are part of the result.
 */
abstract class AbstractProductProposalTestCase extends TestCase
{
    const DOCUMENT_TYPE = 'pimee_workflow_product_proposal';

    private const PAGE_SIZE = 100;

    /** @var Client */
    protected $esProposalProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProposalProductClient = $this->get('akeneo_elasticsearch.client.product_proposal');
        $this->esProposalProductClient->resetIndex();

        $this->addDocuments();
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    abstract protected function addDocuments();

    /**
     * Indexes the given list of products
     *
     * @param array $productProposals
     */
    protected function indexDocuments(array $productProposals)
    {
        foreach ($productProposals as $productProposal) {
            $this->esProposalProductClient->index(self::DOCUMENT_TYPE, $productProposal['identifier'], $productProposal);
        }

        $this->esProposalProductClient->refreshIndex();
    }

    /**
     * Executes the given query and returns the list of skus found.
     *
     * @param array $query
     *
     * @return array
     */
    protected function getSearchQueryResults(array $query)
    {
        $identifiers = [];

        $query['size'] = self::PAGE_SIZE;
        $response = $this->esProposalProductClient->search(self::DOCUMENT_TYPE, $query);

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        return $identifiers;
    }

    /**
     * Checks that the proposal products found are effectively expected
     *
     * @param array $actualProductIdentifiers
     * @param array $expectedProposalProductIdentifiers
     */
    protected function assertDocument(array $actualProductIdentifiers, array $expectedProposalProductIdentifiers)
    {
        sort($actualProductIdentifiers);
        sort($expectedProposalProductIdentifiers);

        $this->assertSame($expectedProposalProductIdentifiers, $actualProductIdentifiers);
    }

    /**
     * Build elasticsearch query
     *
     * @param array $filters
     * @param array $mustNot
     *
     * @return array
     */
    protected function buildQuery(array $filters = [], array $mustNot = []): array
    {
        $query = ['query' => ['bool' => ['filter' => ['bool' => ['should' => []]], 'must_not' => []]]];

        if (!empty($filters)) {
            $query['query']['bool']['filter']['bool']['should'] = $filters;
            $query['query']['bool']['filter']['bool']['minimum_should_match'] = 1;
        }

        if (!empty($mustNot)) {
            $query['query']['bool']['filter']['bool']['must_not'] = $mustNot;
        }

        return $query;
    }
}
