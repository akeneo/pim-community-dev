<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;

class EntityConfigContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  EntityConfigContainer */
    private $container;

    private $config = array();

    protected function setUp()
    {
        /** @var Yaml $config */
        $this->config = Yaml::parse(__DIR__ . '/../Fixture/entity_config.yml');
        $this->config = reset($this->config['oro_entity_config']);

        $scope = key($this->config);

        $this->container = new EntityConfigContainer($scope, $this->config);
    }

    public function testContainer()
    {
        $this->assertEquals(key($this->config), $this->container->getScope());

        $this->assertEquals($this->config['field']['items'], $this->container->getFieldItems());

        $this->assertEquals($this->config, $this->container->getConfig());

        $this->assertTrue($this->container->hasEntityForm());
        $this->assertTrue($this->container->hasFieldForm());

        $this->assertEquals(
            $this->config['field']['form'],
            $this->container->getFieldFormConfig()
        );

        $this->assertEquals(
            $this->config['entity']['grid_action'],
            $this->container->getEntityGridActions()
        );

        $this->assertEquals(
            $this->config['entity']['layout_action'],
            $this->container->getEntityLayoutActions()
        );

        $this->assertEquals(
            $this->config['field']['grid_action'],
            $this->container->getFieldGridActions()
        );

        $this->assertEquals(
            $this->config['field']['layout_action'],
            $this->container->getFieldLayoutActions()
        );

        $this->assertEquals(
            $this->config['entity']['form']['block_config'],
            $this->container->getEntityFormBlockConfig()
        );

        $this->assertEquals(
            array('enabled' => true),
            $this->container->getEntityDefaultValues()
        );

        $this->assertEquals(
            array('enabled' => true, 'is_searchable' => false, 'is_filtrableble' => false),
            $this->container->getFieldDefaultValues()
        );
    }
}
