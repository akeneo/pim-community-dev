<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IndexFamilyProductsAndProductModelsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const DEFAULT_BATCH_SIZE = 100;

    private StepExecution $stepExecution;
    private JobRepositoryInterface $jobRepository;
    private ItemReaderInterface $familyReader;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory;
    private ProductAndAncestorsIndexer $productAndAncestorsIndexer;
    private ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer;
    private EntityManagerClearerInterface $cacheClearer;
    private int $batchSize;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        ItemReaderInterface $familyReader,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        EntityManagerClearerInterface $cacheClearer,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->jobRepository = $jobRepository;
        $this->familyReader = $familyReader;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productModelQueryBuilderFactory = $productModelQueryBuilderFactory;
        $this->productAndAncestorsIndexer = $productAndAncestorsIndexer;
        $this->productModelDescendantsAndAncestorsIndexer = $productModelDescendantsAndAncestorsIndexer;
        $this->cacheClearer = $cacheClearer;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->familyReader instanceof InitializableInterface) {
            $this->familyReader->initialize();
        }

        $familyCodes = $this->getFamilyCodes();
        if (empty($familyCodes)) {
            return;
        }

        $products = $this->getProductsForFamilies($familyCodes);
        $productModels = $this->getProductModelsForFamilies($familyCodes);

        $this->stepExecution->setTotalItems($products->count() + $productModels->count());

        $productIdentifiersToIndex = [];
        $productModelCodesToIndex = [];

        foreach ($products as $product) {
            Assert::isInstanceOf($product, ProductInterface::class);
            $productIdentifiersToIndex[] = $product->getIdentifier();

            if (count($productIdentifiersToIndex) >= $this->batchSize) {
                $this->indexProducts($productIdentifiersToIndex);
                $this->cacheClearer->clear();
                $productIdentifiersToIndex = [];
            }
        }

        if (count($productIdentifiersToIndex) > 0) {
            $this->indexProducts($productIdentifiersToIndex);
        }

        foreach ($productModels as $productModel) {
            Assert::isInstanceOf($productModel, ProductModelInterface::class);
            $productModelCodesToIndex[] = $productModel->getCode();

            if (count($productModelCodesToIndex) >= $this->batchSize) {
                $this->indexProductModels($productModelCodesToIndex);
                $this->cacheClearer->clear();
                $productModelCodesToIndex = [];
            }
        }

        if (count($productModelCodesToIndex) > 0) {
            $this->indexProductModels($productModelCodesToIndex);
        }
    }

    /**
     * @return string[]
     */
    private function getFamilyCodes(): array
    {
        $familyCodes = [];
        while (null !== $family = $this->readFamily()) {
            $familyCodes[] = $family->getCode();
        }

        return $familyCodes;
    }

    private function readFamily(): ?FamilyInterface
    {
        $family = $this->familyReader->read();
        Assert::nullOrIsInstanceOf($family, FamilyInterface::class);
        return $family;
    }

    /**
     * @param string[] $familyCodes
     */
    private function getProductsForFamilies(array $familyCodes): CursorInterface
    {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $productQueryBuilder->addFilter('family', Operators::IN_LIST, $familyCodes);

        return $productQueryBuilder->execute();
    }

    /**
     * @param string[] $familyCodes
     */
    private function getProductModelsForFamilies(array $familyCodes): CursorInterface
    {
        $pqb = $this->productModelQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, $familyCodes);

        return $pqb->execute();
    }

    /**
     * @param string[] $productIdentifiers
     */
    private function indexProducts(array $productIdentifiers): void
    {
        $this->productAndAncestorsIndexer->indexFromProductIdentifiers($productIdentifiers);

        $productCount = count($productIdentifiers);

        $this->stepExecution->incrementProcessedItems($productCount);
        $this->stepExecution->incrementSummaryInfo('process', $productCount);
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    /**
     * @param string[] $productModelCodes
     */
    private function indexProductModels(array $productModelCodes): void
    {
        $this->productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes);

        $productModelCount = count($productModelCodes);

        $this->stepExecution->incrementProcessedItems($productModelCount);
        $this->stepExecution->incrementSummaryInfo('process', $productModelCount);
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }
}
