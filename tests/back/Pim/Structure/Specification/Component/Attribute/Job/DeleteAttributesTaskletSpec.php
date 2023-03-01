<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Pim\Structure\Component\Attribute\Job\DeleteAttributesTasklet;
use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteAttributesTaskletSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository,
        RemoverInterface $attributeRemover,
        TranslatorInterface $translator,
    ): void {
        $this->beConstructedWith(
            $attributeRepository,
            $attributeRemover,
            $translator,
        );
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldHaveType(DeleteAttributesTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_track_processed_items(): void
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    public function it_throws_an_exception_if_step_execution_is_not_set(): void
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'In order to execute "%s" you need to set a step execution.',
                        DeleteAttributesTasklet::class
                    )
                )
            )
            ->during('execute');
    }

    public function it_deletes_attributes(
        AttributeRepositoryInterface $attributeRepository,
        RemoverInterface $attributeRemover,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ): void {
        $this->setStepExecution($stepExecution);
        $filters = [
            'field' => 'id',
            'operator' => 'IN',
            'values' => ['attribute_1', 'attribute_2', 'attribute_3'],
        ];

        $attribute1 = new Attribute();
        $attribute2 = new Attribute();
        $attribute3 = new Attribute();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $attributeRepository->findByCodes(['attribute_1', 'attribute_2', 'attribute_3'])
            ->willReturn([$attribute1, $attribute2, $attribute3]);

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('deleted_attributes', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('skipped_attributes', 0)->shouldBeCalled();

        $attributeRemover->remove($attribute1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attributes')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $attributeRemover->remove($attribute2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attributes')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $attributeRemover->remove($attribute3)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attributes')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $this->execute();
    }

    public function it_catches_attribute_removal_exceptions(
        AttributeRepositoryInterface $attributeRepository,
        RemoverInterface $attributeRemover,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        TranslatorInterface $translator,
    ): void {
        $this->setStepExecution($stepExecution);
        $filters = [
            'field' => 'id',
            'operator' => 'IN',
            'values' => ['attribute_1'],
        ];

        $attribute1 = new Attribute();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $attributeRepository->findByCodes(['attribute_1'])
            ->willReturn([$attribute1]);

        $stepExecution->setTotalItems(1)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('deleted_attributes', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('skipped_attributes', 0)->shouldBeCalled();

        $attributeRemover->remove($attribute1)->willThrow(new CannotRemoveAttributeException('an error'));
        $translator->trans('an error', [])->willReturn('an error');
        $stepExecution->addWarning('an error', [], Argument::type(DataInvalidItem::class))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_attributes')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $this->execute();
    }
}
