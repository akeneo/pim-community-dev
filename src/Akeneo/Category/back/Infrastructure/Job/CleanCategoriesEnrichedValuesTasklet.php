<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale\CleanCategoryEnrichedValuesByChannelOrLocaleCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesEnrichedValuesTasklet implements TaskletInterface
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
        $channelCode = $jobParameters->get('channel_code');
        $locales = $jobParameters->get('locales_codes') ?? [];

        $this->commandBus->dispatch(
            new CleanCategoryEnrichedValuesByChannelOrLocaleCommand($channelCode, $locales),
        );
    }
}
