<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var EntityConfig
     */
    protected $entityConfig;

    /**
     * @var FieldConfig
     */
    protected $fieldConfig;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var EntityConfigContainer
     */
    protected $configContainer;

    protected function setUp()
    {
        $this->entityConfig = new EntityConfig(ConfigManagerTest::DEMO_ENTITY, 'testScope');
        $this->fieldConfig  = new FieldConfig(ConfigManagerTest::DEMO_ENTITY, 'testField', 'string', 'testScope');

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->any())->method('getConfig')->will($this->returnValue($this->entityConfig));
        $this->configManager->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));

        $this->configContainer = new EntityConfigContainer('test', array());
        $this->configProvider  = new ConfigProvider($this->configManager, $this->configContainer);
    }

    public function testConfig()
    {
        $this->assertEquals(true, $this->configProvider->isConfigurable(ConfigManagerTest::DEMO_ENTITY));
        $this->assertEquals($this->entityConfig, $this->configProvider->getConfig(ConfigManagerTest::DEMO_ENTITY));

        $this->entityConfig->addField($this->fieldConfig);

        $this->assertEquals(true, $this->configProvider->hasFieldConfig(ConfigManagerTest::DEMO_ENTITY, 'testField'));
        $this->assertEquals($this->fieldConfig, $this->configProvider->getFieldConfig(
            ConfigManagerTest::DEMO_ENTITY,
            'testField'
        ));

        $this->assertEquals('test', $this->configProvider->getScope());

        $this->assertEquals($this->configContainer, $this->configProvider->getConfigContainer());
    }

    public function testCreateConfig()
    {
        $this->configProvider->createEntityConfig(
            ConfigManagerTest::DEMO_ENTITY, array('first' => 'test'), true
        );

        $this->configProvider->createFieldConfig(
            ConfigManagerTest::DEMO_ENTITY, 'testField', 'string', array('first' => 'test'), true
        );

        $this->configProvider->flush();
    }

    public function testGetClassName()
    {
        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $this->configProvider->getClassName(ConfigManagerTest::DEMO_ENTITY));

        $className  = ConfigManagerTest::DEMO_ENTITY;
        $demoEntity = new $className();
        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $this->configProvider->getClassName($demoEntity));

        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $this->configProvider->getClassName(array($demoEntity)));

        $this->setExpectedException('Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');
        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $this->configProvider->getClassName(array()));
    }
}
