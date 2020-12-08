<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanProductModelTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 100;

    private StepExecution $stepExecution;
    private GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelsIdentifiersWithRemovedAttribute;
    private CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute;
    private RemoveValuesFromProductModels $removeValuesFromProductModels;
    private JobRepositoryInterface $jobRepository;

    public function __construct(
        GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelsIdentifiersWithRemovedAttribute,
        CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute,
        RemoveValuesFromProductModels $removeValuesFromProductModels,
        JobRepositoryInterface $jobRepository
    ) {
        $this->getProductModelsIdentifiersWithRemovedAttribute = $getProductModelsIdentifiersWithRemovedAttribute;
        $this->countProductModelsWithRemovedAttribute = $countProductModelsWithRemovedAttribute;
        $this->removeValuesFromProductModels = $removeValuesFromProductModels;
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

        $this->stepExecution->setTotalItems($this->countProductModelsWithRemovedAttribute->count($attributeCodes));
        foreach ($this->getProductModelsIdentifiersWithRemovedAttribute->nextBatch(
            $attributeCodes,
            self::BATCH_SIZE
        ) as $identifiers) {
            $this->removeValuesFromProductModels->forAttributeCodes($attributeCodes, $identifiers);
            $this->stepExecution->incrementProcessedItems(count($identifiers));
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }
}
