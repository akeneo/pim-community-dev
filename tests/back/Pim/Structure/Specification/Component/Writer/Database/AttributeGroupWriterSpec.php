<?php

namespace Specification\Akeneo\Pim\Structure\Component\Writer\Database;

use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Writer\Database\AttributeGroupWriter;

class AttributeGroupWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        StepExecution $stepExecution,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->beConstructedWith($bulkSaver, $bulkDetacher, $attributeGroupRepository);

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupWriter::class);
    }

    function it_is_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_massively_insert_and_update_objects(
        $bulkSaver,
        $bulkDetacher,
        $stepExecution,
        $attributeGroupRepository,
        AttributeGroupInterface $object1,
        AttributeGroupInterface $object2,
        AttributeGroupInterface $defaultGroup
    ) {
        $attributeGroupRepository->findDefaultAttributeGroup()->willReturn($defaultGroup);

        $bulkSaver->saveAll([$object1, $object2,$defaultGroup]);
        $bulkDetacher->detachAll([$object1, $object2, $defaultGroup]);

        $object1->getId()->willReturn(null);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $defaultGroup->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([$object1, $defaultGroup]);
    }
}
