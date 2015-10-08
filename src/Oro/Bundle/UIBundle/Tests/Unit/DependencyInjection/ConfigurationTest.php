<?php
namespace Oro\Bundle\UIBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\UIBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $bundleConfiguration = new Configuration();
        $this->assertTrue($bundleConfiguration->getConfigTreeBuilder() instanceof TreeBuilder);
    }
}
