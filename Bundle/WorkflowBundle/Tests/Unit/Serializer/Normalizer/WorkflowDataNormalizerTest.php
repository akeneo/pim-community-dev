<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\WorkflowDataNormalizer;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class WorkflowDataNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflow;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeNormalizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var WorkflowDataNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->markTestSkipped('Refacotor this test in scope of CRM-313');
        $this->attributeNormalizer =
            $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Serializer\Normalizer\AttributeNormalizer')
                ->setMethods(array('normalize', 'denormalize'))
                ->disableOriginalConstructor()
                ->getMock();

        $this->workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Serializer\WorkflowDataSerializer')
            ->setMethods(array('getWorkflow', 'normalize'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new WorkflowDataNormalizer($this->attributeNormalizer);
        $this->normalizer->setSerializer($this->serializer);
    }

    public function testNormalize()
    {
        $fooEntity = $this->getMock('FooEntityClass');

        $data = new WorkflowData(
            array(
                'foo' => $fooEntity,
                'bar' => 'scalar_value',
                'baz' => array('compound_value'),
                'qux' => null,
            )
        );

        $expectedData = array(
            'foo' => 100,
            'bar' => 'scalar_value',
            'baz' => array('normalized_compound_value'),
            'qux' => null,
        );

        $this->attributeNormalizer->expects($this->at(0))
            ->method('normalize')
            ->with($this->workflow, 'foo', $data->get('foo'))
            ->will($this->returnValue($expectedData['foo']));

        $this->attributeNormalizer->expects($this->at(1))
            ->method('normalize')
            ->with($this->workflow, 'bar', $data->get('bar'))
            ->will($this->returnValue($expectedData['bar']));

        $this->attributeNormalizer->expects($this->at(2))
            ->method('normalize')
            ->with($this->workflow, 'baz', $data->get('baz'))
            ->will($this->returnValue($data->get('baz')));

        $this->attributeNormalizer->expects($this->at(3))
            ->method('normalize')
            ->with($this->workflow, 'qux', $data->get('qux'))
            ->will($this->returnValue($expectedData['qux']));

        $this->serializer->expects($this->any())->method('getWorkflow')
            ->will($this->returnValue($this->workflow));

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($data->get('baz'), null)
            ->will($this->returnValue($expectedData['baz']));

        $this->assertEquals(
            $expectedData,
            $this->normalizer->normalize($data)
        );
    }

    public function testDenormalize()
    {
        $fooEntity = $this->getMock('FooEntityClass');

        $data = array(
            'foo' => 100,
            'bar' => 'scalar_value',
            'baz' => null,
        );

        $expectedData = new WorkflowData(
            array(
                'foo' => $fooEntity,
                'bar' => 'scalar_value',
                'baz' => null,
            )
        );

        $this->attributeNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with($this->workflow, 'foo', $data['foo'])
            ->will($this->returnValue($expectedData->get('foo')));

        $this->attributeNormalizer->expects($this->at(1))
            ->method('denormalize')
            ->with($this->workflow, 'bar', $data['bar'])
            ->will($this->returnValue($expectedData->get('bar')));

        $this->attributeNormalizer->expects($this->at(2))
            ->method('denormalize')
            ->with($this->workflow, 'baz', $data['baz'])
            ->will($this->returnValue($expectedData->get('baz')));

        $this->serializer->expects($this->any())->method('getWorkflow')
            ->will($this->returnValue($this->workflow));

        $this->assertEquals(
            $expectedData,
            $this->normalizer->denormalize($data, 'Oro\Bundle\WorkflowBundle\Model\WorkflowData')
        );
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new WorkflowData()));
        $this->assertTrue(
            $this->normalizer->supportsNormalization(
                $this->getMock('Oro\Bundle\WorkflowBundle\Model\WorkflowData')
            )
        );
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
    }

    public function testSupportsDenormalization()
    {
        $data = array('foo' => 'bar');
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                $data,
                'Oro\Bundle\WorkflowBundle\Model\WorkflowData'
            )
        );
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                $data,
                $this->getMockClass('Oro\Bundle\WorkflowBundle\Model\WorkflowData')
            )
        );
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                $data,
                'stdClass'
            )
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Cannot get Workflow. Serializer must implement Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer
     */
    // @codingStandardsIgnoreEnd
    public function testGetWorkflowFails()
    {
        $data = new WorkflowData(array('foo' => 'bar'));

        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $this->normalizer->setSerializer($serializer);
        $this->normalizer->normalize($data);
    }
}
