<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product model indexer, define custom logic and options for product model indexing in the search engine.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelIndexer implements ProductIndexerInterface
{
    private const PRODUCT_MODEL_IDENTIFIER_PREFIX = 'product_model_';

    /**
     * Index type is not used anymore in elasticsearch 6, but is still needed by Client
     */
    const INDEX_TYPE = 'pim_catalog_product';

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param NormalizerInterface             $normalizer
     * @param Client                          $productAndProductModelClient
     * @param ProductModelRepositoryInterface $productModelRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        Client $productAndProductModelClient,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->normalizer = $normalizer;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * @param string $productModelIdentifier
     * @param array  $options
     */
    public function indexFromProductIdentifier(string $productModelIdentifier, array $options = []): void
    {
        $this->indexFromProductIdentifiers([$productModelIdentifier], $options);
    }

    /**
     * @param array $productModelIdentifiers
     * @param array $options
     */
    public function indexFromProductIdentifiers(array $productModelIdentifiers, array $options = []): void
    {
        $normalizedProductModels = [];
        foreach ($productModelIdentifiers as $productModelIdentifier) {
            $object = $this->productModelRepository->findOneByIdentifier($productModelIdentifier);
            if (!$object instanceof ProductModelInterface) {
                continue;
            }

            $normalizedProductModel = $this->normalizer->normalize(
                $object,
                ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedProductModel);
            $normalizedProductModels[] = $normalizedProductModel;
        }

        if (count($normalizedProductModels) === 1) {
            $this->productAndProductModelClient->index(
                self::INDEX_TYPE,
                $normalizedProductModels[0]['id'],
                $normalizedProductModels[0]
            );
        } elseif (count($normalizedProductModels) > 1) {
            $this->productAndProductModelClient->bulkIndexes(
                self::INDEX_TYPE,
                $normalizedProductModels,
                'id',
                $options['index_refresh'] ?? Refresh::disable()
            );
        }
    }

    /**
     * Removes the products from both the product index and the product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductId(string $productModelId, array $options = []): void
    {
        $this->removeManyFromProductIds([$productModelId], $options);
    }

    /**
     * Removes the products from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeManyFromProductIds(array $productModelIds, array $options = []): void
    {
        if (empty($productModelIds)) {
            return;
        }

        if (count($productModelIds) === 1) {
            $this->productAndProductModelClient->delete(
                self::INDEX_TYPE,
                self::PRODUCT_MODEL_IDENTIFIER_PREFIX . $productModelIds[0]
            );
            $this->removeDescendantsOf($productModelIds[0]);

            return;
        }

        $this->productAndProductModelClient->bulkDelete(self::INDEX_TYPE, array_map(
            function ($productModelId) {
                return self::PRODUCT_MODEL_IDENTIFIER_PREFIX . $productModelId;
            },
            $productModelIds
        ));
    }

    /**
     * Queries all the different ES indexes to remove any document having a reference to this objectId in its ancestors
     *
     * @param string $objectId
     */
    private function removeDescendantsOf(string $objectId): void
    {
        $this->productAndProductModelClient->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => self::PRODUCT_MODEL_IDENTIFIER_PREFIX.$objectId,
                ],
            ],
        ]);
    }

    /**
     * Removes the products from both the product model index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[]  = self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId;
        }
        $this->productAndProductModelClient->bulkDelete(self::INDEX_TYPE, $objectIds);
    }

    /**
     * {@inheritdoc}
     */
    private function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException(
                'Only product models with an "id" property can be indexed in the search engine.'
            );
        }
    }
}
