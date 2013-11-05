<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SearchBundle\Datagrid\IndexerQueryFactory;

class IndexerQueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateQuery()
    {
        $this->markTestSkipped("TODO Fix or remove");
        $query = $this->getMock('Oro\Bundle\SearchBundle\Query\Query', array(), array(), '', false);

        $indexer = $this->getMock('Oro\Bundle\SearchBundle\Engine\Indexer', array('select'), array(), '', false);
        $indexer->expects($this->once())
            ->method('select')
            ->will($this->returnValue($query));

        $indexerQueryFactory = new IndexerQueryFactory($indexer);
        $indexerQuery = $indexerQueryFactory->createQuery();

        $this->assertInstanceOf('Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery', $indexerQuery);
        $this->assertAttributeEquals($indexer, 'indexer', $indexerQuery);
        $this->assertAttributeEquals($query, 'query', $indexerQuery);
    }
}
