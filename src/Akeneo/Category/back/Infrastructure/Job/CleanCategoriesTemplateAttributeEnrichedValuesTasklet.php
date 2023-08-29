<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues\CleanCategoryTemplateAttributeAndEnrichedValuesCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesTemplateAttributeEnrichedValuesTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $templateUuid = $jobParameters->get('template_uuid');
        $attributeUuid = $jobParameters->get('attribute_uuid');

        $this->commandBus->dispatch(
            new CleanCategoryTemplateAttributeAndEnrichedValuesCommand($templateUuid, $attributeUuid),
        );
    }
}
