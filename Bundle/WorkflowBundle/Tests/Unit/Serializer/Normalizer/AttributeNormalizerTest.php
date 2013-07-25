<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
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
            ->setMethods(array('getAttributes', 'getName'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->workflow->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test_workflow'));

        $this->attributeNormalizer = new AttributeNormalizer($this->registry);
    }

    /**
     * @dataProvider correctAttributesDataProvider
     * @param mixed $attributeValue
     */
    public function testNormalizeSimple($attributeValue)
    {
        $attributeName = 'test';
        $actual = $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
        $this->assertEquals($attributeValue, $actual);
    }

    /**
     * @dataProvider correctAttributesDataProvider
     * @param mixed $attributeValue
     */
    public function testDenormalizeSimple($attributeValue)
    {
        $attributeName = 'test';
        $actual = $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue);
        $this->assertEquals($attributeValue, $actual);
    }

    public function correctAttributesDataProvider()
    {
        return array(
            array('test'),
            array(new \stdClass()),
            array(array()),
            array(1234),
            array(1234.12),
            array(true),
            array(null)
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Workflow "test_workflow" contains "stdClass", but it's not managed entity class
     */
    public function testNormalizeIncorrectEntity()
    {
        $attributeName = 'test';
        $attributeValue = new \stdClass();

        $attributes = $this->createAttributes(
            array(
                $attributeName => array('entity_class' => 'Foo')
            )
        );
        $this->workflow->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
    }

    public function testNormalizeEntity()
    {
        $attributeName = 'test';
        $attributeValue = new \stdClass();
        $entityClass = get_class($attributeValue);

        $em = $this->getEntityManager($entityClass);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($em));

        $actual = $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
        $expected = array('entity_class' => $entityClass, 'ids' => array('id' => 1));
        $this->assertEquals($expected, $actual);
    }

    public function testNormalizeEntityWithStep()
    {
        $attributeName = 'test';
        $attributeValue = new \stdClass();
        $entityClass = get_class($attributeValue);

        $em = $this->getEntityManager($entityClass);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($em));

        $attributes = $this->createAttributes(
            array(
                $attributeName => array('entity_class' => 'stdClass')
            )
        );
        $this->workflow->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $actual = $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
        $expected = array('entity_class' => $entityClass, 'ids' => array('id' => 1));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Attribute "test" defined to use "Foo" but "stdClass" given.
     */
    public function testNormalizeEntityException()
    {
        $attributeName = 'test';
        $attributeValue = new \stdClass();
        $entityClass = get_class($attributeValue);

        $attributes = $this->createAttributes(
            array(
                $attributeName => array('entity_class' => 'Foo')
            )
        );
        $this->workflow->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $em = $this->getEntityManager($entityClass);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($entityClass)
            ->will($this->returnValue($em));

        $this->attributeNormalizer->normalize($this->workflow, $attributeName, $attributeValue);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Workflow "test_workflow" contains "stdClass", but it's not managed entity class
     */
    public function testDenormalizeIncorrectEntity()
    {
        $attributeName = 'test';
        $attributeValue = array('entity_class' => 'stdClass', 'ids' => array('id' => 1));

        $attributes = $this->createAttributes(
            array(
                $attributeName => array('entity_class' => 'stdClass')
            )
        );
        $this->workflow->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Attribute "test" defined to use "Foo" but "\stdClass" given.
     */
    public function testDenormalizeEntityException()
    {
        $attributeName = 'test';
        $attributeValue = array('entity_class' => '\stdClass', 'ids' => array('id' => 1));

        $attributes = $this->createAttributes(
            array(
                $attributeName => array('entity_class' => 'Foo')
            )
        );
        $this->workflow->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue);
    }

    public function testDenormalizeEntity()
    {
        $attributeName = 'test';
        $attributeValue = array('entity_class' => 'stdClass', 'ids' => array('id' => 1));

        $expected = $this->getMockBuilder('Doctrine\ORM\Proxy\Proxy')
            ->getMock();
        $em = $this->getEntityManager($attributeValue['entity_class']);
        $em->expects($this->once())
            ->method('getReference')
            ->with($attributeValue['entity_class'], $attributeValue['ids'])
            ->will($this->returnValue($expected));

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($attributeValue['entity_class'])
            ->will($this->returnValue($em));

        $actual = $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue);
        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeEntityWithStep()
    {
        $attributeName = 'test';
        $attributeValue = array('entity_class' => 'stdClass', 'ids' => array('id' => 1));

        $expected = $this->getMockBuilder('Doctrine\ORM\Proxy\Proxy')
            ->getMock();
        $em = $this->getEntityManager($attributeValue['entity_class']);
        $em->expects($this->once())
            ->method('getReference')
            ->with($attributeValue['entity_class'], $attributeValue['ids'])
            ->will($this->returnValue($expected));

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($attributeValue['entity_class'])
            ->will($this->returnValue($em));

        $attributes = $this->createAttributes(
            array(
                $attributeName => array('entity_class' => 'stdClass')
            )
        );
        $this->workflow->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $actual = $this->attributeNormalizer->denormalize($this->workflow, $attributeName, $attributeValue);
        $this->assertEquals($expected, $actual);
    }

    protected function createAttributes(array $attributesOptions)
    {
        $stepAttributes = new ArrayCollection();

        foreach ($attributesOptions as $attributeName => $options) {
            $stepAttribute = new Attribute();
            $stepAttribute->setName($attributeName);
            $stepAttribute->setOptions($options);
            $stepAttributes->set($attributeName, $stepAttribute);
        }

        return $stepAttributes;
    }

    protected function getEntityManager($entityClass)
    {
        $ids = array('id' => 1);
        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($entityClass));
        $metadata->expects($this->any())
            ->method('getIdentifierValues')
            ->will($this->returnValue($ids));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->with($entityClass)
            ->will($this->returnValue($metadata));
        return $em;
    }
}
