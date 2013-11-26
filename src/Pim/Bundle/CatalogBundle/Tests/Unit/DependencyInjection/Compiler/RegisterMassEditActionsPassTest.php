<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterMassEditActionsPass;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterMassEditActionsPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new RegisterMassEditActionsPass();
    }

    /**
     * Test related method
     */
    public function testInstanceOfCompilerPassInterface()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->target);
    }

    /**
     * Test related method
     */
    public function testProcessTaggedMassEditActionService()
    {
        $container = new ContainerBuilder();
        $container
            ->register('pim_catalog.mass_edit_action.foo')
            ->addTag('pim_catalog.mass_edit_action', array('alias' => 'foo'));
        $container
            ->register('pim_catalog.mass_edit_action.bar')
            ->addTag('pim_catalog.mass_edit_action', array('alias' => 'bar'));
        $container->register('unrelated_service');
        $container
            ->register('pim_catalog.mass_edit_action.operator');

        $this->target->process($container);

        $calls = $container
            ->getDefinition('pim_catalog.mass_edit_action.operator')
            ->getMethodCalls();

        $this->assertHasMethodCall($calls, 0, 'registerMassEditAction', 'foo', 'pim_catalog.mass_edit_action.foo');
        $this->assertHasMethodCall($calls, 1, 'registerMassEditAction', 'bar', 'pim_catalog.mass_edit_action.bar');
    }

    /**
     * @param array   $calls
     * @param integer $position
     * @param string  $method
     * @param string  $alias
     * @param string  $id
     */
    public function assertHasMethodCall($calls, $position, $method, $alias, $id)
    {
        $this->assertEquals($method, $calls[$position][0]);
        $this->assertEquals($alias, $calls[$position][1][0]);
        $this->assertAttributeEquals($id, 'id', $calls[$position][1][1]);
    }
}
