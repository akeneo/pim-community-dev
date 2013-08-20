<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\EntityAttributeNormalizer;

class EntityAttributeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $testWorkflowName = 'test_workflow';

    /**
     * @var string
     */
    protected $testAttributeName = 'test_attribute';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflow;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMetadata;

    /**
     * @var EntityAttributeNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getClassMetadata', 'getReference'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->setMethods(array('getIdentifierValues'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->setMethods(array('getAttribute', 'getName'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->workflow->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($this->testWorkflowName));

        $this->attribute = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->setMethods(array('getType', 'getOption', 'getName'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($this->testAttributeName));

        $this->normalizer = new EntityAttributeNormalizer($this->registry);
    }

    /**
     * @dataProvider normalizeDirectionDataProvider
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\SerializeWorkflowDataException
     * @expectedExceptionMessage Attribute "test_attribute" of workflow "test_workflow" must exist
     */
    public function testNormalizeAndDenormalizeExceptionNoAttribute($direction)
    {
        $attributeValue = $this->getEntityMock();

        $this->workflow->expects($this->once())->method('getAttribute')->with($this->testAttributeName);

        if ($direction == 'normalization') {
            $this->normalizer->normalize($this->workflow, $this->testAttributeName, $attributeValue);
        } else {
            $this->normalizer->denormalize($this->workflow, $this->testAttributeName, $attributeValue);
        }
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\SerializeWorkflowDataException
     * @expectedExceptionMessage Attribute "test_attribute" of workflow "test_workflow" must exist
     */
    public function testNormalizeExceptionNotInstanceofAttributeClassOption()
    {
        $attributeValue = $this->getEntityMock();

        $this->workflow->expects($this->once())->method('getAttribute')->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        $fooClass = $this->getMockClass('FooClass');

        $this->attribute->expects($this->once())->method('getOption')->with('class')
            ->will($this->returnValue($fooClass));

        $this->setExpectedException(
            'Oro\Bundle\WorkflowBundle\Exception\SerializeWorkflowDataException',
            sprintf(
                'Attribute "test_attribute" of workflow "test_workflow" must be an instance of "%s", but "%s" given',
                $fooClass,
                get_class($attributeValue)
            )
        );
        $this->normalizer->normalize($this->workflow, $this->testAttributeName, $attributeValue);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\SerializeWorkflowDataException
     * @expectedExceptionMessage Attribute "test_attribute" of workflow "test_workflow" must exist
     */
    public function testNormalizeExceptionNoEntityManager()
    {
        $attributeValue = $this->getEntityMock();

        $this->workflow->expects($this->once())->method('getAttribute')->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->exactly(2))->method('getOption')->with('class')
            ->will($this->returnValue(get_class($attributeValue)));

        $this->registry->expects($this->once())->method('getManagerForClass')->with(get_class($attributeValue));

        $this->setExpectedException(
            'Oro\Bundle\WorkflowBundle\Exception\SerializeWorkflowDataException',
            sprintf(
                'Attribute "%s" of workflow "%s" contains object of "%s", but it\'s not managed entity class',
                $this->testAttributeName,
                $this->testWorkflowName,
                get_class($attributeValue)
            )
        );
        $this->normalizer->normalize($this->workflow, $this->testAttributeName, $attributeValue);
    }

    public function testNormalizeEntity()
    {
        $attributeValue = $this->getEntityMock();

        $this->workflow->expects($this->once())->method('getAttribute')->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->exactly(3))->method('getOption')->with('class')
            ->will($this->returnValue(get_class($attributeValue)));

        $this->registry->expects($this->once())->method('getManagerForClass')->with(get_class($attributeValue))
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('getClassMetadata')
            ->with(get_class($attributeValue))
            ->will($this->returnValue($this->classMetadata));

        $expectedId = array('id' => 123);
        $this->classMetadata->expects($this->once())->method('getIdentifierValues')
            ->with($attributeValue)
            ->will($this->returnValue($expectedId));

        $this->assertEquals(
            $expectedId,
            $this->normalizer->normalize($this->workflow, $this->testAttributeName, $attributeValue)
        );
    }

    /**
     * @dataProvider normalizeDirectionDataProvider
     */
    public function testNormalizeAndDenormalizeNull($direction)
    {
        $attributeValue = null;

        $this->workflow->expects($this->once())->method('getAttribute')->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        if ($direction == 'normalization') {
            $this->assertNull(
                $this->normalizer->normalize($this->workflow, $this->testAttributeName, $attributeValue)
            );
        } else {
            $this->assertNull(
                $this->normalizer->denormalize($this->workflow, $this->testAttributeName, $attributeValue)
            );
        }
    }

    public function testDenormalizeEntity()
    {
        $expectedValue = $this->getMock('EntityReference');
        $attributeValue = array('id' => 123);

        $this->workflow->expects($this->once())->method('getAttribute')->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->exactly(2))->method('getOption')->with('class')
            ->will($this->returnValue(get_class($expectedValue)));

        $this->registry->expects($this->once())->method('getManagerForClass')->with(get_class($expectedValue))
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('getReference')
            ->with(get_class($expectedValue), $attributeValue)
            ->will($this->returnValue($expectedValue));

        $this->assertEquals(
            $expectedValue,
            $this->normalizer->denormalize($this->workflow, $this->testAttributeName, $attributeValue)
        );
    }

    /**
     * @dataProvider normalizeDirectionDataProvider
     */
    public function testSupportsNormalization($direction)
    {
        $attributeValue = 'bar';

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue('entity'));

        $this->workflow->expects($this->once())
            ->method('getAttribute')
            ->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        $method = 'supports' . ucfirst($direction);
        $this->assertTrue($this->normalizer->$method($this->workflow, $this->testAttributeName, $attributeValue));
    }

    /**
     * @dataProvider normalizeDirectionDataProvider
     */
    public function testNotSupportsNormalizationWhenNoAttribute($direction)
    {
        $attributeValue = 'bar';

        $this->workflow->expects($this->once())->method('getAttribute');

        $method = 'supports' . ucfirst($direction);
        $this->assertFalse($this->normalizer->$method($this->workflow, $this->testAttributeName, $attributeValue));
    }

    /**
     * @dataProvider normalizeDirectionDataProvider
     */
    public function testNotSupportsNormalizationWhenNotEntityType($direction)
    {
        $attributeValue = 'bar';

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue('object'));

        $this->workflow->expects($this->once())
            ->method('getAttribute')
            ->with($this->testAttributeName)
            ->will($this->returnValue($this->attribute));

        $method = 'supports' . ucfirst($direction);
        $this->assertFalse($this->normalizer->$method($this->workflow, $this->testAttributeName, $attributeValue));
    }

    public function normalizeDirectionDataProvider()
    {
        return array(
            array('normalization'),
            array('denormalization'),
        );
    }

    protected function getEntityMock()
    {
        return $this->getMock('FooEntity');
    }
}
