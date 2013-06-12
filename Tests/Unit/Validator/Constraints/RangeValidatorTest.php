<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ProductBundle\Validator\Constraints\Range;
use Pim\Bundle\ProductBundle\Validator\Constraints\RangeValidator;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->target = new RangeValidator;
    }

    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\RangeValidator', $this->target);
    }

    public function testValidValue()
    {
        $constraint = new Range(array(
            'min' => 0,
            'max' => 100,
        ));

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $context->expects($this->never())
            ->method('addViolation');

        $price = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(50));

        $this->target->initialize($context);
        $this->target->validate($price, $constraint);
    }

    public function testInvalidValue()
    {
        $constraint = new Range(array(
            'min' => 0,
            'max' => 100,
        ));

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->maxMessage, array(
                '{{ value }}' => 150,
                '{{ limit }}' => 100
            ));

        $price = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(150));

        $this->target->initialize($context);
        $this->target->validate($price, $constraint);
    }
}
