<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;

class EntityConfigContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PropertyConfigContainer */
    private $container;

    private $config = array();

    protected function setUp()
    {
        /** @var Yaml $config */
        $this->config = Yaml::parse(__DIR__ . '/../Fixture/entity_config.yml');
        $this->config = reset($this->config['oro_entity_config']);

        $scope = key($this->config);

        $this->container = new PropertyConfigContainer($scope, $this->config);
    }

    public function testContainer()
    {
        $this->assertEquals(key($this->config), $this->container->getScope());

        $this->assertEquals($this->config['field']['items'], $this->container->getItems(PropertyConfigContainer::TYPE_FIELD));

        $this->assertEquals($this->config, $this->container->getConfig());

        $this->assertTrue($this->container->hasForm());
        $this->assertTrue($this->container->hasForm(PropertyConfigContainer::TYPE_FIELD));

        $this->assertEquals(
            $this->config['field']['form'],
            $this->container->getFormConfig(PropertyConfigContainer::TYPE_FIELD)
        );

        $this->assertEquals(
            $this->config['entity']['grid_action'],
            $this->container->getGridActions()
        );

        $this->assertEquals(
            $this->config['entity']['layout_action'],
            $this->container->getLayoutActions()
        );

        $this->assertEquals(
            $this->config['field']['grid_action'],
            $this->container->getGridActions(PropertyConfigContainer::TYPE_FIELD)
        );

        $this->assertEquals(
            $this->config['field']['layout_action'],
            $this->container->getLayoutActions(PropertyConfigContainer::TYPE_FIELD)
        );

        $this->assertEquals(
            $this->config['entity']['form']['block_config'],
            $this->container->getFormBlockConfig()
        );

        $this->assertEquals(
            array('enabled' => true),
            $this->container->getDefaultValues()
        );

        $this->assertEquals(
            array('enabled' => true, 'is_searchable' => false, 'is_filtrableble' => false),
            $this->container->getDefaultValues(PropertyConfigContainer::TYPE_FIELD)
        );
    }
}
