<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Event;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;

class PersistConfigEventTest extends \PHPUnit_Framework_TestCase
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
        $config = new EntityConfig('testClass', 'testScope');
        $event  = new PersistConfigEvent($config, $this->configManager);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($this->configManager, $event->getConfigManager());
    }
}
