<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\CleanRemovedAttribute;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class RegisterCleanJobTasklet implements TaskletInterface, TrackableTaskletInterface
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
        $attributeCode = $this->stepExecution
            ->getJobExecution()
            ->getJobParameters()
            ->get('attribute_code');

        if (!$attributeCode) {
            throw new \InvalidArgumentException('The clean removed attribute job requires an attribute code');
        }

        //TODO what do we do when the attribute code is not blacklisted?
        $this->attributeCodeBlacklister->registerJob(
            $attributeCode,
            $this->stepExecution->getJobExecution()->getId()
        );
    }
}
