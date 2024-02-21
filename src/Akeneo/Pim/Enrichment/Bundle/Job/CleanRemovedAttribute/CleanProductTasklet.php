<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductUuidsWithRemovedAttributeInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanProductTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 100;

    private StepExecution $stepExecution;

    public function __construct(
        private readonly GetProductUuidsWithRemovedAttributeInterface $getProductUuidsWithRemovedAttribute,
        private readonly CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        private readonly RemoveValuesFromProducts $removeValuesFromProducts,
        private readonly JobRepositoryInterface $jobRepository
    ) {
    }

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
        $attributeCodes = $this->stepExecution->getJobParameters()->get('attribute_codes');

        $totalProductCount = $this->countProductsWithRemovedAttribute->count($attributeCodes);
        $this->stepExecution->setTotalItems($totalProductCount);
        $this->stepExecution->incrementSummaryInfo('read', $totalProductCount);

        foreach ($this->getProductUuidsWithRemovedAttribute->nextBatch(
            $attributeCodes,
            self::BATCH_SIZE
        ) as $uuids) {
            $this->removeValuesFromProducts->forAttributeCodes($attributeCodes, $uuids);
            $productCount = count($uuids);
            $this->stepExecution->incrementSummaryInfo('process', $productCount);
            $this->stepExecution->incrementProcessedItems($productCount);
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }
}
