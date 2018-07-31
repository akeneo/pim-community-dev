<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

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

    /** @var BulkIndexerInterface */
    private $bulkProductIndexer;

    /** @var ObjectManager */
    private $objectManager;

    /** @var CompletenessManager */
    private $completenessManager;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var IndexerInterface */
    private $productModelIndexer;

    /** @var BulkObjectDetacherInterface */
    private $bulkObjectDetacher;

    /** @var integer */
    private $batchSize;

    /**
     * @param ObjectManager                       $entityManager
     * @param ProductModelRepositoryInterface     $productModelRepository
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param CompletenessManager                 $completenessManager
     * @param BulkIndexerInterface                $bulkProductIndexer
     * @param BulkIndexerInterface                $bulkProductModelIndexer
     * @param IndexerInterface                    $productModelIndexer
     * @param BulkObjectDetacherInterface         $bulkObjectDetacher
     * @param integer                             $batchSize
     *
     * TODO Merge: Remove default values for the 3 last parameters
     */
    public function __construct(
        ObjectManager $entityManager,
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessManager $completenessManager,
        BulkIndexerInterface $bulkProductIndexer,
        BulkIndexerInterface $bulkProductModelIndexer,
        IndexerInterface $productModelIndexer = null,
        BulkObjectDetacherInterface $bulkObjectDetacher = null,
        int $batchSize = self::INDEX_BULK_SIZE
    ) {
        $this->objectManager = $entityManager;
        $this->productModelRepository = $productModelRepository;
        $this->completenessManager = $completenessManager;
        $this->bulkProductIndexer = $bulkProductIndexer;
        $this->bulkProductModelIndexer = $bulkProductModelIndexer;
        $this->pqbFactory = $pqbFactory;
        $this->productModelIndexer = $productModelIndexer;
        $this->bulkObjectDetacher = $bulkObjectDetacher;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     *
     * TODO Merge: Remove check on nullable cache clearer
     */
    public function save($productModel, array $options = []): void
    {
        $this->validateProductModel($productModel);

        $this->computeCompletenessAndIndexDescendantProducts($productModel);

        $this->indexProductModelChildren($productModel);
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * TODO Merge: Remove check on nullable bulk object detacher
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
                $this->computeCompletenesses($productsBatch);
                $this->indexProducts($productsBatch);

                if (null !== $this->bulkObjectDetacher) {
                    $this->bulkObjectDetacher->detachAll($productsBatch);
                }
                $productsBatch = [];
            }
        }

        if (!empty($productsBatch)) {
            $this->computeCompletenesses($productsBatch);
            $this->indexProducts($productsBatch);
            if (null !== $this->bulkObjectDetacher) {
                $this->bulkObjectDetacher->detachAll($productsBatch);
            }
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
         *
         * @TODO We need to remove the if condition during the pull day
         */
        if (null !== $this->productModelIndexer) {
            $this->productModelIndexer->index($productModel);
        }
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
     * Computes the completeness of the given products
     *
     * @param array $products
     */
    private function computeCompletenesses(array $products): void
    {
        $this->completenessManager->bulkSchedule($products);

        foreach ($products as $product) {
            $this->completenessManager->generateMissingForProduct($product);
            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();
    }

    /**
     * Indexes a list of products by bulk of 100.
     *
     * @param array $products
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
