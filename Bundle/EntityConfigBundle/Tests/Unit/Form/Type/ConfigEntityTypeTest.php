<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;
use Oro\Bundle\EntityConfigBundle\Form\Type\ConfigEntityType;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;
use Symfony\Component\Form\Tests\FormIntegrationTestCase;

class ConfigEntityTypeTest extends FormIntegrationTestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected function setUp()
    {

        parent::setUp();

        $entityConfig = new EntityConfig(ConfigManagerTest::DEMO_ENTITY, 'test');

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configProvider = new ConfigProvider($this->configManager, new EntityConfigContainer(array('scope' => 'test')));

        $this->configManager->expects($this->any())->method('getConfig')->will($this->returnValue($entityConfig));
        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('getProviders')->will($this->returnValue(array($configProvider)));
    }

    public function testBindValidData()
    {
        $type = new ConfigEntityType($this->configManager);
    }
}
