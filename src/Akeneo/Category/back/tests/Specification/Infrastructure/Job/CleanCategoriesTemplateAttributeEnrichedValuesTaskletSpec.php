<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues\CleanCategoryTemplateAttributeAndEnrichedValuesCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesTemplateAttributeEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesTemplateAttributeEnrichedValuesTaskletSpec extends ObjectBehavior
{
    function let(CommandBus $commandBus): void
    {
        $this->beConstructedWith($commandBus);
    }

    function it_is_initializable(): void
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(CleanCategoriesTemplateAttributeEnrichedValuesTasklet::class);
    }

    function it_dispatches_a_command_message_to_clean_category_enriched_values_by_template_uuid(
        CommandBus $commandBus,
        StepExecution $stepExecution
    ): void {
        $templateUuid = '2115af0f-f0b0-435e-aa86-9880eaad677e';
        $attributeUuid = 'c87c8b3c-5642-425c-a3b7-8dd5bc503e67';
        $jobParameters = new JobParameters([
            'template_uuid' => $templateUuid,
            'attribute_uuid' => $attributeUuid,
        ]);
        $command = new CleanCategoryTemplateAttributeAndEnrichedValuesCommand(
            $templateUuid,
            $attributeUuid
        );

        $envelope = new Envelope($command);

        $stepExecution->getJobParameters()->shouldBeCalled()->willReturn($jobParameters);
        $commandBus->dispatch($command)->shouldBeCalled()->willReturn($envelope);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
