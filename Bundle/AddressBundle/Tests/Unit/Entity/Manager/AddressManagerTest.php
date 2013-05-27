<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\Common\Persistence\ObjectManager;

class AddressManagerTest extends \PHPUnit_Framework_TestCase
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
     * @var AddressManager
     */
    protected $addressManager;

    /**
     * Setup testing environment
     */
    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->fm = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = 'Oro\Bundle\AddressBundle\Entity\Address';

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

        $this->addressManager = new AddressManager($this->class, $this->om, $this->fm);
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
     * Test magic call methods for existing flexible manager methods
     */
    public function testCall()
    {
        $this->fm
            ->expects($this->once())
            ->method('getFlexibleName')
            ->will($this->returnValue(1));

        $this->assertEquals($this->addressManager->getFlexibleName(), 1);
    }

    /**
     * Testing exception on not existing method in address manager
     *
     * @expectedException \RuntimeException
     */
    public function testCallException()
    {
        $this->addressManager->NotExistingMethod();
    }

    public function testListQuery()
    {
        $limit = 1;
        $offset = 10;
        $paginator = $this->getMockBuilder('Doctrine\ORM\Tools\Pagination\Paginator')
            ->disableOriginalConstructor()
            ->getMock();

        $repo = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findByWithAttributesQB')
            ->with(array(), null, array('id' => 'ASC'), $limit, $offset)
            ->will($this->returnValue($paginator));

        $this->fm->expects($this->once())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($repo));


        $this->assertSame($paginator, $this->addressManager->getListQuery($limit, $offset));
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
