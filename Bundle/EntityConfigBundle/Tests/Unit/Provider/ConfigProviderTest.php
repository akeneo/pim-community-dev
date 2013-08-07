<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\DemoEntity;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var Config
     */
    protected $entityConfig;

    /**
     * @var Config
     */
    protected $fieldConfig;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var PropertyConfigContainer
     */
    protected $configContainer;

    protected function setUp()
    {
        $this->entityConfig = new Config(new EntityConfigId(DemoEntity::ENTITY_NAME, 'test'));

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->any())->method('getConfig')->will($this->returnValue($this->entityConfig));
        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));

        $this->configContainer = new PropertyConfigContainer('test', array());
        $this->configProvider  = new ConfigProvider($this->configManager, $this->configContainer);
    }

    public function testConfig()
    {
        $this->assertEquals(true, $this->configProvider->hasConfig(DemoEntity::ENTITY_NAME));
        $this->assertEquals($this->entityConfig, $this->configProvider->getConfig(DemoEntity::ENTITY_NAME));

        $this->assertEquals('test', $this->configProvider->getScope());

        $this->assertEquals($this->configContainer, $this->configProvider->getPropertyConfig());
    }

    public function testCreateConfig()
    {
        $this->configProvider->createConfig(
            new EntityConfigId(DemoEntity::ENTITY_NAME, 'test'), array('first' => 'test')
        );

        $this->configProvider->flush();
    }

    public function testGetClassName()
    {
        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName(DemoEntity::ENTITY_NAME));

        $className  = DemoEntity::ENTITY_NAME;
        $demoEntity = new $className();
        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName($demoEntity));

        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName(array($demoEntity)));

        $this->setExpectedException('Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');
        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName(array()));
    }
}
