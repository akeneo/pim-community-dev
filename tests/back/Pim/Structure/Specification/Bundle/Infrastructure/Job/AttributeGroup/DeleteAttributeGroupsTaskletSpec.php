<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup\DeleteAttributeGroupsTasklet;
use Akeneo\Pim\Structure\Component\Exception\AttributeGroupOtherCannotBeRemoved;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteAttributeGroupsTaskletSpec extends ObjectBehavior
{
    public function let(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        RemoverInterface $attributeGroupRemover,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
        StepExecution $stepExecution
    ): void {
        $jobStopper->isStopping($stepExecution)->willReturn(false);
        $this->beConstructedWith(
            $attributeGroupRepository,
            $attributeGroupRemover,
            $cacheClearer,
            $jobRepository,
            $jobStopper,
            100
        );

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldHaveType(DeleteAttributeGroupsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_track_processed_items(): void
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    public function it_deletes_attribute_groups(
        AttributeRepositoryInterface $attributeGroupRepository,
        RemoverInterface $attributeGroupRemover,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ): void {
        $filters = [
            'codes' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3'],
        ];

        $attributeGroup1 = new AttributeGroup();
        $attributeGroup2 = new AttributeGroup();
        $attributeGroup3 = new AttributeGroup();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $attributeGroupRepository->findBy(['code' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3']])
            ->willReturn([$attributeGroup1, $attributeGroup2, $attributeGroup3]);

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('deleted_attribute_groups', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('skipped_attribute_groups', 0)->shouldBeCalled();

        $attributeGroupRemover->remove($attributeGroup1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attribute_groups')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $attributeGroupRemover->remove($attributeGroup2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attribute_groups')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $attributeGroupRemover->remove($attributeGroup3)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attribute_groups')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $this->execute();
    }

    public function it_catches_attribute_group_removal_exceptions(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        RemoverInterface $attributeGroupRemover,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        TranslatorInterface $translator,
    ): void {
        $filters = ['codes' => ['attribute_group_1']];

        $attributeGroup1 = new AttributeGroup();
        $attributeGroup1->setCode('attribute_group_1');

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $attributeGroupRepository->findBy(['code' => ['attribute_group_1']])->willReturn([$attributeGroup1]);

        $stepExecution->setTotalItems(1)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('deleted_attribute_groups', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('skipped_attribute_groups', 0)->shouldBeCalled();

        $attributeGroupRemover->remove($attributeGroup1)->willThrow(AttributeGroupOtherCannotBeRemoved::create());
        $translator->trans('pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed', [])
            ->willReturn('pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed');

        $stepExecution->addWarning('pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed', [], new DataInvalidItem([
            'code' => 'attribute_group_1'
        ]))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_attribute_groups')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $this->execute();
    }
}
