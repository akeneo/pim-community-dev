<?php

namespace Specification\Akeneo\Pim\Structure\Component\Attribute\Job;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Attribute\Job\DeleteAttributesTasklet;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeleteAttributesTaskletSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        BulkRemoverInterface $attributeRemover,
        EntityManagerClearerInterface $cacheClearer,
        AttributeIsAFamilyVariantAxisInterface $attributeIsAFamilyVariantAxis,
        ChannelRepositoryInterface $channelRepository,
        Connection $dbConnection,
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $attributeRemover,
            $cacheClearer,
            $attributeIsAFamilyVariantAxis,
            $channelRepository,
            $dbConnection,
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
        AttributeIsAFamilyVariantAxisInterface $attributeIsAFamilyVariantAxis,
        ChannelRepositoryInterface $channelRepository,
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
        $attribute1->setCode('attribute_1');
        $attribute1->setType(AttributeTypes::TEXT);

        $attribute2 = new Attribute();
        $attribute2->setCode('attribute_2');
        $attribute2->setType(AttributeTypes::TEXT);

        $attribute3 = new Attribute();
        $attribute3->setCode('attribute_3');
        $attribute3->setType(AttributeTypes::TEXT);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $attributeRepository->findByCodes(['attribute_1', 'attribute_2', 'attribute_3'])
            ->willReturn([$attribute1, $attribute2, $attribute3]);

        $attributeIsAFamilyVariantAxis->execute(Argument::any())->willReturn(false);

        $channelRepository->findAll()->willReturn([]);

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

//    function it_does_not_deletes_identifier_attributes(
//        AttributeRepositoryInterface $attributeRepository,
//        BulkRemoverInterface $attributeRemover,
//        EntityManagerClearerInterface $cacheClearer,
//        AttributeIsAFamilyVariantAxisInterface $attributeIsAFamilyVariantAxis,
//        ChannelRepositoryInterface $channelRepository,
//        StepExecution $stepExecution,
//        JobParameters $jobParameters,
//    ) {
//        $this->setStepExecution($stepExecution);
//        $filters = [
//            'field' => 'id',
//            'operator' => 'IN',
//            'values' => ['attribute_1', 'attribute_2'],
//        ];
//
//        $attribute1 = new Attribute();
//        $attribute1->setType(AttributeTypes::IDENTIFIER);
//
//        $attribute2 = new Attribute();
//        $attribute2->setType(AttributeTypes::TEXT);
//
//        $stepExecution->getJobParameters()->willReturn($jobParameters);
//        $jobParameters->get('filters')->willReturn($filters);
//
//        $attributeRepository->findByCodes(['attribute_1', 'attribute_2'])
//            ->willReturn([$attribute1, $attribute2]);
//
//        $attributeIsAFamilyVariantAxis->execute(Argument::any())->willReturn(false);
//
//        $channelRepository->findAll()->willReturn([]);
//
//        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
//        $stepExecution->addSummaryInfo('deleted_attributes', 0)->shouldBeCalled();
//
//        $stepExecution->addWarning(
//            'flash.attribute.identifier_not_removable',
//            [],
//            new DataInvalidItem($attribute1),
//        )->shouldBeCalled();
//        $stepExecution->addSummaryInfo('skipped_attributes', 1)->shouldBeCalled();
//
//        $attributeRemover->removeAll([$attribute2])->shouldBeCalled();
//        $stepExecution->incrementSummaryInfo('deleted_attributes', 1)->shouldBeCalled();
//        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
//
//        $cacheClearer->clear()->shouldBeCalledTimes(1);
//
//        $this->execute();
//    }
}
