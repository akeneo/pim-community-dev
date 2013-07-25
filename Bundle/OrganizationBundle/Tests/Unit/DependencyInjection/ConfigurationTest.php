<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\OrganizationBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $config = new Configuration();
        $builder = $config->getConfigTreeBuilder();

        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);

        $root = $builder->buildTree();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $root);
        $this->assertEquals('oro_organization', $root->getName());
    }
}
