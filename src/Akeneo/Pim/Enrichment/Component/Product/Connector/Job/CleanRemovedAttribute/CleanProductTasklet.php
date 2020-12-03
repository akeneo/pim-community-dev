<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\CleanRemovedAttribute;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanProductTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 100;

    private StepExecution $stepExecution;
    private GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute;
    private CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute;
    private RemoveValuesFromProducts $removeValuesFromProducts;

    public function __construct(
        GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute,
        CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        RemoveValuesFromProducts $removeValuesFromProducts
    ) {
        $this->getProductIdentifiersWithRemovedAttribute = $getProductIdentifiersWithRemovedAttribute;
        $this->countProductsWithRemovedAttribute = $countProductsWithRemovedAttribute;
        $this->removeValuesFromProducts = $removeValuesFromProducts;
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
        $attributeCode = $this->stepExecution->getJobParameters()->get('attribute_code');

        $this->stepExecution->setTotalItems($this->countProductsWithRemovedAttribute->count([$attributeCode]));
        foreach ($this->getProductIdentifiersWithRemovedAttribute->nextBatch(
            [$attributeCode],
            self::BATCH_SIZE
        ) as $identifiers) {
            $this->removeValuesFromProducts->forAttributeCode($attributeCode, $identifiers);
            $this->stepExecution->incrementProcessedItems(count($identifiers));
        }
    }
}
