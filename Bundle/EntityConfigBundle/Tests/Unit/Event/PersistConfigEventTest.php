<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Event;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;

class PersistConfigEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));

    }

    public function testEvent()
    {
        $config = new Config(new EntityConfigId('Test/Class', 'test'));
        $event  = new PersistConfigEvent($config, $this->configManager);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($config->getId(), $event->getConfigId());
        $this->assertEquals($this->configManager, $event->getConfigManager());
    }
}
