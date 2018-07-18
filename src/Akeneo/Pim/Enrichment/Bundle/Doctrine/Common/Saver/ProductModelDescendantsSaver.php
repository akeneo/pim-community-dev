<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Manager\CompletenessManager;
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

    /**
     * @param ObjectManager                       $entityManager
     * @param ProductModelRepositoryInterface     $productModelRepository
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param CompletenessManager                 $completenessManager
     * @param BulkIndexerInterface                $productIndexer
     * @param BulkIndexerInterface                $bulkProductModelIndexer
     * @param IndexerInterface                    $productModelIndexer
     */
    public function __construct(
        ObjectManager $entityManager,
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessManager $completenessManager,
        BulkIndexerInterface $productIndexer,
        BulkIndexerInterface $bulkProductModelIndexer,
        IndexerInterface $productModelIndexer
    ) {
        $this->objectManager = $entityManager;
        $this->productModelRepository = $productModelRepository;
        $this->completenessManager = $completenessManager;
        $this->bulkProductIndexer = $productIndexer;
        $this->bulkProductModelIndexer = $bulkProductModelIndexer;
        $this->pqbFactory = $pqbFactory;
        $this->productModelIndexer = $productModelIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public function save($productModel, array $options = []): void
    {
        $this->validateProductModel($productModel);

        $identifiers = $this->productModelRepository->findDescendantProductIdentifiers($productModel);
        $pqb = $this->pqbFactory->create();
        $pqb->addFilter('identifier', Operators::IN_LIST, $identifiers);
        $productsDescendants = $pqb->execute();

        if (0 !== count($productsDescendants)) {
            $this->computeCompletenesses($productsDescendants);
            $this->indexProducts($productsDescendants);
        }

        $productModelsChildren = $this->productModelRepository->findChildrenProductModels($productModel);
        if (!empty($productModelsChildren)) {
            $this->bulkProductModelIndexer->indexAll($productModelsChildren);
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
     * Computes the completeness of the given products
     *
     * @param CursorInterface $products
     */
    private function computeCompletenesses(CursorInterface $products): void
    {
        foreach ($products as $product) {
            $this->completenessManager->schedule($product);
            $this->completenessManager->generateMissingForProduct($product);
            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();
    }

    /**
     * Indexes a list of products by bulk of 100.
     *
     * @param CursorInterface $products
     */
    private function indexProducts(CursorInterface $products): void
    {
        $productsToIndex = [];
        foreach ($products as $product) {
            $productsToIndex[] = $product;

            if (0 === count($productsToIndex) % self::INDEX_BULK_SIZE) {
                $this->bulkProductIndexer->indexAll($productsToIndex);
                $productsToIndex = [];
            }
        }
        $this->bulkProductIndexer->indexAll($productsToIndex);
    }
}
