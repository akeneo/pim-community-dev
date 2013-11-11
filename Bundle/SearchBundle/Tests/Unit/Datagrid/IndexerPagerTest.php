<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SearchBundle\Datagrid\IndexerPager;
use Oro\Bundle\SearchBundle\Query\Query;

class SearchPagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexerPager
     */
    protected $pager;

    protected function setUp()
    {
        $this->markTestSkipped("TODO fix test or remove if not needed");
        $this->pager = new IndexerPager();
    }

    protected function tearDown()
    {
        unset($this->pager);
    }

    public function testSetQuery()
    {
        $indexerQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery',
            array(),
            array(),
            '',
            false
        );
        $this->pager->setQuery($indexerQuery);
        $this->assertAttributeEquals($indexerQuery, 'query', $this->pager);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Indexer query must be set
     */
    public function testInit()
    {
        $this->pager->init();
    }

    public function testGetNbResults()
    {
        $totalCount = 123;

        $indexerQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery',
            array('getTotalCount'),
            array(),
            '',
            false
        );
        $indexerQuery->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue($totalCount));

        $this->pager->setQuery($indexerQuery);
        $this->assertEquals($totalCount, $this->pager->getNbResults());
    }

    /**
     * Data provider for testSetMaxPerPage
     *
     * @return array
     */
    public function maxPerPageDataProvider()
    {
        return array(
            'fixed' => array(
                '$maxPerPage'  => 12,
                '$maxResults'  => 12,
                '$firstResult' => 0,
            ),
            'infinite' => array(
                '$maxPerPage'  => 0,
                '$maxResults'  => Query::INFINITY,
                '$firstResult' => 0,
            ),
        );
    }

    /**
     * @param int $maxPerPage
     * @param int $maxResults
     * @param int $firstResult
     *
     * @dataProvider maxPerPageDataProvider
     */
    public function testSetGetMaxPerPage($maxPerPage, $maxResults, $firstResult)
    {
        $indexerQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery',
            array('setMaxResults', 'setFirstResult'),
            array(),
            '',
            false
        );
        $indexerQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($maxResults);
        $indexerQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($firstResult);

        $this->pager->setQuery($indexerQuery);

        $this->pager->setMaxPerPage($maxPerPage);
        $this->assertAttributeEquals($maxPerPage, 'maxPerPage', $this->pager);
        $this->assertEquals($maxPerPage, $this->pager->getMaxPerPage());
    }

    public function testSetGetPage()
    {
        $page = 2;
        $firstResult = 10;

        $indexerQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery',
            array('setFirstResult'),
            array(),
            '',
            false
        );
        $indexerQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($firstResult);

        $this->pager->setQuery($indexerQuery);

        $this->pager->setPage($page);
        $this->assertAttributeEquals($page, 'page', $this->pager);
        $this->assertEquals($page, $this->pager->getPage());
    }

    public function testGetFirstPreviousNextLastPage()
    {
        $page         = 2;
        $maxPerPage   = 20;
        $totalCount   = 123;
        $firstPage    = 1;
        $lastPage     = 7;
        $previousPage = 1;
        $nextPage     = 3;

        $indexerQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery',
            array('getTotalCount', 'setMaxResults', 'setFirstResult'),
            array(),
            '',
            false
        );
        $indexerQuery->expects($this->any())
            ->method('getTotalCount')
            ->will($this->returnValue($totalCount));

        $this->pager->setQuery($indexerQuery);
        $this->pager->setPage($page);
        $this->pager->setMaxPerPage($maxPerPage);

        $this->assertEquals($firstPage, $this->pager->getFirstPage());
        $this->assertEquals($lastPage, $this->pager->getLastPage());
        $this->assertEquals($previousPage, $this->pager->getPreviousPage());
        $this->assertEquals($nextPage, $this->pager->getNextPage());
    }

    /**
     * Data provider for testHaveToPaginate
     *
     * @return array
     */
    public function haveToPaginateDataProvider()
    {
        return array(
            'no_data' => array(
                '$expected'   => false,
                '$page'       => 1,
                '$maxPerPage' => 0,
                '$totalCount' => 0
            ),
            'one_page' => array(
                '$expected'   => false,
                '$page'       => 1,
                '$maxPerPage' => 10,
                '$totalCount' => 5
            ),
            'several_page' => array(
                '$expected'   => true,
                '$page'       => 1,
                '$maxPerPage' => 10,
                '$totalCount' => 15
            ),
        );
    }

    /**
     * @param boolean $expected
     * @param int     $page
     * @param int     $maxPerPage
     * @param int     $totalCount
     *
     * @dataProvider haveToPaginateDataProvider
     */
    public function testHaveToPaginate($expected, $page, $maxPerPage, $totalCount)
    {
        $indexerQuery = $this->getMock(
            'Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery',
            array('getTotalCount', 'setMaxResults', 'setFirstResult'),
            array(),
            '',
            false
        );
        $indexerQuery->expects($this->any())
            ->method('getTotalCount')
            ->will($this->returnValue($totalCount));

        $this->pager->setQuery($indexerQuery);
        $this->pager->setPage($page);
        $this->pager->setMaxPerPage($maxPerPage);

        $this->assertEquals($expected, $this->pager->haveToPaginate());
    }

    public function testGetLinks()
    {
        $this->assertEmpty($this->pager->getLinks());
    }
}
