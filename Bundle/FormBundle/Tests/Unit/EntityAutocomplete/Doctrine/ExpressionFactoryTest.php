<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\Doctrine;

use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Literal;
use Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\ExpressionFactory;

class ExpressionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExpressionFactory
     */
    protected $expressionFactory;

    protected function setUp()
    {
        $this->expressionFactory = new ExpressionFactory();
    }

    /**
     * @dataProvider multipleConcatDataProvider
     */
    public function testMultipleConcat($parts, $joinLiteral, $expected)
    {
        $this->assertEquals($expected, $this->expressionFactory->multipleConcat($parts, $joinLiteral));
    }

    public function multipleConcatDataProvider()
    {
        return array(
            array(
                array('a', 'b'),
                null,
                new Func(
                    'CONCAT',
                    array('a', 'b')
                )
            ),
            array(
                array('a', 'b', 'c'),
                ' ',
                new Func(
                    'CONCAT',
                    array(
                        new Func(
                            'CONCAT',
                            array(
                                new Func(
                                    'CONCAT',
                                    array(
                                        new Func('CONCAT', array('a', new Literal("' '"))),
                                        'b'
                                    )
                                ),
                                new Literal("' '")
                            )
                        ),
                        'c'
                    )
                )
            ),
            array(
                array('a', 'b', 'c', 'd'),
                null,
                new Func(
                    'CONCAT',
                    array(
                        new Func(
                            'CONCAT',
                            array(
                                new Func('CONCAT', array('a', 'b')),
                                'c'
                            )
                        ),
                        'd'
                    )
                )
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $fields elements count cannot be less then 2
     */
    public function testMultipleConcatFails()
    {
        $this->expressionFactory->multipleConcat(array('a'));
    }
}
