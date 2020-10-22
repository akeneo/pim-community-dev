<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeCompletenessOfFamilyProductsTasklet implements TaskletInterface
{
    private const BATCH_SIZE = 1000;

    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var ItemReaderInterface */
    private $familyReader;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /** @var JobRepositoryInterface */
    private $jobRepository;

    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompletenesses;

    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->familyReader = $familyReader;
        $this->cacheClearer = $cacheClearer;
        $this->jobRepository = $jobRepository;
        $this->computeAndPersistProductCompletenesses = $computeAndPersistProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->familyReader instanceof InitializableInterface) {
            $this->familyReader->initialize();
        }

        $familyCodes = [];

        while(true) {
            $family = $this->familyReader->read();

            if (null === $family) {
                break;
            }

            Assert::isInstanceOf($family, FamilyInterface::class);

            $familyCodes[] = $family->getCode();
        }

        if (empty($familyCodes)) {
            return;
        }

        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $productQueryBuilder->addFilter('family', 'IN', $familyCodes);
        $products = $productQueryBuilder->execute();

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
        $this->computeAndPersistProductCompletenesses->fromProductIdentifiers($productIdentifiers);
        $this->stepExecution->incrementSummaryInfo('process', count($productIdentifiers));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }
}
