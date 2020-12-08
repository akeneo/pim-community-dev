<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class WhitelistAttributeCodesTasklet implements TaskletInterface
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

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $attributeCodes = $this->stepExecution
            ->getJobExecution()
            ->getJobParameters()
            ->get('attribute_codes');

        foreach ($attributeCodes as $attributeCode) {
            $this->attributeCodeBlacklister->whitelist($attributeCode);
        }
    }
}
