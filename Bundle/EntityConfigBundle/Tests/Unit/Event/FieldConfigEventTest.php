<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Event;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Event\NewFieldEvent;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class FieldConfigEventTest extends \PHPUnit_Framework_TestCase
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
        $event = new NewFieldEvent(ConfigManagerTest::DEMO_ENTITY, 'testField', 'string', $this->configManager);

        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $event->getClassName());
        $this->assertEquals($this->configManager, $event->getConfigManager());
        $this->assertEquals('testField', $event->getFieldName());
        $this->assertEquals('string', $event->getFieldType());
    }
}
