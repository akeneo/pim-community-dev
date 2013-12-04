<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Tests\Mocks\ConnectionMock;
use Doctrine\Tests\Mocks\EntityManagerMock;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

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
        $this->entityConfig = new Config(new EntityConfigId(DemoEntity::ENTITY_NAME, 'testScope'));
        $this->fieldConfig  = new Config(
            new FieldConfigId(DemoEntity::ENTITY_NAME, 'testScope', 'testField', 'string')
        );

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->any())->method('getConfig')->will($this->returnValue($this->entityConfig));
        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('persist')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));

        $this->configContainer = new Container();
        $this->configProvider  = new ConfigProvider($this->configManager, $this->configContainer, 'testScope', array());
    }

    public function testConfig()
    {
        $this->assertEquals($this->configManager, $this->configProvider->getConfigManager());
        $this->assertEquals(true, $this->configProvider->hasConfig(DemoEntity::ENTITY_NAME));
        $this->assertEquals($this->entityConfig, $this->configProvider->getConfig(DemoEntity::ENTITY_NAME));
        $this->assertEquals('testScope', $this->configProvider->getScope());

        $entityConfigId = new EntityConfigId(DemoEntity::ENTITY_NAME, 'testScope');
        $fieldConfigId  = new FieldConfigId(DemoEntity::ENTITY_NAME, 'testScope', 'testField', 'string');

        $this->assertEquals($entityConfigId, $this->configProvider->getId(DemoEntity::ENTITY_NAME));
        $this->assertEquals(
            $fieldConfigId,
            $this->configProvider->getId(DemoEntity::ENTITY_NAME, 'testField', 'string')
        );

        $entityConfigIdWithOtherScope = new EntityConfigId(DemoEntity::ENTITY_NAME, 'otherScope');
        $fieldConfigIdWithOtherScope  = new FieldConfigId(DemoEntity::ENTITY_NAME, 'otherScope', 'testField', 'string');

        $this->assertEquals($entityConfigId, $this->configProvider->copyId($entityConfigIdWithOtherScope));
        $this->assertEquals($fieldConfigId, $this->configProvider->copyId($fieldConfigIdWithOtherScope));
        $this->assertEquals($this->entityConfig, $this->configProvider->getConfigById($entityConfigIdWithOtherScope));
    }

    public function testCreateConfig()
    {
        $entityConfig = $this->configProvider->createConfig(
            new EntityConfigId(DemoEntity::ENTITY_NAME, 'testScope'),
            array('first' => 'test')
        );

        $this->entityConfig->set('first', 'test');
        $this->assertEquals($this->entityConfig, $entityConfig);

        $fieldConfig = $this->configProvider->createConfig(
            new FieldConfigId(DemoEntity::ENTITY_NAME, 'testScope', 'testField', 'string'),
            array('first' => 'test')
        );

        $this->fieldConfig->set('first', 'test');
        $this->assertEquals($this->fieldConfig, $fieldConfig);
    }

    public function testPersistFlush()
    {
        $this->configProvider->persist($this->entityConfig);
        $this->configProvider->flush();
    }

    public function testGetClassName()
    {
        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName(DemoEntity::ENTITY_NAME));

        $className  = DemoEntity::ENTITY_NAME;
        $demoEntity = new $className();
        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName($demoEntity));

        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName(array($demoEntity)));

        $classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $classMetadata->expects($this->once())->method('getName')->will($this->returnValue(DemoEntity::ENTITY_NAME));

        $connectionMock       = new ConnectionMock(array(), new \Doctrine\Tests\Mocks\DriverMock());
        $emMock               = EntityManagerMock::create($connectionMock);
        $persistentCollection = new PersistentCollection($emMock, $classMetadata, new ArrayCollection);

        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName($persistentCollection));

        $this->setExpectedException('Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');
        $this->assertEquals(DemoEntity::ENTITY_NAME, $this->configProvider->getClassName(array()));
    }

    public function testClearCache()
    {
        $this->configManager
            ->expects($this->once())
            ->method('clearCache')
            ->with(new EntityConfigId(DemoEntity::ENTITY_NAME, 'testScope'))
            ->will($this->returnValue(true));

        $this->configProvider->clearCache(DemoEntity::ENTITY_NAME);
    }

    public function testGetConfigs()
    {
        $this->configManager
            ->expects($this->exactly(4))
            ->method('getIds')
            ->with('testScope', DemoEntity::ENTITY_NAME)
            ->will($this->returnValue(array($this->entityConfig->getId())));

        $this->assertEquals(
            array($this->entityConfig->getId()),
            $this->configProvider->getIds(DemoEntity::ENTITY_NAME)
        );

        $this->assertEquals(
            array($this->entityConfig),
            $this->configProvider->getConfigs(DemoEntity::ENTITY_NAME)
        );

        $this->assertEquals(
            array(),
            $this->configProvider->filter(
                function (ConfigInterface $config) {
                    return $config->getId()->getScope() == 'wrongScope';
                },
                DemoEntity::ENTITY_NAME
            )
        );

        $entityConfig = new Config(new EntityConfigId(DemoEntity::ENTITY_NAME, 'testScope'));
        $entityConfig->set('key', 'value');
        $this->assertEquals(
            array($entityConfig),
            $this->configProvider->map(
                function (ConfigInterface $config) {
                    return $config->set('key', 'value');
                },
                DemoEntity::ENTITY_NAME
            )
        );
    }
}
