<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Query;

use Oro\Bundle\SearchBundle\Query\Parser;
use Oro\Bundle\SearchBundle\Query\Query;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testGetQueryFromString()
    {
        $parser = new Parser(
            array(
                'Oro\Bundle\DataBundle\Entity\Product' => array(
                    'alias' => 'test',
                    'fields' => array(
                        array(
                            'name' => 'name',
                            'target_type' => 'string',
                        ),
                        array(
                            'name' => 'description',
                            'target_type' => 'string',
                            'target_fields' => array('description')
                        )
                    )
                )
            )
        );

        $query = $parser->getQueryFromString(
            'from (product, category)
            where description !~ "description" or decimal price < 10'
        );
        $from = $query->getFrom();
        $searchCondition = $query->getOptions();

        $this->assertEquals('product', $from[0]);
        $this->assertEquals('category', $from[1]);

        $this->assertEquals('description', $searchCondition[0]['fieldName']);
        $this->assertEquals(Query::OPERATOR_NOT_CONTAINS, $searchCondition[0]['condition']);
        $this->assertEquals('description', $searchCondition[0]['fieldValue']);
        $this->assertEquals(Query::TYPE_TEXT, $searchCondition[0]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchCondition[0]['type']);

        $this->assertEquals('price', $searchCondition[1]['fieldName']);
        $this->assertEquals(Query::OPERATOR_LESS_THAN, $searchCondition[1]['condition']);
        $this->assertEquals(10, $searchCondition[1]['fieldValue']);
        $this->assertEquals(Query::TYPE_DECIMAL, $searchCondition[1]['fieldType']);
        $this->assertEquals(Query::KEYWORD_OR, $searchCondition[1]['type']);

        $query = $parser->getQueryFromString(
            'description ~ description order_by integer count desc '
        );
        $from = $query->getFrom();
        $this->assertEquals('*', $from[0]);
        $this->assertEquals(Query::TYPE_INTEGER, $query->getOrderType());

        $query = $parser->getQueryFromString(
            'from product where decimal price > 10'
        );
        $from = $query->getFrom();
        $this->assertEquals('product', $from[0]);

        $this->setExpectedException('InvalidArgumentException');
        $parser->getQueryFromString(
            'decimal price ~ 10'
        );
    }
}
