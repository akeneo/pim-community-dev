<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Forms;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;
use Oro\Bundle\EntityConfigBundle\Form\Type\ConfigEntityType;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class ConfigEntityTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new DataBlockExtension())
            ->getFormFactory();

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixture/entity_config.yml'));
        $scope  = key($config['oro_entity_config']);
        $config = reset($config['oro_entity_config']);

        $configProvider = new ConfigProvider($this->configManager, new EntityConfigContainer($scope, $config));
        $entityConfig   = new EntityConfig(ConfigManagerTest::DEMO_ENTITY, 'datagrid');

        $this->configManager->expects($this->any())->method('getConfig')->will($this->returnValue($entityConfig));
        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('getProviders')->will($this->returnValue(array($configProvider)));
    }

    public function testBindValidData()
    {
        $formData = array(
            'datagrid' => array('enabled' => true),
        );

        $type = new ConfigEntityType($this->configManager);
        $form = $this->factory->create($type, null, array('class_name' => ConfigManagerTest::DEMO_ENTITY));
        $form->bind($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
