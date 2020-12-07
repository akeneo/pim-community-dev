<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanProductTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 100;

    private StepExecution $stepExecution;
    private GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute;
    private CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute;
    private RemoveValuesFromProducts $removeValuesFromProducts;
    private JobRepositoryInterface $jobRepository;

    public function __construct(
        GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute,
        CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        RemoveValuesFromProducts $removeValuesFromProducts,
        JobRepositoryInterface $jobRepository
    ) {
        $this->getProductIdentifiersWithRemovedAttribute = $getProductIdentifiersWithRemovedAttribute;
        $this->countProductsWithRemovedAttribute = $countProductsWithRemovedAttribute;
        $this->removeValuesFromProducts = $removeValuesFromProducts;
        $this->jobRepository = $jobRepository;
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

        $this->stepExecution->setTotalItems($this->countProductsWithRemovedAttribute->count($attributeCodes));
        foreach ($this->getProductIdentifiersWithRemovedAttribute->nextBatch(
            $attributeCodes,
            self::BATCH_SIZE
        ) as $identifiers) {
            $this->removeValuesFromProducts->forAttributeCodes($attributeCodes, $identifiers);
            $this->stepExecution->incrementProcessedItems(count($identifiers));
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }
}
