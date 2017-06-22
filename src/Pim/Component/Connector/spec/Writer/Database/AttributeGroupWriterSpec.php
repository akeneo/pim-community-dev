<?php

namespace spec\Pim\Component\Connector\Writer\Database;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Connector\Writer\Database\AttributeGroupWriter;

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
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
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
