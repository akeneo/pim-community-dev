<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\Manager\AddressTypeManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\Common\Persistence\ObjectManager;

class AddressTypeManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FlexibleManager
     */
    protected $fm;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var AddressTypeManager
     */
    protected $addressTypeManager;

    /**
     * Setup testing environment
     */
    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->fm = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = 'Oro\Bundle\AddressBundle\Entity\AddressType';

        $classMetaData = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $classMetaData
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue($this->class));

        $this->om
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($this->class))
            ->will($this->returnValue($classMetaData));

        $this->addressTypeManager = new AddressTypeManager($this->class, $this->om, $this->fm);
    }

    /**
     * Test address type manager construct
     */
    public function testAddressTypeManagerConstruct()
    {
        $this->assertEquals($this->addressTypeManager->getStorageManager(), $this->om);
        $this->assertInstanceOf($this->class, $this->addressTypeManager->createAddressType());
        $this->assertEquals($this->class, $this->addressTypeManager->getClass());
    }

    /**
     * Test querying from repository and address manager getter
     */
    public function testRepository()
    {
        $addressTypeCriteria = array('type' => 'shipping');
        $repository = $this->getRepository($addressTypeCriteria);

        $this->om
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($this->addressTypeManager->getClass()))
            ->will($this->returnValue($repository));

        $this->assertEquals($this->addressTypeManager->getRepository(), $repository);
        $this->assertEquals($repository->findOneBy($addressTypeCriteria), new AddressType());
    }

    /**
     * Test CRUD methods
     */
    public function testQueryMethods()
    {
        $addressType = new AddressType();
        $addressTypeCriteria = array('type' => 'shipping');

        $this->om
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($addressType));

        $this->om
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($addressType));

        $this->om
            ->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($addressType));

        $this->om
            ->expects($this->exactly(2))
            ->method('flush');

        $this->addressTypeManager->updateAddressType($addressType);
        $this->addressTypeManager->deleteAddressType($addressType);
        $this->addressTypeManager->reloadAddressType($addressType);

        $repository = $this->getRepository($addressTypeCriteria);
        $this->om
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($this->addressTypeManager->getClass()))
            ->will($this->returnValue($repository));
        $this->assertEquals($this->addressTypeManager->findAddressTypeBy($addressTypeCriteria), $addressType);
    }

    /**
     * Return repository mock
     *
     * @param array $addressTypeCriteria
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepository($addressTypeCriteria = array())
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with($this->equalTo($addressTypeCriteria))
            ->will($this->returnValue(new AddressType()));

        return $repository;
    }
}
