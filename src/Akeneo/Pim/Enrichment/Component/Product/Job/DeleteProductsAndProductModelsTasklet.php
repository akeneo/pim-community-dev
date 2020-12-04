<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountVariantProductsInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CountProductModelsAndChildrenProductModelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;

/**
 * Delete products and product models
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteProductsAndProductModelsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    protected ?StepExecution $stepExecution = null;
    protected BulkRemoverInterface $productRemover;
    protected BulkRemoverInterface $productModelRemover;
    protected ProductQueryBuilderFactoryInterface $pqbFactory;
    protected EntityManagerClearerInterface $cacheClearer;
    protected ObjectFilterInterface $filter;
    protected int $batchSize = 100;
    private CountProductModelsAndChildrenProductModelsInterface $countProductModelsAndChildrenProductModels;
    private CountVariantProductsInterface $countVariantProducts;
    private JobStopper $jobStopper;
    private JobRepositoryInterface $jobRepository;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        BulkRemoverInterface $productRemover,
        BulkRemoverInterface $productModelRemover,
        EntityManagerClearerInterface $cacheClearer,
        ObjectFilterInterface $filter,
        int $batchSize,
        CountProductModelsAndChildrenProductModelsInterface $countProductModelsAndChildrenProductModels,
        CountVariantProductsInterface $countVariantProducts,
        JobStopper $jobStopper,
        JobRepositoryInterface $jobRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->productRemover = $productRemover;
        $this->productModelRemover = $productModelRemover;
        $this->cacheClearer = $cacheClearer;
        $this->batchSize = $batchSize;
        $this->filter = $filter;
        $this->countProductModelsAndChildrenProductModels = $countProductModelsAndChildrenProductModels;
        $this->countVariantProducts = $countVariantProducts;
        $this->jobStopper = $jobStopper;
        $this->jobRepository = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(
                sprintf('In order to execute "%s" you need to set a step execution.', static::class)
            );
        }

        $this->stepExecution->setTotalItems($this->countTotalItemsToDelete());
        $this->stepExecution->addSummaryInfo('deleted_products', 0);
        $this->stepExecution->addSummaryInfo('deleted_product_models', 0);

        $productsAndRootProductModels = $this->findSimpleProductsAndRootProductModels();
        $this->delete($productsAndRootProductModels);

        $subProductModels = $this->findSubProductModels();
        $this->delete($subProductModels);

        $variantProducts = $this->findVariantProducts();
        $this->delete($variantProducts);
    }

    private function findVariantProducts(): CursorInterface
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $options = ['filters' => $filters];

        $productQueryBuilder = $this->pqbFactory->create($options);
        $productQueryBuilder->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);
        $productQueryBuilder->addFilter('parent', Operators::IS_NOT_EMPTY, null);

        return $productQueryBuilder->execute();
    }

    private function findSubProductModels(): CursorInterface
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $options = ['filters' => $filters];

        $productQueryBuilder = $this->pqbFactory->create($options);
        $productQueryBuilder->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);
        $productQueryBuilder->addFilter('parent', Operators::IS_NOT_EMPTY, null);

        return $productQueryBuilder->execute();
    }

    /**
     * @return CursorInterface
     */
    private function findSimpleProductsAndRootProductModels(): CursorInterface
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $options = ['filters' => $filters];

        $productQueryBuilder = $this->pqbFactory->create($options);
        $productQueryBuilder->addFilter('parent', Operators::IS_EMPTY, null);

        return $productQueryBuilder->execute();
    }

    /**
     * @param CursorInterface $products
     */
    private function delete(CursorInterface $products): void
    {
        $loopCount = 0;
        $entitiesToRemove = [];
        while ($products->valid()) {
            $product = $products->current();
            if (!$this->filter->filterObject($product, 'pim.enrich.product.delete')) {
                $entitiesToRemove[] = $product;
            } else {
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems(1);
            }

            $loopCount++;
            if ($this->batchSizeIsReached($loopCount)) {
                if ($this->jobStopper->isStopping($this->stepExecution)) {
                    $this->jobStopper->stop($this->stepExecution);
                    return;
                }
                $this->doDelete($entitiesToRemove);
                $this->jobRepository->updateStepExecution($this->stepExecution);
                $entitiesToRemove = [];
            }
            $products->next();
            $this->stepExecution->incrementReadCount();
        }

        if (!empty($entitiesToRemove)) {
            $this->doDelete($entitiesToRemove);
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }

    /**
     * Deletes given products and product models, clears the cache and increments the summary info.
     *
     * @param array $entities
     */
    protected function doDelete(array $entities): void
    {
        $products = $this->filterProducts($entities);
        $productModels = $this->filterProductModels($entities);

        $deletedProductsCount = $this->countProductsToDelete($products, $productModels);
        $deletedProductModelsCount = $this->countProductModelsToDelete($productModels);

        $this->productRemover->removeAll($products);
        $this->stepExecution->incrementSummaryInfo('deleted_products', $deletedProductsCount);
        $this->stepExecution->incrementProcessedItems($deletedProductsCount);

        $this->productModelRemover->removeAll($productModels);
        $this->stepExecution->incrementSummaryInfo('deleted_product_models', $deletedProductModelsCount);
        $this->stepExecution->incrementProcessedItems($deletedProductModelsCount);

        $this->cacheClearer->clear();
    }

    /**
     * @param ProductInterface[] $products
     * @param ProductModelInterface[] $productModels
     *
     * @return int
     */
    private function countProductsToDelete(array $products, array $productModels): int
    {
        return count($products) + $this->countVariantProducts->forProductModelCodes(
            array_map(
                function (ProductModelInterface $productModel) {
                    return $productModel->getCode();
                },
                $productModels
            )
        );
    }

    /**
     * @param ProductModelInterface[] $productModels
     *
     * @return int
     */
    private function countProductModelsToDelete(array $productModels): int
    {
        return $this->countProductModelsAndChildrenProductModels->forProductModelCodes(
            array_map(
                function (ProductModelInterface $productModel) {
                    return $productModel->getCode();
                },
                $productModels
            )
        );
    }

    /**
     * @param int $loopCount
     *
     * @return bool
     */
    private function batchSizeIsReached(int $loopCount): bool
    {
        return 0 === $loopCount % $this->batchSize;
    }

    /**
     * Returns only entities that are products in the given array.
     *
     * @param array $entities
     *
     * @return ProductInterface[]
     */
    private function filterProducts(array $entities): array
    {
        return array_values(
            array_filter($entities, function ($item) {
                return $item instanceof ProductInterface;
            })
        );
    }

    /**
     * Returns only entities that are product models in the given array.
     *
     * @param array $entities
     *
     * @return ProductModelInterface[]
     */
    private function filterProductModels(array $entities): array
    {
        return array_values(
            array_filter($entities, function ($item) {
                return $item instanceof ProductModelInterface;
            })
        );
    }

    private function countTotalItemsToDelete(): int
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $options = ['filters' => $filters];

        $productQueryBuilder = $this->pqbFactory->create($options);
        $items = $productQueryBuilder->execute();

        return $items->count();
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
