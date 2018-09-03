<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Event;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class BuildBeforeTest extends \PHPUnit_Framework_TestCase
{
    const TEST_STRING = 'testString';

    public function testEventCreation()
    {
        $grid = $this->getMockForAbstractClass(DatagridInterface::class);
        $config = DatagridConfiguration::create([]);

        $event = new BuildBefore($grid, $config);
        $this->assertSame($grid, $event->getDatagrid());
        $this->assertSame($config, $event->getConfig());

        // test config passed as link
        $event->getConfig()->offsetSet(self::TEST_STRING, self::TEST_STRING . 'value');
        $this->assertEquals(self::TEST_STRING . 'value', $config->offsetGet(self::TEST_STRING));
    }
}
