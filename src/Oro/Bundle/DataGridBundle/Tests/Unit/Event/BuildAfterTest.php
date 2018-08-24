<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class BuildAfterTest extends \PHPUnit_Framework_TestCase
{
    public function testEventCreation()
    {
        $grid = $this->getMockForAbstractClass(DatagridInterface::class);

        $event = new BuildAfter($grid);
        $this->assertSame($grid, $event->getDatagrid());
    }
}
