<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\WorkflowBundle\Serializer\WorkflowItemDataSerializer;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData;

class WorkflowItemSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $delegateSerializer;

    /**
     * @var WorkflowItemDataSerializer
     */
    protected $serializer;

    protected function setUp()
    {
        $this->delegateSerializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $this->serializer = new WorkflowItemDataSerializer($this->delegateSerializer);
    }

    public function testSerialize()
    {
        $data = new WorkflowItemData();

        $expectedResult = '{"foo":"bar"}';
        $this->delegateSerializer->expects($this->once())->method('serialize')
            ->with($data, 'json')
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->serializer->serialize($data));
    }

    public function testDeserialize()
    {
        $data = '{"foo":"bar"}';

        $expectedResult = new WorkflowItemData();
        $expectedResult->foo = 'bar';

        $this->delegateSerializer->expects($this->once())->method('deserialize')
            ->with($data, 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData', 'json')
            ->will($this->returnValue($expectedResult));

        $this->assertEquals(
            $expectedResult,
            $this->serializer->deserialize($data)
        );
    }
}
