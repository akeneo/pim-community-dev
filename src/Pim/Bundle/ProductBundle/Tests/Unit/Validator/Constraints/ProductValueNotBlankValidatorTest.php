<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ProductBundle\Validator\Constraints\ProductValueNotBlank;

use Pim\Bundle\ProductBundle\Validator\Constraints\ProductValueNotBlankValidator;

class ProductValueNotBlankValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductValueNotBlankValidator
     */
    protected $validator;

    /**
     * @var ProductValueNotBlank
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->validator = new ProductValueNotBlankValidator();
        $this->constraint = new ProductValueNotBlank();
    }

    /**
     * Assert validation with null
     */
    public function testNullValue()
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->messageNotNull);

        $this->validator->initialize($context);
        $this->validator->validate(null, $this->constraint);
    }

    /**
     * Assert validation with wrong entity
     */
    public function testWithWrongEntity()
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->initialize($context);

        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * Data provider for right simple data
     *
     * @static
     * @return array
     */
    public static function dataProviderWithRightSimpleData()
    {
        return array(
            array('char' => 'a'),
            array('string' => 'test'),
            array('sentence' => 'juste a sentence'),
            array('integer' => 5),
            array('zero' => 0),
            array('float' => 3.4),
            array('zero float' => 0.0),
            array('negative integer' => -2),
            array('negative float' => -5.3),
            array('negative zero' => -0),
            array('negative zero float' => -0.00),
            array('boolean true' => true),
            array('boolean false' => false),
            array('not empty array' => array('A')),
            array('object' => new \stdClass())
        );
    }

    /**
     * Assert validation with simple right data (string, int, etc.)
     *
     * @dataProvider dataProviderWithRightSimpleData
     */
    public function testWithRightSimpleData($return)
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->never())
            ->method('addViolation');

        $productValue = $this->getProductValueMock($return);

        $this->validator->initialize($context);
        $this->validator->validate($productValue, $this->constraint);
    }

    /**
     * Data provider for wrong simple data
     *
     * @static
     * @return array
     */
    public static function dataProviderWithWrongSimpleData()
    {
        return array(
            array('null' => null),
            array('empty string' => ''),
            array('empty array' => array())
        );
    }

    /**
     * Assert validation with simple wrong data (null, empty string, empty array)
     *
     * @dataProvider dataProviderWithWrongSimpleData
     */
    public function testWithWrongSimpleData($return)
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->once())
            ->method('addViolation');

        $productValue = $this->getProductValueMock($return);

        $this->validator->initialize($context);
        $this->validator->validate($productValue, $this->constraint);
    }

    /**
     * Get execution context
     *
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    protected function getExecutionContext()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get a product value mock
     *
     * @param mixed $return
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductValue
     */
    protected function getProductValueMock($return)
    {
        $productValue = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue');

        $productValue
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($return));

        return $productValue;
    }
}
