<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup\MoveChildAttributesTasklet;
use Akeneo\Pim\Structure\Component\Exception\UserFacingError;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class MoveChildAttributesTaskletSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $attributeUpdater,
        BulkSaverInterface $attributeSaver,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ): void {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->beConstructedWith(
            $attributeRepository,
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
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $attributeUpdater,
        AttributeSaver $attributeSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ) {
        $filters = [
            'codes' => ['attribute_group_1', 'attribute_group_2'],
        ];

        $replacementAttributeGroupCode = 'attribute_group_3';

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);
        $jobParameters->get('replacement_attribute_group_code')->willReturn($replacementAttributeGroupCode);

        $attribute1 = new Attribute();
        $attribute1->setCode('attribute_1');
        $attribute2 = new Attribute();
        $attribute2->setCode('attribute_2');
        $attribute3 = new Attribute();
        $attribute3->setCode('attribute_3');

        $attributeRepository->getAttributesByGroups(['attribute_group_1', 'attribute_group_2'], 3, null)
            ->willReturn([$attribute1, $attribute2, $attribute3]);
        $attributeRepository->getAttributesByGroups(['attribute_group_1', 'attribute_group_2'], 3, 'attribute_3')
            ->willReturn(null);

        $stepExecution->addSummaryInfo('moved_attributes', 0)->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(3);
        $stepExecution->incrementSummaryInfo('moved_attributes')->shouldBeCalledTimes(2);
        $stepExecution->addWarning('an_error', [], Argument::type(DataInvalidItem::class))->shouldBeCalled();
        $isFirstCall = true;
        $attributeUpdater
            ->update(Argument::type(Attribute::class), ['group' => 'attribute_group_3'])
            ->will(function () use (&$isFirstCall) {
                if ($isFirstCall) {
                    $isFirstCall = false;
                    throw new UserFacingError('an_error', []);
                }
            })
            ->shouldBeCalledTimes(3);
        $attributeSaver->saveAll(Argument::type('array'))->shouldBeCalled();

        $this->execute();
    }
}
