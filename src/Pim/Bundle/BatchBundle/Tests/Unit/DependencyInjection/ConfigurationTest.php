<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Pim\Bundle\BatchBundle\DependencyInjection\Configuration;

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
