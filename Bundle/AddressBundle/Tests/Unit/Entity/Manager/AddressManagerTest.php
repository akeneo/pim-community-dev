<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;
use Oro\Bundle\AddressBundle\Entity\Address;

class AddressManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    /**
     * @var string
     */
    protected $class = 'Oro\Bundle\AddressBundle\Entity\Address';

    /**
     * @var AddressManager
     */
    protected $addressManager;

    /**
     * Setup testing environment
     */
    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

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

        $this->addressManager = new AddressManager($this->class, $this->om);
    }

    /**
     * Test address manager construct
     */
    public function testAddressManagerConstruct()
    {
        $this->assertEquals($this->addressManager->getStorageManager(), $this->om);
        $this->assertInstanceOf($this->class, $this->addressManager->createAddress());
        $this->assertEquals($this->class, $this->addressManager->getClass());
    }

    /**
     * Test querying from repository and address manager getter
     */
    public function testRepository()
    {
        $addressCriteria = array('street' => 'No way');
        $repository = $this->getRepository($addressCriteria);

        $this->om
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($this->addressManager->getClass()))
            ->will($this->returnValue($repository));

        $this->assertEquals($this->addressManager->getRepository(), $repository);
        $this->assertEquals($repository->findOneBy($addressCriteria), new Address());
    }

    /**
     * Test CRUD methods
     */
    public function testQueryMethods()
    {
        $address = new Address();
        $addressCriteria = array('street' => 'No way');

        $this->om
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($address));

        $this->om
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($address));

        $this->om
            ->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($address));

        $this->om
            ->expects($this->exactly(2))
            ->method('flush');

        $this->addressManager->updateAddress($address);
        $this->addressManager->deleteAddress($address);
        $this->addressManager->reloadAddress($address);

        $repository = $this->getRepository($addressCriteria);
        $this->om
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($this->addressManager->getClass()))
            ->will($this->returnValue($repository));
        $this->assertEquals($this->addressManager->findAddressBy($addressCriteria), $address);
    }

    /**
     * Return repository mock
     *
     * @param array $addressCriteria
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepository($addressCriteria = array())
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with($this->equalTo($addressCriteria))
            ->will($this->returnValue(new Address()));

        return $repository;
    }
}
