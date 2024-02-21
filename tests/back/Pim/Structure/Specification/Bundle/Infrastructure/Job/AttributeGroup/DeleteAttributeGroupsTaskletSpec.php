<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup\DeleteAttributeGroupsTasklet;
use Akeneo\Pim\Structure\Component\Exception\AttributeGroupOtherCannotBeRemoved;
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

class DeleteAttributeGroupsTaskletSpec extends ObjectBehavior
{
    public function let(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        RemoverInterface $attributeGroupRemover,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ): void {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobStopper->isStopping($stepExecution)->willReturn(false);
        $this->beConstructedWith(
            $attributeGroupRepository,
            $attributeGroupRemover,
            $cacheClearer,
            $jobRepository,
            $jobStopper,
            3
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
    ): void {
        $filters = ['codes' => ['attribute_group_1']];

        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode('attribute_group_1');

        $jobParameters->get('filters')->willReturn($filters);

        $attributeGroupRepository->findBy(['code' => ['attribute_group_1']])->willReturn([$attributeGroup]);

        $stepExecution->setTotalItems(1)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('deleted_attribute_groups', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('skipped_attribute_groups', 0)->shouldBeCalled();

        $attributeGroupRemover->remove($attributeGroup)->willThrow(AttributeGroupOtherCannotBeRemoved::create());

        $stepExecution->addWarning(
            'pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed',
            [],
            new DataInvalidItem(['code' => 'attribute_group_1'])
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_attribute_groups')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();

        $this->execute();
    }

    public function it_batch_attribute_group_deletion(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        RemoverInterface $attributeGroupRemover,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ): void {
        $filters = ['codes' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3', 'attribute_group_4']];

        $attributeGroup1 = new AttributeGroup();
        $attributeGroup2 = new AttributeGroup();
        $attributeGroup3 = new AttributeGroup();
        $attributeGroup4 = new AttributeGroup();

        $jobParameters->get('filters')->willReturn($filters);

        $attributeGroupRepository->findBy(['code' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3']])
            ->shouldBeCalled()
            ->willReturn([$attributeGroup1, $attributeGroup2, $attributeGroup3]);

        $attributeGroupRepository->findBy(['code' => ['attribute_group_4']])
            ->shouldBeCalled()
            ->willReturn([$attributeGroup4]);

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('deleted_attribute_groups', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('skipped_attribute_groups', 0)->shouldBeCalled();

        $attributeGroupRemover->remove($attributeGroup1)->shouldBeCalled();
        $attributeGroupRemover->remove($attributeGroup2)->shouldBeCalled();
        $attributeGroupRemover->remove($attributeGroup3)->shouldBeCalled();
        $attributeGroupRemover->remove($attributeGroup4)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_attribute_groups')->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attribute_groups')->shouldBeCalledTimes(4);
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(4);

        $this->execute();
    }
}
