<?php

namespace Oro\Bundle\EntityBundle\Tests\Unit\ORM;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

class OroEntityManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterCollection;

    /**
     * @var OroEntityManager
     */
    protected $manager;

    protected function setUp()
    {
        $this->filterCollection = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\Query\FilterCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $conn = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $eventManager = $this->getMockBuilder('\Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
        $config = $this->getMockBuilder('\Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $metadataFactory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())->method('getClassMetadataFactoryName')
            ->will($this->returnValue(get_class($metadataFactory)));
        $config->expects($this->any())->method('getProxyDir')
            ->will($this->returnValue('test'));
        $config->expects($this->any())->method('getProxyNamespace')
            ->will($this->returnValue('test'));
        $config->expects($this->any())->method('getMetadataDriverImpl')->will($this->returnValue(true));
        $conn->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));

        $this->manager = OroEntityManager::create($conn, $config, $eventManager);
    }

    public function testSetGetFiltersCollection()
    {
        $this->assertInstanceOf('Oro\Bundle\EntityBundle\ORM\Query\FilterCollection', $this->manager->getFilters());
        $this->manager->setFilterCollection($this->filterCollection);
        $this->assertAttributeEquals($this->filterCollection, 'filterCollection', $this->manager);
        $this->assertEquals($this->filterCollection, $this->manager->getFilters());
        $this->assertTrue($this->manager->hasFilters());

        $this->filterCollection->expects($this->at(0))->method('isClean')
            ->will($this->returnValue(true));
        $this->filterCollection->expects($this->at(1))->method('isClean')
            ->will($this->returnValue(false));
        $this->assertTrue($this->manager->isFiltersStateClean());
        $this->assertFalse($this->manager->isFiltersStateClean());
    }
}
