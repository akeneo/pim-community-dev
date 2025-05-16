<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale\CleanCategoryEnrichedValuesByChannelOrLocaleCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;

class CleanCategoriesEnrichedValuesTaskletSpec extends ObjectBehavior
{
    function let(CommandBus $commandBus): void
    {
        $this->beConstructedWith($commandBus);
    }

    function it_is_initializable(): void
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(CleanCategoriesEnrichedValuesTasklet::class);
    }

    function it_dispatches_a_command_message_to_clean_category_enriched_values_by_channel_or_locale(
        CommandBus $commandBus,
        StepExecution $stepExecution
    ): void {
        $jobParameters = new JobParameters([
            'channel_code' => 'ecommerce',
            'locales_codes' => ['en_US', 'fr_FR']
        ]);
        $command = new CleanCategoryEnrichedValuesByChannelOrLocaleCommand(
            'ecommerce',
            ['en_US', 'fr_FR']
        );
        $envelope = new Envelope($command);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $commandBus->dispatch($command)->willReturn($envelope);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
