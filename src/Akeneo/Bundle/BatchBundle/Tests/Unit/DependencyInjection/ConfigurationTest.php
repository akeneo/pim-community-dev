<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Akeneo\Bundle\BatchBundle\DependencyInjection\Configuration;

/**
 * Test related class
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

    public function testDefaultConfiguration()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->process($configuration->getConfigTreeBuilder()->buildTree(), array());
        $this->assertEquals('mailer@bap.com', $config['sender_email']);
    }

    public function testCustomConfiguration()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->process(
            $configuration->getConfigTreeBuilder()->buildTree(),
            array(
                'akeneo_batch' => array(
                    'sender_email' => 'foo@example.com'
                )
            )
        );
        $this->assertEquals('foo@example.com', $config['sender_email']);
    }
}
