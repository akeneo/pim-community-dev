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
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
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

    /** @var ProductIndexerInterface */
    private $productIndexer;

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
        ProductIndexerInterface $productIndexer,
        BulkIndexerInterface $bulkProductModelIndexer,
        IndexerInterface $productModelIndexer,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        int $batchSize
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->pqbFactory = $pqbFactory;
        $this->productIndexer = $productIndexer;
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
                $identifiers = array_map(function (ProductInterface $product) {
                    return $product->getIdentifier();
                }, $productsBatch);

                $this->computeAndPersistProductCompletenesses->fromProductIdentifiers($identifiers);
                $this->indexProducts($identifiers);
                $productsBatch = [];
            }
        }

        if (!empty($productsBatch)) {
            $identifiers = array_map(function (ProductInterface $product) {
                return $product->getIdentifier();
            }, $productsBatch);

            $this->computeAndPersistProductCompletenesses->fromProductIdentifiers($identifiers);
            $this->indexProducts($identifiers);
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
     * @param string[] $identifiers
     */
    private function indexProducts(array $identifiers): void
    {
        $identifiersToIndex = [];
        foreach ($identifiers as $identifier) {
            $identifiersToIndex[] = $identifier;
            if (0 === count($identifiersToIndex) % self::INDEX_BULK_SIZE) {
                $this->productIndexer->indexFromProductIdentifiers(
                    $identifiersToIndex,
                    ['index_refresh' => Refresh::disable()]
                );
                $identifiersToIndex = [];
            }
        }

        if (!empty($identifiersToIndex)) {
            $this->productIndexer->indexFromProductIdentifiers(
                $identifiersToIndex,
                ['index_refresh' => Refresh::disable()]
            );
        }
    }
}
