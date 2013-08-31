<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal;
use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimalValidator;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalValidatorTest extends \PHPUnit_Framework_TestCase
{
    public static function getValidData()
    {
        return array(
            array(100),
            array((float) 100),
            array('100'),
        );
    }

    public static function getInvalidData()
    {
        return array(
            array(100.5),
            array((float) 100.5),
            array('100.5'),
        );
    }

    public function setUp()
    {
        $this->target = new NotDecimalValidator();
    }

    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintValidator', $this->target);
    }

    /**
     * @dataProvider getValidData
     */
    public function testValidValue($value)
    {
        $constraint = new NotDecimal();

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->never())
            ->method('addViolation');

        $this->target->initialize($context);
        $this->target->validate($value, $constraint);
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testInvalidValue($value)
    {
        $constraint = new NotDecimal();

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->message);

        $this->target->initialize($context);
        $this->target->validate($value, $constraint);
    }

    public function testValidMetric()
    {
        $value = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Metric');
        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(100));

        $constraint = new NotDecimal();

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->never())
            ->method('addViolation');

        $this->target->initialize($context);
        $this->target->validate($value, $constraint);
    }

    public function testInvalidMetric()
    {
        $value = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Metric');
        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(100.5));

        $constraint = new NotDecimal();

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->message);

        $this->target->initialize($context);
        $this->target->validate($value, $constraint);
    }
}
