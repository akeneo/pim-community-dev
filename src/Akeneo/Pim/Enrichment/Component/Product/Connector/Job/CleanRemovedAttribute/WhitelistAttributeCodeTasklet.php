<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\CleanRemovedAttribute;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class WhitelistAttributeCodeTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private AttributeCodeBlacklister $attributeCodeBlacklister;

    public function __construct(
        AttributeCodeBlacklister $attributeCodeBlacklister
    ) {
        $this->attributeCodeBlacklister = $attributeCodeBlacklister;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $attributeCode = $this->setStepExecution
            ->getJobExecution()
            ->getJobParameters()
            ->get('attribute_code');

        if (!$attributeCode) {
            throw new \InvalidArgumentException('the clean deleted attribute require an attribute code');
        }

        $this->attributeCodeBlacklister->whitelist($attributeCode);
    }
}
