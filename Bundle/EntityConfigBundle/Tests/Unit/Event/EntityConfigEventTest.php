<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Event;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityEvent;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class EntityConfigEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));

    }

    public function testEvent()
    {
        $event = new NewEntityEvent(ConfigManagerTest::DEMO_ENTITY, $this->configManager);

        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $event->getClassName());
        $this->assertEquals($this->configManager, $event->getConfigManager());
    }
}
