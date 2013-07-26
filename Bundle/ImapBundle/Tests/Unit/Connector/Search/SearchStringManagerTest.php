<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\Search;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryMatch;
use Oro\Bundle\ImapBundle\Connector\Search\SearchStringManager;

class SearchStringManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var SearchStringManager */
    private $searchStringManager;

    /** @var SearchQuery */
    private $query;

    protected function setUp()
    {
        $this->searchStringManager = new SearchStringManager();
        $this->query = $this->createSearchQuery();
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($value, $match, $expectedQuery)
    {
        $this->query->value($value, $match);
        $this->assertEquals($expectedQuery, $this->query->convertToSearchString());
    }

    /**
     * @dataProvider itemProvider
     */
    public function testItem($name, $value, $match, $expectedQuery)
    {
        $this->query->item($name, $value, $match);
        $this->assertEquals($expectedQuery, $this->query->convertToSearchString());
    }

    public function testOrOperatorWithTwoOperands()
    {
        // val1 OR val2
        $this->query->item('subject', 'val1');
        $this->query->orOperator();
        $this->query->item('subject', 'val2');
        $this->assertEquals(
            'OR SUBJECT val1 SUBJECT val2',
            $this->query->convertToSearchString()
        );
    }

    public function testOrOperatorWithNotBeforeFirstOperand()
    {
        // NOT val1 OR val2
        $this->query->notOperator();
        $this->query->item('subject', 'val1');
        $this->query->orOperator();
        $this->query->item('subject', 'val2');
        $this->assertEquals(
            'OR NOT SUBJECT val1 SUBJECT val2',
            $this->query->convertToSearchString()
        );
    }

    public function testOrOperatorWithDoubleNotBeforeFirstOperand()
    {
        // NOT NOT val1 OR val2
        $this->query->notOperator();
        $this->query->notOperator();
        $this->query->item('subject', 'val1');
        $this->query->orOperator();
        $this->query->item('subject', 'val2');
        $this->assertEquals(
            'OR NOT NOT SUBJECT val1 SUBJECT val2',
            $this->query->convertToSearchString()
        );
    }

    public function testOrOperatorWithThreeOperands()
    {
        // val1 OR val2 OR val3
        $this->query->item('subject', 'val1');
        $this->query->orOperator();
        $this->query->item('subject', 'val2');
        $this->query->orOperator();
        $this->query->item('subject', 'val3');
        $this->assertEquals(
            'OR SUBJECT val1 OR SUBJECT val2 SUBJECT val3',
            $this->query->convertToSearchString()
        );
    }

    public function testOrOperatorWithSubQuery()
    {
        // (val1 OR val2 OR val3)
        $subQuery = $this->createSearchQuery();
        $subQuery->value('val1');
        $subQuery->orOperator();
        $subQuery->value('val2');
        $subQuery->orOperator();
        $subQuery->value('val3');

        $this->query->item('subject', $subQuery);
        $this->assertEquals(
            '(OR SUBJECT val1 OR SUBJECT val2 SUBJECT val3)',
            $this->query->convertToSearchString()
        );
    }

    public function testOrOperatorWithParenthesis()
    {
        // (val1 OR val2) OR val3
        $this->query->openParenthesis();
        $this->query->item('subject', 'val1');
        $this->query->orOperator();
        $this->query->item('subject', 'val2');
        $this->query->closeParenthesis();
        $this->query->orOperator();
        $this->query->item('subject', 'val3');
        $this->assertEquals(
            'OR (OR SUBJECT val1 SUBJECT val2) SUBJECT val3',
            $this->query->convertToSearchString()
        );
    }

    public function testOrOperatorWithNestedParenthesis()
    {
        // (val1 OR (val2 OR val3)) OR val4
        $this->query->openParenthesis();
        $this->query->item('subject', 'val1');
        $this->query->orOperator();
        $this->query->openParenthesis();
        $this->query->item('subject', 'val2');
        $this->query->orOperator();
        $this->query->item('subject', 'val3');
        $this->query->closeParenthesis();
        $this->query->closeParenthesis();
        $this->query->orOperator();
        $this->query->item('subject', 'val4');
        $this->assertEquals(
            'OR (OR SUBJECT val1 (OR SUBJECT val2 SUBJECT val3)) SUBJECT val4',
            $this->query->convertToSearchString()
        );
    }

    public function testComplexQuery()
    {
        $simpleSubQuery = $this->createSearchQuery();
        $simpleSubQuery->value('val1');

        $complexSubQuery = $this->createSearchQuery();
        $complexSubQuery->value('val2');
        $complexSubQuery->orOperator();
        $complexSubQuery->value('val3');

        $this->query->item('subject', $simpleSubQuery);
        $this->query->andOperator();
        $this->query->item('subject', $complexSubQuery);
        $this->query->orOperator();
        $this->query->openParenthesis();
        $this->query->item('subject', 'product3');
        $this->query->notOperator();
        $this->query->item('subject', 'product4');
        $this->query->closeParenthesis();
        $this->assertEquals(
            'SUBJECT val1 OR (OR SUBJECT val2 SUBJECT val3) (SUBJECT product3 NOT SUBJECT product4)',
            $this->query->convertToSearchString()
        );
    }

    public function valueProvider()
    {
        $sampleQuery = $this->createSearchQuery();
        $sampleQuery->value('product');

        return array(
            'one word + DEFAULT_MATCH' => array('product', SearchQueryMatch::DEFAULT_MATCH, 'product'),
            'one word + SUBSTRING_MATCH' => array('product', SearchQueryMatch::SUBSTRING_MATCH, 'product'),
            'two words + DEFAULT_MATCH' => array('my product', SearchQueryMatch::DEFAULT_MATCH, '"my product"'),
            'two words + SUBSTRING_MATCH' => array('my product', SearchQueryMatch::SUBSTRING_MATCH, '"my product"'),
            'SearchQuery as value + DEFAULT_MATCH' => array($sampleQuery, SearchQueryMatch::DEFAULT_MATCH, 'product'),
        );
    }

    public function itemProvider()
    {
        $sampleQuery = $this->createSearchQuery();
        $sampleQuery->value('product');

        return array(
            'one word + DEFAULT_MATCH' => array(
                'subject',
                'product',
                SearchQueryMatch::DEFAULT_MATCH,
                'SUBJECT product'
            ),
            'one word + SUBSTRING_MATCH' => array(
                'subject',
                'product',
                SearchQueryMatch::SUBSTRING_MATCH,
                'SUBJECT product'
            ),
            'two words + DEFAULT_MATCH' => array(
                'subject',
                'my product',
                SearchQueryMatch::DEFAULT_MATCH,
                'SUBJECT "my product"'
            ),
            'two words + SUBSTRING_MATCH' => array(
                'subject',
                'my product',
                SearchQueryMatch::SUBSTRING_MATCH,
                'SUBJECT "my product"'
            ),
            'SearchQuery as value + DEFAULT_MATCH' => array(
                'subject',
                $sampleQuery,
                SearchQueryMatch::DEFAULT_MATCH,
                'SUBJECT product'
            ),
        );
    }

    private function createSearchQuery()
    {
        return new SearchQuery(new SearchStringManager());
    }
}
