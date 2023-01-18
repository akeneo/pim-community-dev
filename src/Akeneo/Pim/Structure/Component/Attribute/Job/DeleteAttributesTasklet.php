<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAttributesTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(
                sprintf('In order to execute "%s" you need to set a step execution.', static::class)
            );
        }

        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $attributesCount = count($filters['attribute_ids']);

        $this->stepExecution->addSummaryInfo('deleted_attributes', $attributesCount);
    }
}
