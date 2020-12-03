<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class WhitelistAttributeCodeTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private StepExecution $stepExecution;
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
        $attributeCode = $this->stepExecution
            ->getJobExecution()
            ->getJobParameters()
            ->get('attribute_code');

        $this->attributeCodeBlacklister->whitelist($attributeCode);
    }
}
