<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\AttributeNormalizer;

class AttributeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflow;

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
     * @var AttributeNormalizer
     */
    protected $attributeNormalizer;

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
            ->setMethods(array('getStepAttributes', 'getName'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeNormalizer = new AttributeNormalizer($this->registry);
    }

    public function testNormalizeWhenNotHasStepAttribute()
    {
        $attributeName = 'foo';
        $attributeValue = 'fooValue';

        $stepAttributes = $this->createStepAttributes(array('bar' => array()));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->never())->method($this->anything());

        $this->assertEquals(
            $attributeValue,
            $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    public function testNormalizeWhenHasStepAttributeWithoutEntity()
    {
        $attributeName = 'foo';
        $attributeValue = 'fooValue';

        $stepAttributes = $this->createStepAttributes(array('foo' => array()));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->never())->method($this->anything());

        $this->assertEquals(
            $attributeValue,
            $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    public function testNormalizeWhenHasStepAttributeWithEntity()
    {
        $attributeName = 'foo';
        $attributeValue = $this->getMock('FooClass');
        $entityIdentifiers = array('id' => 1);
        $entityClass = 'FooClass';

        $stepAttributes = $this->createStepAttributes(array('foo' => array('entity_class' => $entityClass)));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->once())->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('getClassMetadata')
            ->with($entityClass)
            ->will($this->returnValue($this->classMetadata));

        $this->classMetadata->expects($this->once())->method('getIdentifierValues')
            ->with($attributeValue)
            ->will($this->returnValue($entityIdentifiers));

        $this->assertEquals(
            $entityIdentifiers['id'],
            $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    public function testNormalizeWhenHasStepAttributeWithEntityAndMultipleIds()
    {
        $attributeName = 'foo';
        $attributeValue = $this->getMock('FooClass');
        $entityIdentifiers = array('id1' => 'foo', 'id2' => 'bar');
        $entityClass = 'FooClass';

        $stepAttributes = $this->createStepAttributes(array('foo' => array('entity_class' => $entityClass)));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->once())->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('getClassMetadata')
            ->with($entityClass)
            ->will($this->returnValue($this->classMetadata));

        $this->classMetadata->expects($this->once())->method('getIdentifierValues')
            ->with($attributeValue)
            ->will($this->returnValue($entityIdentifiers));

        $this->assertEquals(
            $entityIdentifiers,
            $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Can't access id of entity in workflow data attribute "foo". You must flush entity explicitly or set ID manually if you want to save it to workflow data.
     */
    // @codingStandardsIgnoreEnd
    public function testNormalizeFailsWhenStepAttributeEntityHasNoId()
    {
        $attributeName = 'foo';
        $attributeValue = $this->getMock('FooClass');
        $entityClass = 'FooClass';

        $stepAttributes = $this->createStepAttributes(array('foo' => array('entity_class' => $entityClass)));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->once())->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('getClassMetadata')
            ->with($entityClass)
            ->will($this->returnValue($this->classMetadata));

        $this->classMetadata->expects($this->once())->method('getIdentifierValues')
            ->with($attributeValue)
            ->will($this->returnValue(null));

        $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage "foo" attribute of workflow "test_workflow" refers to "FooClass", but it's not managed entity class
     */
    // @codingStandardsIgnoreEnd
    public function testNormalizeFailsWhenStepAttributeEntityNotManaged()
    {
        $attributeName = 'foo';
        $attributeValue = $this->getMock('FooClass');
        $entityClass = 'FooClass';

        $stepAttributes = $this->createStepAttributes(array('foo' => array('entity_class' => $entityClass)));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->workflow->expects($this->exactly(2))->method('getName')
            ->will($this->returnValue('test_workflow'));

        $this->registry->expects($this->once())->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue(null));

        $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
    }

    public function testDenormalizeWhenNotHasStepAttribute()
    {
        $attributeName = 'foo';
        $attributeValue = 'fooValue';

        $stepAttributes = $this->createStepAttributes(array('bar' => array()));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->never())->method($this->anything());

        $this->assertEquals(
            $attributeValue,
            $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    public function testDenormalizeWhenHasStepAttributeWithoutEntity()
    {
        $attributeName = 'foo';
        $attributeValue = 'fooValue';

        $stepAttributes = $this->createStepAttributes(array('foo' => array()));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->never())->method($this->anything());

        $this->assertEquals(
            $attributeValue,
            $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    public function testDenormalizeWhenHasStepAttributeWithEntity()
    {
        $attributeName = 'foo';
        $attributeValue = 1;
        $entity = $this->getMock('FooClass');
        $entityClass = 'FooClass';

        $stepAttributes = $this->createStepAttributes(array('foo' => array('entity_class' => $entityClass)));

        $this->workflow->expects($this->once())->method('getStepAttributes')
            ->will($this->returnValue($stepAttributes));

        $this->registry->expects($this->once())->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('getReference')
            ->with($entityClass, $attributeValue)
            ->will($this->returnValue($entity));

        $this->assertEquals(
            $entity,
            $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue)
        );
    }

    protected function createStepAttributes(array $attributesOptions)
    {
        $stepAttributes = new ArrayCollection();

        foreach ($attributesOptions as $attributeName => $options) {
            $stepAttribute = new StepAttribute();
            $stepAttribute->setName('foo');
            $stepAttribute->setOptions($options);
            $stepAttributes->set($attributeName, $stepAttribute);
        }

        return $stepAttributes;
    }
}
