<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

class ComputeCompletenessOfTableAttributeProductsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 1000;
    private StepExecution $stepExecution;

    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private CompletenessCalculator $completenessCalculator,
        private SaveProductCompletenesses $saveProductCompletenesses,
        private JobRepositoryInterface $jobRepository,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $attributeCode = $this->stepExecution->getJobParameters()['attribute_code'];
        $familyCodes = $this->stepExecution->getJobParameters()['family_codes'];

        $productIdentifiers = $this->getProductIdentifiersFromTableAttributeCodes($attributeCode, $familyCodes);
        if(\count($productIdentifiers) === 0) {
            return;
        }
        $this->stepExecution->setTotalItems($productIdentifiers->count());
        $productsToCompute = [];
        foreach ($productIdentifiers as $identifier) {
            $productsToCompute[] = $identifier;
            if (count($productsToCompute) >= self::BATCH_SIZE) {
                $this->computeCompleteness($productsToCompute);
                $productsToCompute = [];
            }
        }

        if (count($productsToCompute) > 0) {
            $this->computeCompleteness($productsToCompute);
        }
    }

    public function isTrackable(): bool
    {
        return true;
    }

    private function computeCompleteness(array $productIdentifiers): void
    {
        $completenessCollections = $this->completenessCalculator->fromProductIdentifiers($productIdentifiers);
        $this->saveProductCompletenesses->saveAll($completenessCollections);

        $this->stepExecution->incrementProcessedItems(count($productIdentifiers));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function getProductIdentifiersFromTableAttributeCodes(string $attributeCode, array $familyCodes): CursorInterface
    {
        $pqb = $this->productQueryBuilderFactory->create();

        $pqb->addFilter('family', Operators::IN_LIST, $familyCodes);
        $pqb->addFilter($attributeCode, Operators::IS_NOT_EMPTY, null);

        return $pqb->execute();
    }
}
