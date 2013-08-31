<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\DependencyInjection\Compiler;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterAttributeConstraintGuessersPassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->target = new RegisterAttributeConstraintGuessersPass();
    }

    public function testInstanceOfCompilerPassInterface()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->target);
    }

    public function testProcessUnknownAttributeConstraintGuesserService()
    {
        $container = $this->getContainerBuilderMock();

        $this->target->process($container);
    }

    public function testProcessTaggedAttributeConstraintGuessersService()
    {
        $definition = $this->getDefinitionMock();
        $container  = $this->getContainerBuilderMock(
            $definition,
            array('pim.attribute_constraint_guesser.foo', 'pim.attribute_constraint_guesser.bar',)
        );

        $definition->expects($this->exactly(2))
            ->method('addMethodCall')
            ->with('addConstraintGuesser', $this->anything());

        $this->target->process($container);
    }

    private function getContainerBuilderMock($definition = null, array $taggedServices = array())
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->any())
            ->method('hasDefinition')
            ->with('oro_flexibleentity.validator.attribute_constraint_guesser')
            ->will($this->returnValue(null !== $definition));

        if ($definition) {
            $container->expects($this->any())
                ->method('getDefinition')
                ->will($this->returnValue($definition));

            $container->expects($this->any())
                ->method('findTaggedServiceIds')
                ->with('pim.attribute_constraint_guesser')
                ->will($this->returnValue($taggedServices));
        } else {
            $container->expects($this->never())
                ->method('getDefinition');
        }

        return $container;
    }

    private function getDefinitionMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\Definition');
    }
}
