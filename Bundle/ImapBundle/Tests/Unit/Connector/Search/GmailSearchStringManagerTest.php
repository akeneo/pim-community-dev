<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\Search;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExpr;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryMatch;
use Oro\Bundle\ImapBundle\Connector\Search\GmailSearchStringManager;

class GmailSearchStringManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var GmailSearchStringManager */
    private $searchStringManager;

    /** @var SearchQuery */
    private $query;

    protected function setUp()
    {
        $this->searchStringManager = new GmailSearchStringManager();
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

    public function testAndOperator()
    {
        $this->query->andOperator();
        $this->assertEquals('""', $this->query->convertToSearchString());
    }

    public function testOrOperator()
    {
        $this->query->orOperator();
        $this->assertEquals('"OR"', $this->query->convertToSearchString());
    }

    public function testNotOperator()
    {
        $this->query->notOperator();
        $this->assertEquals('"-"', $this->query->convertToSearchString());
    }

    public function testOpenParenthesis()
    {
        $this->query->openParenthesis();
        $this->assertEquals('"("', $this->query->convertToSearchString());
    }

    public function testCloseParenthesis()
    {
        $this->query->closeParenthesis();
        $this->assertEquals('")"', $this->query->convertToSearchString());
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
        $this->query->item('subject', $complexSubQuery);
        $this->query->orOperator();
        $this->query->openParenthesis();
        $this->query->item('subject', 'product3');
        $this->query->notOperator();
        $this->query->item('subject', 'product4');
        $this->query->closeParenthesis();
        $this->assertEquals(
            '"subject:val1 subject:(val2 OR val3) OR (subject:product3 - subject:product4)"',
            $this->query->convertToSearchString()
        );
    }

    public function valueProvider()
    {
        $sampleQuery = $this->createSearchQuery();
        $sampleQuery->value('product');

        return array(
            'one word + DEFAULT_MATCH'
                => array('product', SearchQueryMatch::DEFAULT_MATCH, '"product"'),
            'one word + SUBSTRING_MATCH'
                => array('product', SearchQueryMatch::SUBSTRING_MATCH, '"product"'),
            'one word + EXACT_MATCH'
                => array('product', SearchQueryMatch::EXACT_MATCH, '"+product"'),
            'two words + DEFAULT_MATCH'
                => array('my product', SearchQueryMatch::DEFAULT_MATCH, '"\\"my product\\""'),
            'two words + SUBSTRING_MATCH'
                => array('my product', SearchQueryMatch::SUBSTRING_MATCH, '"\\"my product\\""'),
            'two words + EXACT_MATCH'
                => array('my product', SearchQueryMatch::EXACT_MATCH, '"+\\"my product\\""'),
            'SearchQuery as value + DEFAULT_MATCH'
                => array($sampleQuery, SearchQueryMatch::DEFAULT_MATCH, '"product"'),
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
                '"subject:product"'
            ),
            'one word + SUBSTRING_MATCH' => array(
                'subject',
                'product',
                SearchQueryMatch::SUBSTRING_MATCH,
                '"subject:product"'
            ),
            'one word + EXACT_MATCH' => array(
                'subject',
                'product',
                SearchQueryMatch::EXACT_MATCH,
                '"subject:+product"'
            ),
            'two words + DEFAULT_MATCH' => array(
                'subject',
                'my product',
                SearchQueryMatch::DEFAULT_MATCH,
                '"subject:\\"my product\\""'
            ),
            'two words + SUBSTRING_MATCH' => array(
                'subject',
                'my product',
                SearchQueryMatch::SUBSTRING_MATCH,
                '"subject:\\"my product\\""'
            ),
            'two words + EXACT_MATCH' => array(
                'subject',
                'my product',
                SearchQueryMatch::EXACT_MATCH,
                '"subject:+\\"my product\\""'
            ),
            'SearchQuery as value + DEFAULT_MATCH' => array(
                'subject',
                $sampleQuery,
                SearchQueryMatch::DEFAULT_MATCH,
                '"subject:product"'
            ),
        );
    }

    private function createSearchQuery()
    {
        return new SearchQuery(new GmailSearchStringManager());
    }
}
