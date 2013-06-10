<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ConstraintGuesserTest extends \PHPUnit_Framework_TestCase
{
    protected function getAttributeMock(array $options = array())
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        foreach ($options as $name => $value) {
            $attribute->expects($this->any())
                ->method(sprintf('get%s', ucfirst($name)))
                ->will($this->returnValue($value));
        }

        return $attribute;
    }

    protected function assertContainsInstanceOf($class, $constraints)
    {
        if (!$this->getInstanceOf($class, $constraints)) {
            throw new \Exception(sprintf('Expecting constraints to contain instance of "%s"', $class));
        }
    }

    protected function assertConstraintsConfiguration($class, $constraints, array $config)
    {
        $constraint = $this->getInstanceOf($class, $constraints);

        foreach ($config as $name => $value) {
            $this->assertEquals($value, $constraint->$name, sprintf('Expecting property "%s" of constraint "%s" to be "%s", but got "%s"', $name, $class, $value, $constraint->$name));
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
