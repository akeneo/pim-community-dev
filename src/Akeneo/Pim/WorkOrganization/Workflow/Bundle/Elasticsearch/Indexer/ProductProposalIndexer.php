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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductProposalNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexer responsible for the indexing of product proposals entities.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
{
    public const PRODUCT_IDENTIFIER_PREFIX = 'product_draft_';

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
     * Indexes a product proposal in from the product proposal index.
     *
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        $normalizedObject = $this->normalizer->normalize($object, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->productProposalClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);
    }

    /**
     * Indexes a product proposal in both from the product proposal index.
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
        foreach ($objects as $object) {
            $normalizedProduct = $this->normalizer->normalize(
                $object,
                ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX
            );
            $this->validateObjectNormalization($normalizedProduct);
            $normalizedProducts[] = $normalizedProduct;
        }

        $this->productProposalClient->bulkIndexes($this->indexType, $normalizedProducts, 'id', $indexRefresh);
    }

    /**
     * Removes the product proposal from the product proposal index.
     *
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = []) : void
    {
        $documents = $this->productProposalClient->search(
            $this->indexType,
            ['query' => ['term' => ['id' => self::PRODUCT_IDENTIFIER_PREFIX . (string) $objectId]]]
        );
        if (0 !== $documents['hits']['total']) {
            $this->productProposalClient->delete(
                $this->indexType,
                self::PRODUCT_IDENTIFIER_PREFIX . (string) $objectId
            );
        }
    }

    /**
     * Removes the product proposals from the product proposal index.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[]  = self::PRODUCT_IDENTIFIER_PREFIX . (string) $objectId;
        }
        $this->productProposalClient->bulkDelete($this->indexType, $objectIds);
    }

    /**
     * Checks the normalized object has the minimum property needed for the indexation to work.
     *
     * @param array $normalization
     *
     * @throws \InvalidArgumentException
     */
    protected function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException('Only product proposals with an "id" property can be indexed in the search engine.');
        }
    }
}
