<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;
use Pim\Bundle\CatalogBundle\Validator\Constraints\RangeValidator;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new RangeValidator();
        $this->validator->initialize($this->context);
    }

    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\RangeValidator', $this->validator);
    }

    public function testValidPrice()
    {
        $constraint = new Range(
            array(
                'min' => 0,
                'max' => 100,
            )
        );

        $this->context->expects($this->never())
            ->method('addViolation');

        $price = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(50));

        $this->validator->validate($price, $constraint);
    }

    public function testOutOfRangePrice()
    {
        $constraint = new Range(
            array(
                'min' => 0,
                'max' => 100,
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                $constraint->maxMessage,
                array(
                    '{{ value }}' => 150,
                    '{{ limit }}' => 100
                )
            );

        $price = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(150));

        $this->validator->validate($price, $constraint);
    }

    /**
     * @dataProvider getValideDateData
     */
    public function testValidDate($options, $date)
    {
        $constraint = new Range($options);

        $this->context->expects($this->never())
            ->method('addViolation');

        $date = new \DateTime($date);
        $this->validator->validate($date, $constraint);
    }

    /**
     * @dataProvider getOutOfRangeDateData
     */
    public function testOutOfRangeDate($options, $date, $with)
    {
        $constraint = new Range($options);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->{$with['message']}, array('{{ limit }}' => $with['date']));

        $date = new \DateTime($date);
        $this->validator->validate($date, $constraint);
    }

    public static function getValideDateData()
    {
        return array(
            array(array('min' => new \DateTime('2013-06-13'), 'max' => new \DateTime('2014-06-13')), '2013-12-25'),
            array(array('min' => new \DateTime('2013-06-13')), '2013-12-25'),
            array(array('max' => new \DateTime('2014-06-13')), '2013-12-25'),
        );
    }

    public static function getOutOfRangeDateData()
    {
        return array(
            array(
                array(
                    'min' => new \DateTime('2013-06-13'),
                    'max' => new \DateTime('2014-06-13')
                ),
                '2012-12-25',
                array(
                    'message' => 'minDateMessage',
                    'date'    => '2013-06-13'
                )
            ),
            array(
                array(
                    'min' => new \DateTime('2013-06-13'),
                    'max' => new \DateTime('2014-06-13')
                ),
                '2015-12-25',
                array(
                    'message' => 'maxDateMessage',
                    'date'    => '2014-06-13'
                )
            ),
            array(
                array(
                    'min' => new \DateTime('2013-06-13')
                ),
                '2012-12-25',
                array(
                    'message' => 'minDateMessage',
                    'date'    => '2013-06-13'
                )
            ),
            array(
                array(
                    'max' => new \DateTime('2014-06-13')
                ),
                '2015-12-25',
                array(
                    'message' => 'maxDateMessage',
                    'date'    => '2014-06-13'
                )
            ),
        );
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Range(array('min' => 10, 'max' => 20)));
    }

    public function getTenToTwenty()
    {
        return array(
            array(10.00001),
            array(19.99999),
            array('10.00001'),
            array('19.99999'),
            array(10),
            array(20),
            array(10.0),
            array(20.0),
        );
    }

    public function getLessThanTen()
    {
        return array(
            array(9.99999),
            array('9.99999'),
            array(5),
            array(1.0),
        );
    }

    public function getMoreThanTwenty()
    {
        return array(
            array(20.000001),
            array('20.000001'),
            array(21),
            array(30.0),
        );
    }

    /**
     * @dataProvider getTenToTwenty
     */
    public function testValidValuesMin($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Range(array('min' => 10));
        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getTenToTwenty
     */
    public function testValidValuesMax($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Range(array('max' => 20));
        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getTenToTwenty
     */
    public function testValidValuesMinMax($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Range(array('min' => 10, 'max' => 20));
        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getLessThanTen
     */
    public function testInvalidValuesMin($value)
    {
        $constraint = new Range(
            array(
                'min' => 10,
                'minMessage' => 'myMessage',
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                $this->identicalTo(array('{{ value }}' => $value, '{{ limit }}' => 10,))
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getMoreThanTwenty
     */
    public function testInvalidValuesMax($value)
    {
        $constraint = new Range(
            array(
                'max' => 20,
                'maxMessage' => 'myMessage',
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                $this->identicalTo(
                    array(
                        '{{ value }}' => $value,
                        '{{ limit }}' => 20,
                    )
                )
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getMoreThanTwenty
     */
    public function testInvalidValuesCombinedMax($value)
    {
        $constraint = new Range(
            array(
                'min' => 10,
                'max' => 20,
                'minMessage' => 'myMinMessage',
                'maxMessage' => 'myMaxMessage',
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMaxMessage',
                $this->identicalTo(
                    array(
                        '{{ value }}' => $value,
                        '{{ limit }}' => 20,
                    )
                )
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getLessThanTen
     */
    public function testInvalidValuesCombinedMin($value)
    {
        $constraint = new Range(
            array(
                'min' => 10,
                'max' => 20,
                'minMessage' => 'myMinMessage',
                'maxMessage' => 'myMaxMessage',
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMinMessage',
                $this->identicalTo(
                    array(
                        '{{ value }}' => $value,
                        '{{ limit }}' => 10,
                    )
                )
            );

        $this->validator->validate($value, $constraint);
    }

    public function getInvalidValues()
    {
        return array(
            array(9.999999),
            array(20.000001),
            array('9.999999'),
            array('20.000001'),
            array(new \stdClass()),
        );
    }

    public function testMinMessageIsSet()
    {
        $constraint = new Range(
            array(
                'min' => 10,
                'max' => 20,
                'minMessage' => 'myMessage',
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                array(
                    '{{ value }}' => 9,
                    '{{ limit }}' => 10,
                )
            );

        $this->validator->validate(9, $constraint);
    }

    public function testMaxMessageIsSet()
    {
        $constraint = new Range(
            array(
                'min' => 10,
                'max' => 20,
                'maxMessage' => 'myMessage',
            )
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                array(
                    '{{ value }}' => 21,
                    '{{ limit }}' => 20,
                )
            );

        $this->validator->validate(21, $constraint);
    }
}
