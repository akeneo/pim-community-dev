<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

use Oro\Bundle\ImportExportBundle\Reader\FlexibleEntityReader;

class FlexibleEntityReaderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME = 'FooEntityClass';

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleManagerRegistry;

    /**
     * @var FlexibleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleManager;

    /**
     * @var FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleRepository;

    /**
     * @var FlexibleEntityReader
     */
    protected $reader;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->flexibleRepository =
            $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $this->flexibleManager =
            $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlexibleRepository', 'getStorageManager'))
                ->getMock();

        $this->flexibleManager->expects($this->once())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($this->flexibleRepository));

        $this->flexibleManager->expects($this->once())
            ->method('getStorageManager')
            ->will($this->returnValue($this->objectManager));

        $this->flexibleManagerRegistry =
            $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry')
                ->disableOriginalConstructor()
                ->setMethods(array('getManager'))
                ->getMock();

        $this->flexibleManagerRegistry->expects($this->once())
            ->method('getManager')
            ->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($this->flexibleManager));

        $this->reader = new FlexibleEntityReader($this->flexibleManagerRegistry, self::TEST_ENTITY_NAME);
    }

    public function testConstructor()
    {
        $this->assertAttributeEquals($this->flexibleManager, 'flexibleManager', $this->reader);
        $this->assertAttributeEquals($this->flexibleRepository, 'flexibleRepository', $this->reader);
        $this->assertAttributeEquals($this->objectManager, 'objectManager', $this->reader);
    }

    public function testGetFields()
    {
        $doctrineFieldNames = array('id', 'email');
        $flexibleAttributes = array(
            'first_name' => new Attribute(),
            'last_name' => new Attribute(),
        );
        $expectedFields = array('id', 'email', 'first_name', 'last_name');

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())->method('getFieldNames')
            ->will($this->returnValue($doctrineFieldNames));

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($classMetadata));

        $this->flexibleRepository->expects($this->once())
            ->method('getCodeToAttributes')
            ->with(array())
            ->will($this->returnValue($flexibleAttributes));

        $this->assertEquals($expectedFields, $this->reader->getFields());
        $this->assertEquals($expectedFields, $this->reader->getFields()); // Test lazy load
    }
}
