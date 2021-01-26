<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
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
 *  Computation of the completeness for all products belonging to a family that has been updated by mass action
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO refactor with Akeneo\Pim\Enrichment\Component\Product\Job\ComputeCompletenessOfProductsFamilyTasklet
 *            that work only for unitary update
 */
class ComputeCompletenessOfFamilyProductsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 1000;

    private StepExecution $stepExecution;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private ItemReaderInterface $familyReader;
    private EntityManagerClearerInterface $cacheClearer;
    private JobRepositoryInterface $jobRepository;
    private CompletenessCalculator $completenessCalculator;
    private SaveProductCompletenesses $saveProductCompletenesses;

    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->familyReader = $familyReader;
        $this->cacheClearer = $cacheClearer;
        $this->jobRepository = $jobRepository;
        $this->completenessCalculator = $completenessCalculator;
        $this->saveProductCompletenesses = $saveProductCompletenesses;
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

        $familyCodes = $this->extractFamilyCodes();
        if (empty($familyCodes)) {
            return;
        }

        $products = $this->getProductsForFamilies($familyCodes);
        $this->stepExecution->setTotalItems($products->count());

        $productsToCompute = [];
        foreach ($products as $product) {
            Assert::isInstanceOf($product, ProductInterface::class);
            $productsToCompute[] = $product->getIdentifier();

            if (count($productsToCompute) >= self::BATCH_SIZE) {
                $this->computeCompleteness($productsToCompute);
                $productsToCompute = [];
            }
        }

        if (count($productsToCompute) > 0) {
            $this->computeCompleteness($productsToCompute);
        }
    }

    private function computeCompleteness(array $productIdentifiers): void
    {
        $completenessCollections = $this->completenessCalculator->fromProductIdentifiers($productIdentifiers);
        $this->saveProductCompletenesses->saveAll($completenessCollections);

        $this->stepExecution->incrementProcessedItems(count($productIdentifiers));
        $this->stepExecution->incrementSummaryInfo('process', count($productIdentifiers));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function extractFamilyCodes()
    {
        $familyCodes = [];
        while (true) {
            $family = $this->familyReader->read();
            if (null === $family) {
                break;
            }

            Assert::isInstanceOf($family, FamilyInterface::class);

            $familyCodes[] = $family->getCode();
        }

        return $familyCodes;
    }

    private function getProductsForFamilies(array $familyCodes): CursorInterface
    {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $productQueryBuilder->addFilter('family', Operators::IN_LIST, $familyCodes);

        return $productQueryBuilder->execute();
    }
}
