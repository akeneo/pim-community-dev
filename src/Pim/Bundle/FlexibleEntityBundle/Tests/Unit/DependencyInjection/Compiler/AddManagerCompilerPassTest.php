<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddManagerCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddManagerCompilerPassTest extends \PHPUnit_Framework_TestCase
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
        $this->compiler = new AddManagerCompilerPass();

        $this->builder = new ContainerBuilder();
        $defRegistry = new Definition('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry');
        $definitions = array('pim_flexibleentity.registry' => $defRegistry);
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
