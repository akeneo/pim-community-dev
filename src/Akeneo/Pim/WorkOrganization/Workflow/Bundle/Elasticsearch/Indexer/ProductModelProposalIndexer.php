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

    public function __construct(
        NormalizerInterface $normalizer,
        Client $productModelProposalClient
    ) {
        $this->normalizer = $normalizer;
        $this->productModelProposalClient = $productModelProposalClient;
    }

    /**
     * {@inheritdoc}
     */
    public function index($object, array $options = [])
    {
        $normalizedObject = $this->normalizer->normalize($object, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->productModelProposalClient->index($normalizedObject['id'], $normalizedObject);
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

        $this->productModelProposalClient->bulkIndexes($normalizedProductModels, 'id', $indexRefresh);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = [])
    {
        $documents = $this->productModelProposalClient->search(
            ['query' => ['term' => ['id' => self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId]]]
        );

        if (0 !== $documents['hits']['total']['value']) {
            $this->productModelProposalClient->delete(
                self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId
            );
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
        $this->productModelProposalClient->bulkDelete($objectIds);
    }

    private function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException('Only product model proposals with an "id" property can be indexed in the search engine.');
        }
    }
}
