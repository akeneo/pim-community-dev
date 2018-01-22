<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexer responsible for the indexing of product proposals entities.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productProposalClient;

    /** @var string */
    private $indexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $productProposalClient
     * @param string              $indexType
     */
    public function __construct(
        NormalizerInterface $normalizer,
        Client $productProposalClient,
        string $indexType
    ) {
        $this->normalizer = $normalizer;
        $this->productProposalClient = $productProposalClient;
        $this->indexType = $indexType;
    }

    /**
     * Indexes a product proposal in both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        $normalizedObject = $this->normalizer->normalize($object, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->productProposalClient->index($this->indexType, $normalizedObject['identifier'], $normalizedObject);
    }

    /**
     * Indexes a product proposal in both the product index and the product and product model index.
     *
     * If the index_refresh is provided, it uses the refresh strategy defined.
     * Otherwise the waitFor strategy is by default.
     *
     * {@inheritdoc}
     */
    public function indexAll(array $objects, array $options = []) : void
    {
        if (empty($objects)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::waitFor();

        $normalizedProducts = [];
        $normalizedProductModels = [];
        foreach ($objects as $object) {
            $normalizedProduct = $this->normalizer->normalize(
                $object,
                ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX
            );
            $this->validateObjectNormalization($normalizedProduct);
            $normalizedProducts[] = $normalizedProduct;

            $normalizedProductModel = $this->normalizer->normalize(
                $object,
                ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX
            );
            $this->validateObjectNormalization($normalizedProductModel);
            $normalizedProductModels[] = $normalizedProductModel;
        }

        $this->productProposalClient->bulkIndexes($this->indexType, $normalizedProducts, 'id', $indexRefresh);
    }

    /**
     * Removes the product proposal from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = []) : void
    {
        $this->productProposalClient->delete($this->indexType, $objectId);
    }

    /**
     * Removes the product proposals from both the product proposal index and the product proposal.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $this->productProposalClient->bulkDelete($this->indexType, $objects);
    }

    /**
     * Checks the normalized object has the minimum property needed for the indexation to work.
     *
     * @param array $normalization
     */
    protected function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException('Only product proposals with an "id" property can be indexed in the search engine.');
        }
    }
}
