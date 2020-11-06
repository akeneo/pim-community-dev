<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestCase;

class BuildAfterTest extends TestCase
{
    public function testEventCreation()
    {
        $grid = $this->getMockForAbstractClass(DatagridInterface::class);

        $event = new BuildAfter($grid);
        $this->assertSame($grid, $event->getDatagrid());
    }
}
