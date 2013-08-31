<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class ConstraintGuesserTest extends \PHPUnit_Framework_TestCase
{
    protected function getAttributeMock(array $options = array())
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        foreach ($options as $name => $value) {
            $attribute->expects($this->any())
                ->method(sprintf('get%s', ucfirst($name)))
                ->will($this->returnValue($value));

            $attribute->expects($this->any())
                ->method(sprintf('is%s', ucfirst($name)))
                ->will($this->returnValue($value));
        }

        return $attribute;
    }

    protected function assertContainsInstanceOf($class, $constraints)
    {
        if (!$this->getInstanceOf($class, $constraints)) {
            $this->fail(
                sprintf(
                    'Expecting constraints to contain instance of "%s", instead got instance(s) of "%s"',
                    $class,
                    implode(
                        '", "',
                        array_map(
                            function ($constraint) {
                                return get_class($constraint);
                            },
                            $constraints
                        )
                    )
                )
            );
        }
    }

    protected function assertConstraintsConfiguration($class, $constraints, array $config)
    {
        $constraint = $this->getInstanceOf($class, $constraints);

        foreach ($config as $name => $value) {
            $this->assertEquals(
                $value,
                $constraint->$name,
                sprintf(
                    'Expecting property "%s" of constraint "%s" to be "%s", but got "%s"',
                    $name,
                    $class,
                    print_r($value, true),
                    print_r($constraint->$name, true)
                )
            );
        }
    }

    protected function getInstanceOf($class, $constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof $class) {
                return $constraint;
            }
        }
    }
}
