<?php

namespace Oro\Bundle\EntityBundle\Tests\Unit\ORM\Query;

use Oro\Bundle\EntityBundle\ORM\Query\FilterCollection;

class FilterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var FilterCollection
     */
    protected $filterCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    protected function setUp()
    {
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder('\Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())->method('getConfiguration')->will($this->returnValue($this->config));
        $this->filter = $this->getMockBuilder('\Doctrine\ORM\Query\Filter\SQLFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterCollection = new FilterCollection($entityManager);
    }

    public function testFilters()
    {
        $this->assertTrue($this->filterCollection->isClean());

        $this->filterCollection->addFilter('test', $this->filter);
        $this->assertContainsOnly($this->filter, $this->filterCollection->getDisabledFilters());
        $this->assertEmpty($this->filterCollection->getEnabledFilters());

        $this->enable();
        $this->assertFalse($this->filterCollection->isClean());

        $this->disable();
    }

    protected function enable()
    {
        $this->filterCollection->enable("test");
        $this->assertContainsOnly($this->filter, $this->filterCollection->getEnabledFilters());
        $this->assertEmpty($this->filterCollection->getDisabledFilters());
    }

    protected function disable()
    {
        $this->filterCollection->disable("test");
        $this->assertContainsOnly($this->filter, $this->filterCollection->getDisabledFilters());
        $this->assertEmpty($this->filterCollection->getEnabledFilters());
    }

    public function testSetFiltersStateDirty()
    {
        $this->filterCollection->setFiltersStateDirty();
        $this->assertFalse($this->filterCollection->isClean());
    }

    public function testStandardFilters()
    {
        $this->config->expects($this->at(0))->method('getFilterClassName')->with('test')
            ->will($this->returnValue(get_class($this->filter)));
        $this->filterCollection->enable('test');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStandardFiltersNotFound()
    {
        $this->config->expects($this->at(0))->method('getFilterClassName')->with('test')
            ->will($this->returnValue(null));
        $this->filterCollection->enable('test');
    }
}
