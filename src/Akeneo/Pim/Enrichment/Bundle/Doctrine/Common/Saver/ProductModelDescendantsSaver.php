<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * This class ensures two things:
 * - Recalculate the completeness for each *variant product* belonging to the subtree
 * - Trigger the reindexing of the model and variant product belonging to the subtree
 *
 * @internal
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelDescendantsSaver implements SaverInterface
{
    private const INDEX_BULK_SIZE = 100;

    /** @var BulkIndexerInterface */
    private $bulkProductModelIndexer;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var BulkIndexerInterface */
    private $bulkProductIndexer;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var IndexerInterface */
    private $productModelIndexer;

    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompletenesses;

    /** @var integer */
    private $batchSize;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        BulkIndexerInterface $bulkProductIndexer,
        BulkIndexerInterface $bulkProductModelIndexer,
        IndexerInterface $productModelIndexer,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        int $batchSize
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->pqbFactory = $pqbFactory;
        $this->bulkProductIndexer = $bulkProductIndexer;
        $this->bulkProductModelIndexer = $bulkProductModelIndexer;
        $this->productModelIndexer = $productModelIndexer;
        $this->computeAndPersistProductCompletenesses = $computeAndPersistProductCompletenesses;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function save($productModel, array $options = []): void
    {
        $this->validateProductModel($productModel);

        $this->computeCompletenessAndIndexDescendantProducts($productModel);

        $this->indexProductModelChildren($productModel);
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function computeCompletenessAndIndexDescendantProducts(ProductModelInterface $productModel): void
    {
        $identifiers = $this->productModelRepository->findDescendantProductIdentifiers($productModel);
        $pqb = $this->pqbFactory->create();
        $pqb->addFilter('identifier', Operators::IN_LIST, $identifiers);
        $productsDescendants = $pqb->execute();

        $count = 0;
        $productsBatch = [];
        foreach ($productsDescendants as $product) {
            $productsBatch[] = $product;

            if (++$count % $this->batchSize === 0) {
                $this->computeAndPersistProductCompletenesses->fromProductIdentifiers(
                    array_map(function (ProductInterface $product) {
                        return $product->getIdentifier();
                    }, $productsBatch)
                );
                $this->indexProducts($productsBatch);
                $productsBatch = [];
            }
        }

        if (!empty($productsBatch)) {
            $this->computeAndPersistProductCompletenesses->fromProductIdentifiers(
                array_map(function (ProductInterface $product) {
                    return $product->getIdentifier();
                }, $productsBatch)
            );
            $this->indexProducts($productsBatch);
        }
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function indexProductModelChildren(ProductModelInterface $productModel): void
    {
        $productModelsChildren = $this->productModelRepository->findChildrenProductModels($productModel);
        if (!empty($productModelsChildren)) {
            $this->bulkProductModelIndexer->indexAll($productModelsChildren, ['index_refresh' => Refresh::disable()]);
        }

        /**
         * In this method, we computed the completeness of the product model children. That means the ratio of complete
         * product models may change during this task so we need to index again the product model. If we don't do that
         * it may break the complete filter on the grid because wrong data was indexed in ES.
         *
         * You should have a look to https://akeneo.atlassian.net/browse/PIM-7388
         */
        $this->productModelIndexer->index($productModel);
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @throws \InvalidArgumentException
     */
    private function validateProductModel($productModel): void
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductModelInterface::class,
                    get_class($productModel)
                )
            );
        }
    }

    /**
     * Indexes a list of products by bulk of 100.
     *
     * @param ProductInterface[] $products
     */
    private function indexProducts(array $products): void
    {
        $productsToIndex = [];
        foreach ($products as $product) {
            $productsToIndex[] = $product;

            if (0 === count($productsToIndex) % self::INDEX_BULK_SIZE) {
                $this->bulkProductIndexer->indexAll($productsToIndex, ['index_refresh' => Refresh::disable()]);
                $productsToIndex = [];
            }
        }
        $this->bulkProductIndexer->indexAll($productsToIndex, ['index_refresh' => Refresh::disable()]);
    }
}
