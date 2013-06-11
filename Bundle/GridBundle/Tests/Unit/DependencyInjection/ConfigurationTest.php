<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;

use Oro\Bundle\GridBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedConfiguration = array(
        Configuration::TRANSLATION_DOMAIN_NODE => Configuration::DEFAULT_TRANSLATION_DOMAIN
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
