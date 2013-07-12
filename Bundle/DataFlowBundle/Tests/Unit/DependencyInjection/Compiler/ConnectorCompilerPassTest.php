<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler\ConnectorCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test related class
 *
 *
 */
class ConnectorCompilerPassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ConnectorCompilerPass
     */
    protected $compiler;

    /**
     * @var ContainerBuilder
     */
    protected $builder;

    /**
     * Setup
     */
    public function setup()
    {
        $this->compiler = new ConnectorCompilerPass();

        $this->builder = new ContainerBuilder();
        $defRegistry = new Definition('Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler\ConnectorRegistry');
        $definitions = array('oro_dataflow.connectors' => $defRegistry);
        $this->builder->setDefinitions($definitions);
    }

    /**
     * Test related method
     */
    public function testProcess()
    {
        $this->compiler->process($this->builder);
    }
}
