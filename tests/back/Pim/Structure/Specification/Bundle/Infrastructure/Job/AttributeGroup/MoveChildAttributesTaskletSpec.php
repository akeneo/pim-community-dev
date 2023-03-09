<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup\MoveChildAttributesTasklet;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class MoveChildAttributesTaskletSpec extends ObjectBehavior
{
    public function let(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        ObjectUpdaterInterface $attributeUpdater,
        SaverInterface $attributeSaver,
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
            $attributeUpdater,
            $attributeSaver,
            $cacheClearer,
            $jobRepository,
            $jobStopper,
            3
        );

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldHaveType(MoveChildAttributesTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_track_processed_items(): void
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    public function it_move_attributes(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        ObjectUpdaterInterface $attributeUpdater,
        AttributeSaver $attributeSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ) {
        $filters = [
            'codes' => ['attribute_group_1', 'attribute_group_2'],
        ];

        $replacementAttributeGroupCode = 'attribute_group_3';

        $attributeGroup1 = new AttributeGroup();
        $attributeGroup1->addAttribute(new Attribute());
        $attributeGroup2 = new AttributeGroup();
        $attributeGroup2->addAttribute(new Attribute());
        $attributeGroup2->addAttribute(new Attribute());
        $attributeGroup3 = new AttributeGroup();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);
        $jobParameters->get('replacement_attribute_group_code')->willReturn($replacementAttributeGroupCode);

        $attributeGroupRepository->findBy(['code' => ['attribute_group_1', 'attribute_group_2']])
            ->willReturn([$attributeGroup1, $attributeGroup2]);

        $stepExecution->incrementSummaryInfo('attributes_to_move', 3)->shouldBeCalled();

        $attributeUpdater->update(Argument::type(Attribute::class), ['group' => 'attribute_group_3'])->shouldBeCalledTimes(3);
        $attributeSaver->saveAll(Argument::type('array'))->shouldBeCalled();

        $this->execute();
    }
}
