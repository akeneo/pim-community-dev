<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;
use Pim\Bundle\CatalogBundle\Validator\Constraints\RangeValidator;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', [], [], '', false);
        $this->validator = new RangeValidator();
        $this->validator->initialize($this->context);
    }

    /**
     * Test related method
     */
    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\RangeValidator', $this->validator);
    }

    /**
     * Test related method
     */
    public function testValidPrice()
    {
        $constraint = new Range(
            [
                'min' => 0,
                'max' => 100,
            ]
        );

        $this->context->expects($this->never())
            ->method('addViolation');

        $price = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(50));

        $this->validator->validate($price, $constraint);
    }

    /**
     * Test related method
     */
    public function testOutOfRangePrice()
    {
        $constraint = new Range(
            [
                'min' => 0,
                'max' => 100,
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with(
                'data',
                $constraint->maxMessage,
                [
                    '{{ value }}' => 150,
                    '{{ limit }}' => 100
                ]
            );

        $price = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(150));

        $this->validator->validate($price, $constraint);
    }

    /**
     * @param array $options
     * @param mixed $date
     *
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
     * @param array $options
     * @param mixed $date
     * @param array $with
     *
     * @dataProvider getOutOfRangeDateData
     */
    public function testOutOfRangeDate($options, $date, $with)
    {
        $constraint = new Range($options);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->{$with['message']}, ['{{ limit }}' => $with['date']]);

        $date = new \DateTime($date);
        $this->validator->validate($date, $constraint);
    }

    /**
     * @return array
     */
    public static function getValideDateData()
    {
        return [
            [['min' => new \DateTime('2013-06-13'), 'max' => new \DateTime('2014-06-13')], '2013-12-25'],
            [['min' => new \DateTime('2013-06-13')], '2013-12-25'],
            [['max' => new \DateTime('2014-06-13')], '2013-12-25'],
        ];
    }

    /**
     * @return array
     */
    public static function getOutOfRangeDateData()
    {
        return [
            [
                [
                    'min' => new \DateTime('2013-06-13'),
                    'max' => new \DateTime('2014-06-13')
                ],
                '2012-12-25',
                [
                    'message' => 'minDateMessage',
                    'date'    => '2013-06-13'
                ]
            ],
            [
                [
                    'min' => new \DateTime('2013-06-13'),
                    'max' => new \DateTime('2014-06-13')
                ],
                '2015-12-25',
                [
                    'message' => 'maxDateMessage',
                    'date'    => '2014-06-13'
                ]
            ],
            [
                [
                    'min' => new \DateTime('2013-06-13')
                ],
                '2012-12-25',
                [
                    'message' => 'minDateMessage',
                    'date'    => '2013-06-13'
                ]
            ],
            [
                [
                    'max' => new \DateTime('2014-06-13')
                ],
                '2015-12-25',
                [
                    'message' => 'maxDateMessage',
                    'date'    => '2014-06-13'
                ]
            ],
        ];
    }

    /**
     * Test related method
     */
    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Range(['min' => 10, 'max' => 20]));
    }

    /**
     * @return array
     */
    public function getTenToTwenty()
    {
        return [
            [10.00001],
            [19.99999],
            ['10.00001'],
            ['19.99999'],
            [10],
            [20],
            [10.0],
            [20.0],
        ];
    }

    /**
     * @return array
     */
    public function getLessThanTen()
    {
        return [
            [9.99999],
            ['9.99999'],
            [5],
            [1.0],
        ];
    }

    /**
     * @return array
     */
    public function getMoreThanTwenty()
    {
        return [
            [20.000001],
            ['20.000001'],
            [21],
            [30.0],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getTenToTwenty
     */
    public function testValidValuesMin($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Range(['min' => 10]);
        $this->validator->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getTenToTwenty
     */
    public function testValidValuesMax($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Range(['max' => 20]);
        $this->validator->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getTenToTwenty
     */
    public function testValidValuesMinMax($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Range(['min' => 10, 'max' => 20]);
        $this->validator->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getLessThanTen
     */
    public function testInvalidValuesMin($value)
    {
        $constraint = new Range(
            [
                'min' => 10,
                'minMessage' => 'myMessage',
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                $this->identicalTo(['{{ value }}' => $value, '{{ limit }}' => 10])
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getMoreThanTwenty
     */
    public function testInvalidValuesMax($value)
    {
        $constraint = new Range(
            [
                'max' => 20,
                'maxMessage' => 'myMessage',
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                $this->identicalTo(
                    [
                        '{{ value }}' => $value,
                        '{{ limit }}' => 20,
                    ]
                )
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getMoreThanTwenty
     */
    public function testInvalidValuesCombinedMax($value)
    {
        $constraint = new Range(
            [
                'min' => 10,
                'max' => 20,
                'minMessage' => 'myMinMessage',
                'maxMessage' => 'myMaxMessage',
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMaxMessage',
                $this->identicalTo(
                    [
                        '{{ value }}' => $value,
                        '{{ limit }}' => 20,
                    ]
                )
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getLessThanTen
     */
    public function testInvalidValuesCombinedMin($value)
    {
        $constraint = new Range(
            [
                'min' => 10,
                'max' => 20,
                'minMessage' => 'myMinMessage',
                'maxMessage' => 'myMaxMessage',
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMinMessage',
                $this->identicalTo(
                    [
                        '{{ value }}' => $value,
                        '{{ limit }}' => 10,
                    ]
                )
            );

        $this->validator->validate($value, $constraint);
    }

    /**
     * @return array
     */
    public function getInvalidValues()
    {
        return [
            [9.999999],
            [20.000001],
            ['9.999999'],
            ['20.000001'],
            [new \stdClass()],
        ];
    }

    /**
     * Test related method
     */
    public function testMinMessageIsSet()
    {
        $constraint = new Range(
            [
                'min' => 10,
                'max' => 20,
                'minMessage' => 'myMessage',
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                [
                    '{{ value }}' => 9,
                    '{{ limit }}' => 10,
                ]
            );

        $this->validator->validate(9, $constraint);
    }

    /**
     * Test related method
     */
    public function testMaxMessageIsSet()
    {
        $constraint = new Range(
            [
                'min' => 10,
                'max' => 20,
                'maxMessage' => 'myMessage',
            ]
        );

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'myMessage',
                [
                    '{{ value }}' => 21,
                    '{{ limit }}' => 20,
                ]
            );

        $this->validator->validate(21, $constraint);
    }
}
