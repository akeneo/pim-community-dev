<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit;

use Oro\Bundle\WorkflowBundle\Serializer\EntityReference;

class EntityReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $className = 'TEST';
        $ids = array(1);
        $reference = new EntityReference();
        $reference->setClassName($className);
        $reference->setIds($ids);
        $this->assertEquals($className, $reference->getClassName());
        $this->assertEquals($ids, $reference->getIds());
    }

    public function testInitByEntity()
    {
        $class = new \stdClass();
        $className = get_class($class);
        $ids = array(1);

        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($className));
        $metadata->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue($ids));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with($className)
            ->will($this->returnValue($metadata));

        $reference = new EntityReference();
        $reference->initByEntity($entityManager, $class);
        $this->assertEquals($className, $reference->getClassName());
        $this->assertEquals($ids, $reference->getIds());
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     */
    public function testInitByEntityException()
    {
        $class = new \stdClass();
        $className = get_class($class);

        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($className));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with($className)
            ->will($this->returnValue($metadata));

        $reference = new EntityReference();
        $reference->initByEntity($entityManager, $class);
    }
}
