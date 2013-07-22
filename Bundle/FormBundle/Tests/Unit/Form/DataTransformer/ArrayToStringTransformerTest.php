<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToStringTransformer;

class ArrayToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider transformDataProvider
     * @param string $delimiter
     * @param boolean $filterUinqueValues
     * @param mixed $value
     * @param mixed $expectedValue
     */
    public function testTransform($delimiter, $filterUinqueValues, $value, $expectedValue)
    {
        $transformer = $this->createTestTransfomer($delimiter, $filterUinqueValues);
        $this->assertEquals($expectedValue, $transformer->transform($value));
    }

    public function transformDataProvider()
    {
        return array(
            'default' => array(
                ',',
                false,
                array(1, 2, 3, 4),
                '1,2,3,4',
            ),
            'null' => array(
                ',',
                false,
                null,
                ''
            ),
            'empty array' => array(
                ',',
                false,
                array(),
                ''
            ),
            'trim delimiter' => array(
                ' , ',
                false,
                array(1, 2, 3, 4),
                '1,2,3,4'
            ),
            'filter unique values on' => array(
                ',',
                true,
                array(1, 1, 2, 2, 3, 3, 4, 4),
                '1,2,3,4'
            ),
            'filter unique values off' => array(
                ',',
                false,
                array(1, 1, 2, 2, 3, 3, 4, 4),
                '1,1,2,2,3,3,4,4'
            ),
            'space delimiter' => array(
                ' ',
                false,
                array(1, 2, 3, 4),
                '1 2 3 4'
            ),
        );
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "array", "string" given
     */
    public function testTransformFailsWhenUnexpectedType()
    {
        $transformer = $this->createTestTransfomer();
        $transformer->transform('');
    }

    /**
     * @dataProvider reverseTransformDataProvider
     * @param string $delimiter
     * @param boolean $filterUinqueValues
     * @param mixed $value
     * @param mixed $expectedValue
     */
    public function testReverseTransform($delimiter, $filterUinqueValues, $value, $expectedValue)
    {
        $transformer = $this->createTestTransfomer($delimiter, $filterUinqueValues);
        $this->assertEquals($expectedValue, $transformer->reverseTransform($value));
    }

    public function reverseTransformDataProvider()
    {
        return array(
            'default' => array(
                ',',
                false,
                '1,2,3,4',
                array('1', '2', '3', '4')
            ),
            'null' => array(
                ',',
                false,
                null,
                array()
            ),
            'empty string' => array(
                ',',
                false,
                '',
                array()
            ),
            'trim and empty values' => array(
                ',',
                false,
                ' , 1 , 2 , , 3 , 4,  ',
                array('1', '2', '3', '4')
            ),
            'trim delimiter' => array(
                ' , ',
                false,
                '1,2,3,4',
                array('1', '2', '3', '4')
            ),
            'filter unique values on' => array(
                ',',
                true,
                '1,1,2,2,3,3,4,4',
                array('1', '2', '3', '4')
            ),
            'filter unique values off' => array(
                ',',
                false,
                '1,1,2,2,3,3,4,4',
                array('1', '1', '2', '2', '3', '3', '4', '4')
            ),
            'space delimiter' => array(
                ' ',
                false,
                ' 1  2  3  4 ',
                array('1', '2', '3', '4')
            ),
        );
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string", "array" given
     */
    public function testReverseTransformFailsWhenUnexpectedType()
    {
        $this->createTestTransfomer()->reverseTransform(array());
    }

    /**
     * @param string $delimiter
     * @param boolean $filterUinqueValues
     * @return ArrayToStringTransformer
     */
    private function createTestTransfomer($delimiter = ',', $filterUinqueValues = false)
    {
        return new ArrayToStringTransformer($delimiter, $filterUinqueValues);
    }
}
