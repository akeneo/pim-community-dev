<?php
namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\Search;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExprItem;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExprOperator;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExprValue;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryMatch;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryValueBuilder;
use Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface;

class SearchQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider simpleProvider
     */
    public function testFrom($value, $match)
    {
        $this->simpleFieldTesting('from', $value, $match);
    }

    public function testFromWithClosure()
    {
        $this->simpleFieldTestingWithClosure('from');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testTo($value, $match)
    {
        $this->simpleFieldTesting('to', $value, $match);
    }

    public function testToWithClosure()
    {
        $this->simpleFieldTestingWithClosure('to');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testCc($value, $match)
    {
        $this->simpleFieldTesting('cc', $value, $match);
    }

    public function testCcWithClosure()
    {
        $this->simpleFieldTestingWithClosure('cc');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testBcc($value, $match)
    {
        $this->simpleFieldTesting('bcc', $value, $match);
    }

    public function testBccWithClosure()
    {
        $this->simpleFieldTestingWithClosure('bcc');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testParticipants($value, $match)
    {
        $this->simpleFieldTesting('participants', $value, $match);
    }

    public function testParticipantsWithClosure()
    {
        $this->simpleFieldTestingWithClosure('participants');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testSubject($value, $match)
    {
        $this->simpleFieldTesting('subject', $value, $match);
    }

    public function testSubjectWithClosure()
    {
        $this->simpleFieldTestingWithClosure('subject');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testBody($value, $match)
    {
        $this->simpleFieldTesting('body', $value, $match);
    }

    public function testBodyWithClosure()
    {
        $this->simpleFieldTestingWithClosure('body');
    }

    /**
     * @dataProvider simpleProvider
     */
    public function testAttachment($value, $match)
    {
        $this->simpleFieldTesting('attachment', $value, $match);
    }

    public function testAttachmentWithClosure()
    {
        $this->simpleFieldTestingWithClosure('attachment');
    }

    public function testSent()
    {
        $this->rangeFieldTesting('sent');
    }

    public function testReceived()
    {
        $this->rangeFieldTesting('received');
    }

    public static function simpleProvider()
    {
        return array(
            'default match' => array('product', SearchQueryMatch::DEFAULT_MATCH),
            'substring match' => array('product', SearchQueryMatch::SUBSTRING_MATCH),
            'exact match' => array('product', SearchQueryMatch::EXACT_MATCH),
        );
    }

    private function simpleFieldTesting($name, $value, $match)
    {
        $expr = $this->createSearchQueryBuilder()->$name($value, $match)->get()->getExpression();

        $expected = array(new SearchQueryExprItem($name, $value, $match));

        $this->assertEquals($expected, $expr->getItems());
    }

    private function simpleFieldTestingWithClosure($name)
    {
        /** @var SearchQuery $query */
        $query = $this->createSearchQueryBuilder()
            ->$name(
                function ($builder) {
                    /** @var SearchQueryValueBuilder $builder */
                    $builder
                        ->value('val1')
                        ->value('val2');
                }
            )
            ->get();
        $expr = $query->getExpression();

        $subQuery = $query->newInstance();
        $subQuery->value('val1');
        $subQuery->andOperator();
        $subQuery->value('val2');

        $expected = array(
            new SearchQueryExprItem(
                $name,
                $subQuery->getExpression(),
                SearchQueryMatch::DEFAULT_MATCH
            )
        );

        $this->assertEquals($expected, $expr->getItems());
    }

    private function rangeFieldTesting($name)
    {
        $expr = $this->createSearchQueryBuilder()
            ->$name('val')
            ->get()
            ->getExpression();
        $expected = array(new SearchQueryExprItem($name . ':after', 'val', SearchQueryMatch::DEFAULT_MATCH));
        $this->assertEquals($expected, $expr->getItems());

        $expr = $this->createSearchQueryBuilder()
            ->$name('val', null)
            ->get()
            ->getExpression();
        $expected = array(new SearchQueryExprItem($name . ':after', 'val', SearchQueryMatch::DEFAULT_MATCH));
        $this->assertEquals($expected, $expr->getItems());

        $expr = $this->createSearchQueryBuilder()
            ->$name(null, 'val')
            ->get()
            ->getExpression();
        $expected = array(new SearchQueryExprItem($name . ':before', 'val', SearchQueryMatch::DEFAULT_MATCH));
        $this->assertEquals($expected, $expr->getItems());

        $expr = $this->createSearchQueryBuilder()
            ->$name('val1', 'val2')
            ->get()
            ->getExpression();
        $expected = array(
            new SearchQueryExprItem($name . ':after', 'val1', SearchQueryMatch::DEFAULT_MATCH),
            new SearchQueryExprOperator('AND'),
            new SearchQueryExprItem($name . ':before', 'val2', SearchQueryMatch::DEFAULT_MATCH)
        );
        $this->assertEquals($expected, $expr->getItems());
    }

    /**
     * @return SearchQueryBuilder
     */
    private function createSearchQueryBuilder()
    {
        $searchStringManager = $this->getMock('Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface');
        $searchStringManager
            ->expects($this->any())
            ->method('isAcceptableItem')
            ->will($this->returnValue(true));
        $searchStringManager
            ->expects($this->never())
            ->method('buildSearchString');

        return new SearchQueryBuilder(new SearchQuery($searchStringManager));
    }
}
