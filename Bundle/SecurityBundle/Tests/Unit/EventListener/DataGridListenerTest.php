<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Query;

use Oro\Bundle\SecurityBundle\EventListener\DataGridListener;

class DataGridListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testListener()
    {
        $helper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(
            $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $helper->expects($this->once())
            ->method('apply')
            ->with($query);
        $event = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Event\GetResultsBefore')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $listener = new DataGridListener($helper);
        $listener->applyAclToQuery($event);
    }
}
