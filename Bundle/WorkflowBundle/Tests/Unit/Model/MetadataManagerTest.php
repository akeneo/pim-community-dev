<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Persistence\Proxy;

use Oro\Bundle\WorkflowBundle\Model\MetadataManager;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\__CG__\ItemStubProxy;

class MetadataManagerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_IDENTIFIER = 42;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var MetadataManager
     */
    protected $metadataManager;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->metadataManager = new MetadataManager($this->registry);
    }

    protected function tearDown()
    {
        unset($this->registry);
        unset($this->metadataManager);
    }

    /**
     * @param string $entity
     * @param string $expectedClass
     * @dataProvider getEntityClassDataProvider
     */
    public function testGetEntityClass($entity, $expectedClass)
    {
        $this->assertEquals($expectedClass, $this->metadataManager->getEntityClass($entity));
    }

    /**
     * @return array
     */
    public function getEntityClassDataProvider()
    {
        return array(
            'existing entity' => array(
                'entity'        => new ItemStub(),
                'expectedClass' => 'Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub',
            ),
            'entity proxy' => array(
                'entity'        => new ItemStubProxy(),
                'expectedClass' => 'ItemStubProxy',
            ),
        );
    }

    /**
     * @param object $entity
     * @param string $class
     * @dataProvider getEntityIdentifierDataProvider
     */
    public function testGetEntityIdentifier($entity, $class)
    {
        $entityManager = $this->getMockBuilder('Doctrine\Orm\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassMetadata'))
            ->getMock();

        if ($entity instanceof Proxy) {
            $entityManager->expects($this->never())
                ->method('getClassMetadata');
        } else {
            $classMetadata = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $classMetadata->expects($this->once())
                ->method('getIdentifierValues')
                ->with($entity)
                ->will($this->returnValue(self::TEST_IDENTIFIER));

            $entityManager->expects($this->once())
                ->method('getClassMetadata')
                ->with($class)
                ->will($this->returnValue($classMetadata));
        }

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($class)
            ->will($this->returnValue($entityManager));

        $this->assertEquals(self::TEST_IDENTIFIER, $this->metadataManager->getEntityIdentifier($entity));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException
     * @expectedExceptionMessage Entity class "ItemStubProxy" is not manageable.
     */
    public function testGetEntityIdentifierNotManageableEntity()
    {
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue(null));

        $this->metadataManager->getEntityIdentifier(new ItemStubProxy());
    }

    /**
     * @return array
     */
    public function getEntityIdentifierDataProvider()
    {
        return array(
            'existing entity' => array(
                'entity' => new ItemStub(),
                'class'  => 'Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub',
            ),
            'entity proxy' => array(
                'entity' => new ItemStubProxy(self::TEST_IDENTIFIER),
                'class'  => 'ItemStubProxy',
            ),
        );
    }

    /**
     * @param object $entity
     * @param boolean $manageable
     * @dataProvider isManageableEntityDataProvider
     */
    public function testIsManageableEntity($entity, $manageable)
    {
        if ($manageable) {
            $entityManager = $this->getMockBuilder('Doctrine\Orm\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();
            $this->registry->expects($this->once())
                ->method('getManagerForClass')
                ->with($this->metadataManager->getEntityClass($entity))
                ->will($this->returnValue($entityManager));
        } else {
            $this->registry->expects($this->once())
                ->method('getManagerForClass')
                ->with($this->metadataManager->getEntityClass($entity))
                ->will($this->returnValue(null));
        }

        $this->assertEquals($manageable, $this->metadataManager->isManageableEntity($entity));
    }

    /**
     * @return array
     */
    public function isManageableEntityDataProvider()
    {
        return array(
            'manageable entity' => array(
                'entity'     => new ItemStubProxy(),
                'manageable' => true
            ),
            'not manageable entity' => array(
                'entity'     => new \DateTime('now'),
                'manageable' => false
            ),
        );
    }
}
