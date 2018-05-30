<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductModelProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelProposalIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
{
    private const PRODUCT_MODEL_IDENTIFIER_PREFIX = 'product_model_draft_';

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productModelProposalClient;

    /** @var string */
    private $indexType;

    public function __construct(
        NormalizerInterface $normalizer,
        Client $productModelProposalClient,
        string $indexType
    ) {
        $this->normalizer = $normalizer;
        $this->productModelProposalClient = $productModelProposalClient;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function index($object, array $options = [])
    {
        $normalizedObject = $this->normalizer->normalize($object, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->productModelProposalClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll(array $objects, array $options = [])
    {
        // TODO: Implement indexAll() method.
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        // TODO: Implement remove() method.
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = [])
    {
        // TODO: Implement removeAll() method.
    }

    private function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException('Only product model proposals with an "id" property can be indexed in the search engine.');
        }
    }
}
