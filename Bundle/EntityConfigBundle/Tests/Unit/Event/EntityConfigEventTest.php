<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Event;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Event\NewConfigModelEvent;
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

        $this->configManager->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));

    }

    public function testEvent()
    {
        $event = new NewConfigModelEvent(ConfigManagerTest::DEMO_ENTITY, $this->configManager);

        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $event->getConfigId());
        $this->assertEquals($this->configManager, $event->getConfigManager());
    }
}
