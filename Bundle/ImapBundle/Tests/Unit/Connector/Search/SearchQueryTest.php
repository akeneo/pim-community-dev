<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\Search;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExpr;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryMatch;

class SearchQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valueProviderForInvalidArguments
     * @expectedException \InvalidArgumentException
     */
    public function testValueInvalidArguments($value, $match)
    {
        $query = $this->createSearchQuery();
        $query->value($value, $match);
    }

    /**
     * @dataProvider itemProviderForInvalidArguments
     * @expectedException \InvalidArgumentException
     */
    public function testItemInvalidArguments($name, $value, $match)
    {
        $query = $this->createSearchQuery();
        $query->item($name, $value, $match);
    }

    /**
     * @param SearchQuery $query
     * @param $expectedResult
     *
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($query, $expectedResult)
    {
        $this->assertEquals($expectedResult, $query->isEmpty());
    }

    /**
     * @param SearchQuery $query
     * @param $expectedResult
     *
     * @dataProvider isComplexProvider
     */
    public function testIsComplex($query, $expectedResult)
    {
        $this->assertEquals($expectedResult, $query->isComplex());
    }

    public function valueProviderForInvalidArguments()
    {
        $complexQuery = $this->createSearchQuery();
        $complexQuery->value('product1');
        $complexQuery->value('product2');

        return array(
            'SearchQuery as value + SUBSTRING_MATCH' => array($complexQuery, SearchQueryMatch::SUBSTRING_MATCH),
            'SearchQuery as value + EXACT_MATCH' => array($complexQuery, SearchQueryMatch::EXACT_MATCH),
        );
    }

    public function itemProviderForInvalidArguments()
    {
        $sampleQuery = $this->createSearchQuery();
        $sampleQuery->value('product1');
        $sampleQuery->value('product2');

        return array(
            'SearchQuery as value + SUBSTRING_MATCH' => array(
                'subject',
                $sampleQuery,
                SearchQueryMatch::SUBSTRING_MATCH
            ),
            'SearchQuery as value + EXACT_MATCH' => array(
                'subject',
                $sampleQuery,
                SearchQueryMatch::EXACT_MATCH
            ),
        );
    }

    public function isEmptyProvider()
    {
        $empty = $this->createSearchQuery();
        $emptyWithEmptySubQuery = $this->createSearchQuery();
        $emptyWithEmptySubQuery->value($this->createSearchQuery());
        $nonEmpty = $this->createSearchQuery();
        $nonEmpty->value('val');
        $nonEmptyWithNonEmptySubQuery = $this->createSearchQuery();
        $nonEmptySubQuery = $this->createSearchQuery();
        $nonEmptySubQuery->value('val');
        $nonEmptyWithNonEmptySubQuery->value($nonEmptySubQuery);

        return array(
            "empty" => array($empty, true),
            "emptyWithEmptySubQuery" => array($emptyWithEmptySubQuery, true),
            "nonEmpty" => array($nonEmpty, false),
            "nonEmptyWithNonEmptySubQuery" => array($nonEmptyWithNonEmptySubQuery, false),
        );
    }

    public function isComplexProvider()
    {
        $empty = $this->createSearchQuery();
        $emptyWithEmptySubQuery = $this->createSearchQuery();
        $emptyWithEmptySubQuery->value($this->createSearchQuery());

        $simple = $this->createSearchQuery();
        $simple->value('val');

        $complex = $this->createSearchQuery();
        $complex->value('val1');
        $complex->value('val2');

        return array(
            "empty" => array($empty, false),
            "emptyWithEmptySubQuery" => array($emptyWithEmptySubQuery, false),
            "simple" => array($simple, false),
            "complex" => array($complex, true),
        );
    }

    private function createSearchQuery()
    {
        $searchStringManager = $this->getMock('Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface');
        $searchStringManager
            ->expects($this->any())
            ->method('isAcceptableItem')
            ->will($this->returnValue(true));
        $searchStringManager
            ->expects($this->never())
            ->method('buildSearchString');

        return new SearchQuery($searchStringManager);
    }
}
