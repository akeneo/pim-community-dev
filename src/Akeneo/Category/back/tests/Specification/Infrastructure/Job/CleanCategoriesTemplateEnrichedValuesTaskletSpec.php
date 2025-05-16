<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues\CleanCategoryTemplateAndEnrichedValuesCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesTemplateEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesTemplateEnrichedValuesTaskletSpec extends ObjectBehavior
{
    function let(CommandBus $commandBus): void
    {
        $this->beConstructedWith($commandBus);
    }

    function it_is_initializable(): void
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(CleanCategoriesTemplateEnrichedValuesTasklet::class);
    }

    function it_dispatches_a_command_message_to_clean_category_enriched_values_by_template_uuid(
        CommandBus $commandBus,
        StepExecution $stepExecution
    ): void {
        $jobParameters = new JobParameters([
            'template_uuid' => '2115af0f-f0b0-435e-aa86-9880eaad677e',
        ]);
        $command = new CleanCategoryTemplateAndEnrichedValuesCommand(
            '2115af0f-f0b0-435e-aa86-9880eaad677e'
        );

        $envelope = new Envelope($command);

        $stepExecution->getJobParameters()->shouldBeCalled()->willReturn($jobParameters);
        $commandBus->dispatch($command)->shouldBeCalled()->willReturn($envelope);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
