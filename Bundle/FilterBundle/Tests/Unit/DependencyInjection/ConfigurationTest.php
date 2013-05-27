<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FilterBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ArrayNode;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedConfiguration = array(
        'twig' => array(
            'layout' => Configuration::DEFAULT_LAYOUT,
            'header' => Configuration::DEFAULT_HEADER
        )
    );

    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $builder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);

        /** @var $rootNode ArrayNode */
        $rootNode = $builder->buildTree();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $rootNode);

        $actualConfiguration = $rootNode->finalize(array());
        $this->assertEquals($this->expectedConfiguration, $actualConfiguration);
    }
}
