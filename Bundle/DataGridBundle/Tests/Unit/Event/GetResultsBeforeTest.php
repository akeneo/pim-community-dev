<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Event;

use Doctrine\ORM\Query;
use Oro\Bundle\DataGridBundle\Event\GetResultsBefore;


class GetResultsBeforeTest extends \PHPUnit_Framework_TestCase
{
    public function testEventCreation()
    {
        $query = new Query(
            $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock()
        );

        $event = new GetResultsBefore($query);
        $this->assertSame($query, $event->getQuery());
    }
}
