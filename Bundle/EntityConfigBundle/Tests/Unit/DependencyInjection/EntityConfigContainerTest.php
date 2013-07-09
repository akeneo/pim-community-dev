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

    }

    public function testServiceProxy()
    {
    }

    public function test()
    {

    }
}

//    /**
//     * @return array
//     */
//    public function getEntityItems()
//    {
//        if (isset($this->config['entity']) && isset($this->config['entity']['items'])) {
//            return $this->config['entity']['items'];
//        }
//
//        return array();
//    }
//
//    public function hasEntityForm()
//    {
//        return (boolean) array_filter($this->getEntityItems(), function ($item) {
//            return (isset($item['form']) && isset($item['form']['type']));
//        });
//    }
//
//    /**
//     * @return array
//     */
//    public function getEntityFormBlockConfig()
//    {
//        if (isset($this->config['entity'])
//            && isset($this->config['entity']['form'])
//            && isset($this->config['entity']['form']['block_config'])
//        ) {
//            return $this->config['entity']['form']['block_config'];
//        }
//
//        return null;
//    }
//
//    /**
//     * @return array
//     */
//    public function getEntityGridActions()
//    {
//        if (isset($this->config['entity']) && isset($this->config['entity']['grid_action'])) {
//            return $this->config['entity']['grid_action'];
//        }
//
//        return array();
//    }
//
//    /**
//     * @return array
//     */
//    public function getEntityLayoutActions()
//    {
//        if (isset($this->config['entity']) && isset($this->config['entity']['layout_action'])) {
//            return $this->config['entity']['layout_action'];
//        }
//
//        return array();
//    }

//    /**
//     * @return array
//     */
//    public function getFieldItems()
//    {
//        if (isset($this->config['field']) && isset($this->config['field']['items'])) {
//            return $this->config['field']['items'];
//        }
//
//        return array();
//    }
//
//    public function hasFieldForm()
//    {
//        return (boolean) array_filter($this->getFieldItems(), function ($item) {
//            return (isset($item['form']) && isset($item['form']['type']));
//        });
//    }
//
//    /**
//     * @return array
//     */
//    public function getFieldFormConfig()
//    {
//        if (isset($this->config['field']) && isset($this->config['field']['form'])) {
//            return $this->config['field']['form'];
//        }
//
//        return array();
//    }
//
//    /**
//     * @return array
//     */
//    public function getFieldGridActions()
//    {
//        if (isset($this->config['field']) && isset($this->config['field']['grid_action'])) {
//            return $this->config['field']['grid_action'];
//        }
//
//        return array();
//    }
//
//    /**
//     * @return array
//     */
//    public function getFieldLayoutActions()
//    {
//        if (isset($this->config['field']) && isset($this->config['field']['layout_action'])) {
//            return $this->config['field']['layout_action'];
//        }
//
//        return array();
//    }
//}
