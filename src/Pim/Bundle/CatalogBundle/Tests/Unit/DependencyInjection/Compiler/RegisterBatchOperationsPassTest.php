<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterBatchOperationsPass;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterBatchOperationsPassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->target = new RegisterBatchOperationsPass();
    }

    public function testInstanceOfCompilerPassInterface()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->target);
    }

    public function testProcessTaggedBatchOperationService()
    {
        $container = new ContainerBuilder();
        $container
            ->register('pim_catalog.batch_operation.foo')
            ->addTag('pim_catalog.batch_operation', array('alias' => 'foo'));
        $container
            ->register('pim_catalog.batch_operation.bar')
            ->addTag('pim_catalog.batch_operation', array('alias' => 'bar'));
        $container->register('unrelated_service');
        $container
            ->register('pim_catalog.batch_operation.operator');

        $this->target->process($container);

        $calls = $container
            ->getDefinition('pim_catalog.batch_operation.operator')
            ->getMethodCalls();

        $this->assertHasMethodCall($calls, 0, 'registerBatchOperation', 'foo', 'pim_catalog.batch_operation.foo');
        $this->assertHasMethodCall($calls, 1, 'registerBatchOperation', 'bar', 'pim_catalog.batch_operation.bar');
    }

    public function assertHasMethodCall($calls, $position, $method, $alias, $id)
    {
        $this->assertEquals($method, $calls[$position][0]);
        $this->assertEquals($alias, $calls[$position][1][0]);
        $this->assertAttributeEquals($id, 'id', $calls[$position][1][1]);
    }
}
