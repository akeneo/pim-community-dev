<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator;

use Symfony\Component\Validator\Constraints;
use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConstraintGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->target = new ConstraintGuesser;
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface', $this->target);
    }

    public function testGuessNotBlankConstraint()
    {
        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\NotBlank', $this->target->guessConstraints(
            $this->getAttributeMock(array(
                'required' => true,
            ))
        ));
    }

    public function testGuessNegativeNotAllowedConstraint()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'     => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'negativeAllowed' => false,
        )));

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Min', $constraints);
        $this->assertConstraintsConfiguration('Symfony\Component\Validator\Constraints\Min', $constraints, array(
            'limit' => 0,
        ));
    }

    public function testGuessNotAllowedDecimalsConstraint()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'     => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'decimalsAllowed' => false,
        )));

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Type', $constraints);
        $this->assertConstraintsConfiguration('Symfony\Component\Validator\Constraints\Type', $constraints, array(
            'type' => 'int',
        ));
    }

    public function testGuessRangeConstraints()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType' => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'numberMin'   => 100,
            'numberMax'   => 300,
        )));

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration('Symfony\Component\Validator\Constraints\Range', $constraints, array(
            'min' => 100,
            'max' => 300
        ));
    }

    private function getAttributeMock(array $options)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        foreach ($options as $name => $value) {
            $attribute->expects($this->any())
                ->method(sprintf('get%s', ucfirst($name)))
                ->will($this->returnValue($value));
        }

        return $attribute;
    }

    private function assertContainsInstanceOf($class, $constraints)
    {
        if (!$this->getInstanceOf($class, $constraints)) {
            throw new \Exception(sprintf('Expecting constraints to contain instance of "%s"', $class));
        }
    }

    private function assertConstraintsConfiguration($class, $constraints, array $config)
    {
        $constraint = $this->getInstanceOf($class, $constraints);

        foreach ($config as $name => $value) {
            $this->assertEquals($value, $constraint->$name, sprintf('Expecting property "%s" of constraint "%s" to be "%s", but got "%s"', $name, $class, $value, $constraint->$name));
        }
    }

    private function getInstanceOf($class, $constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof $class) {
                return $constraint;
            }
        }
    }
}
