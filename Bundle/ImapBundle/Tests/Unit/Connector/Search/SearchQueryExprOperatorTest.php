<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\Search;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExprOperator;

class SearchQueryExprOperatorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $name = 'testName';
        $obj = new SearchQueryExprOperator($name);

        $this->assertEquals($name, $obj->getName());
    }

    public function testSettersAndGetters()
    {
        $obj = new SearchQueryExprOperator('1');

        $name = 'testName';

        $obj->setName($name);

        $this->assertEquals($name, $obj->getName());
    }
}
