<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SearchBundle\Datagrid\IndexerQuery;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Result;

class IndexerQueryTest extends \PHPUnit_Framework_TestCase
{
    const TEST_VALUE = 'test_value';
    const TEST_COUNT = 42;

    /**
     * @var IndexerQuery
     */
    protected $query;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchIndexer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchQuery;

    /**
     * @var array
     */
    protected $testElements = array(1, 2, 3);

    protected function setUp()
    {
        $this->markTestSkipped("TODO Fix or remove");

        $this->searchIndexer = $this->getMock(
            'Oro\Bundle\SearchBundle\Engine\Indexer',
            array('query'),
            array(),
            '',
            false
        );

        $this->searchQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Query\Query',
            array(
                'setFirstResult',
                'getFirstResult',
                'setMaxResults',
                'getMaxResults',
                'getOrderBy',
                'getOrderDirection',
            ),
            array(),
            '',
            false
        );

        $this->query = new IndexerQuery($this->searchIndexer, $this->searchQuery);
    }

    protected function tearDown()
    {
        unset($this->searchIndexer);
        unset($this->searchQuery);
        unset($this->query);
    }

    /**
     * @return Result
     */
    protected function prepareResult()
    {
        return new Result($this->searchQuery, $this->testElements, self::TEST_COUNT);
    }

    public function testCall()
    {
        $this->searchQuery->expects($this->once())
            ->method('getOrderDirection')
            ->will($this->returnValue(self::TEST_VALUE));

        $this->assertEquals(self::TEST_VALUE, $this->query->getOrderDirection());
    }

    public function testExecute()
    {
        $result = $this->prepareResult();

        $this->searchIndexer->expects($this->once())
            ->method('query')
            ->with($this->searchQuery)
            ->will($this->returnValue($result));

        // two calls to assert lazy load
        $this->assertEquals($this->testElements, $this->query->execute());
        $this->assertEquals($this->testElements, $this->query->execute());
    }

    public function testSetFirstResult()
    {
        $this->searchQuery->expects($this->once())
            ->method('setFirstResult')
            ->with(self::TEST_VALUE);

        $this->query->setFirstResult(self::TEST_VALUE);
    }

    public function testGetFirstResult()
    {
        $this->searchQuery->expects($this->once())
            ->method('getFirstResult')
            ->will($this->returnValue(self::TEST_VALUE));

        $this->assertEquals(self::TEST_VALUE, $this->query->getFirstResult());
    }

    public function testSetMaxResults()
    {
        $this->searchQuery->expects($this->once())
            ->method('setMaxResults')
            ->with(self::TEST_VALUE);

        $this->query->setMaxResults(self::TEST_VALUE);
    }

    public function testGetMaxResults()
    {
        $this->searchQuery->expects($this->once())
            ->method('getMaxResults')
            ->will($this->returnValue(self::TEST_VALUE));

        $this->assertEquals(self::TEST_VALUE, $this->query->getMaxResults());
    }

    public function testGetTotalCount()
    {
        $result = $this->prepareResult();

        $this->searchIndexer->expects($this->once())
            ->method('query')
            ->with($this->searchQuery)
            ->will($this->returnValue($result));

        $this->assertEquals(self::TEST_COUNT, $this->query->getTotalCount());
    }

    public function testGetSortBy()
    {
        $this->searchQuery->expects($this->once())
            ->method('getOrderBy')
            ->will($this->returnValue(self::TEST_VALUE));

        $this->assertEquals(self::TEST_VALUE, $this->query->getSortBy());
    }

    public function testGetSortOrder()
    {
        $this->searchQuery->expects($this->once())
            ->method('getOrderDirection')
            ->will($this->returnValue(self::TEST_VALUE));

        $this->assertEquals(self::TEST_VALUE, $this->query->getSortOrder());
    }
}
