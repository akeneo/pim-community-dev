<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal;
use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimalValidator;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public static function getValidData()
    {
        return [
            [100],
            [(float) 100],
            ['100'],
        ];
    }

    /**
     * @return array
     */
    public static function getInvalidData()
    {
        return [
            [100.5],
            [(float) 100.5],
            ['100.5'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new NotDecimalValidator();
    }

    /**
     * Test related method
     */
    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintValidator', $this->target);
    }

    /**
     * @param mixed $value
     *
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
     * @param mixed $value
     *
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

    /**
     * Test related method
     */
    public function testValidMetric()
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\Metric');
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

    /**
     * Test related method
     */
    public function testInvalidMetric()
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\Metric');
        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(100.5));

        $constraint = new NotDecimal();

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('addViolationAt')
            ->with('data', $constraint->message);

        $this->target->initialize($context);
        $this->target->validate($value, $constraint);
    }
}
