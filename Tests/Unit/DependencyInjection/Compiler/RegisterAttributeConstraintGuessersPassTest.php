<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\DependencyInjection\Compiler;

use Pim\Bundle\ProductBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterAttributeConstraintGuessersPassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->target = new RegisterAttributeConstraintGuessersPass;
    }

    public function testInstanceOfCompilerPassInterface()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->target);
    }

    public function testProcessUnknownAttributeConstraintGuesserService()
    {
        $container = $this->getContainerBuilderMock(array(
            'hasDefinition' => array(
                'with'   => 'oro_flexibleentity.validator.attribute_constraint_guesser',
                'return' => false
            ),
            'getDefinition' => array('expectation' => $this->never()),
        ));

        $this->target->process($container);
    }

    public function testProcessTaggedAttributeConstraintGuessersService()
    {
        $container = $this->getContainerBuilderMock(array(
            'hasDefinition' => array(
                'with'   => 'oro_flexibleentity.validator.attribute_constraint_guesser',
                'return' => true
            ),
            'getDefinition' => array(
                'with'   => 'oro_flexibleentity.validator.attribute_constraint_guesser',
                'return' => $this->getDefinitionMock(array(
                    'addMethodCall' => array(
                        'expectation' => $this->exactly(2),
                    )
                ))
            ),
            'findTaggedServiceIds' => array(
                'with'   => 'pim.attribute_constraint_guesser',
                'return' => array(
                    'pim.attribute_constraint_guesser.foo',
                    'pim.attribute_constraint_guesser.bar',
                )
            ),
        ));

        $this->target->process($container);
    }

    public function __call($name, $arguments)
    {
        preg_match('/^get(.*)Mock$/', $name, $matches);
        $classes = array(
            'ContainerBuilder' => 'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Definition'       => 'Symfony\Component\DependencyInjection\Definition',
        );
        $options = $arguments[0];
        $mock = $this->getMock($classes[$matches[1]], array_keys($options));

        foreach ($options as $name => $data) {
            $data = array_merge(array(
                'return'      => null,
                'with'        => null,
                'expectation' => $this->any(),
            ), $data);

            $method = $mock->expects($data['expectation'])
                ->method($name);
            if ($data['with']) {
               $method->with($data['with']);
            }
            $method->will($this->returnValue($data['return']));
        }

        return $mock;
    }
}

