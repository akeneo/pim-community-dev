<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product model indexer, define custom logic and options for product model indexing in the search engine.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelIndexer implements ProductModelIndexerInterface
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
     * Indexes a product model in the product and product model index from its code.
     *
     * {@inheritdoc}
     */
    public function indexFromProductModelCode(string $productModelCode, array $options = []): void
    {
        $this->indexFromProductModelCodes([$productModelCode], $options);
    }

    /**
     * Indexes a list of product models in the product and product model index from their codes.
     *
     * {@inheritdoc}
     */
    public function indexFromProductModelCodes(array $productModelCodes, array $options = []): void
    {
        $normalizedProductModels = [];
        foreach ($productModelCodes as $productModelCode) {
            $object = $this->productModelRepository->findOneByIdentifier($productModelCode);
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

        if (!empty($normalizedProductModels)) {
            $this->productAndProductModelClient->bulkIndexes(
                self::INDEX_TYPE,
                $normalizedProductModels,
                'id',
                $options['index_refresh'] ?? Refresh::disable()
            );
        }
    }

    /**
     * Removes the product model from the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductModelId(int $productModelId, array $options = []): void
    {
        $this->removeFromProductModelIds([$productModelId], $options);
    }

    /**
     * Removes the product models from the product and product model index.
     * Removes also the descendants of the product models (and the descendants of the descendants, etc...).
     *
     * {@inheritdoc}
     */
    public function removeFromProductModelIds(array $productModelIds, array $options = []): void
    {
        if (empty($productModelIds)) {
            return;
        }

        $indexIdentifiers = array_map(
            function ($productModelId) {
                return self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $productModelId;
            },
            $productModelIds
        );

        $this->productAndProductModelClient->deleteByQuery([
            'query' => [
                'bool' => [
                    'should' => [
                        ['terms' => ['id' => $indexIdentifiers]],
                        ['terms' => ['ancestors.ids' => $indexIdentifiers]],
                    ],
                ],
            ],
        ]);
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
