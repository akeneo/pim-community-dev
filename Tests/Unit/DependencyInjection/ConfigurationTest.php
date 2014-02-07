<?php
namespace Akeneo\Bundle\MeasureBundle\Tests\Unit\DependencyInjection;

use Akeneo\Bundle\MeasureBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Test related class
 *
 *
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertTrue($configuration->getConfigTreeBuilder() instanceof TreeBuilder);
    }
}
