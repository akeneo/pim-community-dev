<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\DependencyInjection;

use Pim\Bundle\BatchBundle\DependencyInjection\Configuration;
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
