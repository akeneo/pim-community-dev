<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposalNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
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
        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $normalizedObject = $this->normalizer->normalize($object, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->productModelProposalClient->index($this->indexType, $normalizedObject['id'], $normalizedObject, $indexRefresh);
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::waitFor();

        $normalizedProductModels = [];
        foreach ($objects as $object) {
            $normalizedProductModel = $this->normalizer->normalize(
                $object,
                ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX
            );
            $this->validateObjectNormalization($normalizedProductModel);
            $normalizedProductModels[] = $normalizedProductModel;
        }

        $this->productModelProposalClient->bulkIndexes($this->indexType, $normalizedProductModels, 'id', $indexRefresh);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = [])
    {
        $documents = $this->productModelProposalClient->search(
            $this->indexType,
            ['query' => ['term' => ['id' => self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId]]]
        );

        if (0 !== $documents['hits']['total']) {
            $this->productModelProposalClient->delete(
                $this->indexType,
                self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId
            );
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        if ($indexRefresh instanceof Refresh && Refresh::ENABLE === $indexRefresh->getType()) {
            $this->productModelProposalClient->refreshIndex();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = [])
    {
        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[]  = self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId;
        }
        $this->productModelProposalClient->bulkDelete($this->indexType, $objectIds);
    }

    private function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException('Only product model proposals with an "id" property can be indexed in the search engine.');
        }
    }
}
