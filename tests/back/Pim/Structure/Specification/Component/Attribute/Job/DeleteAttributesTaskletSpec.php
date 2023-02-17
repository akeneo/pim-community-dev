<?php

namespace Specification\Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Pim\Structure\Component\Attribute\Job\DeleteAttributesTasklet;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use PhpSpec\ObjectBehavior;

class DeleteAttributesTaskletSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        BulkRemoverInterface $attributeRemover,
        EntityManagerClearerInterface $cacheClearer,
    )
    {
        $this->beConstructedWith(
            $attributeRepository,
            $attributeRemover,
            $cacheClearer,
            2,
        );
    }

    function it_is_a_tasklet()
    {
        $this->shouldHaveType(DeleteAttributesTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_throws_an_exception_if_step_execution_is_not_set()
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

    function it_deletes_attributes(
        AttributeRepositoryInterface $attributeRepository,
        BulkRemoverInterface $attributeRemover,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
    ) {
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

        $attributeRemover->removeAll([$attribute1, $attribute2])->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attributes', 2)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();

        $attributeRemover->removeAll([$attribute3])->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_attributes', 1)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $this->execute();
    }
}
