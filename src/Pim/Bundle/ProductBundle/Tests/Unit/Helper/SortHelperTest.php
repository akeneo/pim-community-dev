<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Helper;

use Pim\Bundle\ProductBundle\Helper\SortHelper;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SortHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider with data sorted and not sorted
     *
     * @static
     *
     * @return array
     */
    public static function dataProviderSort()
    {
        return array(
            'sort_alphabetically' => array(
                array(1 => 'baz', 2 => 'foo', 3 => 'world', 4 => 'bar', 5 => 'qux', 6 => 'hello'),
                array(4 => 'bar', 1 => 'baz', 2 => 'foo', 6 => 'hello', 5 => 'qux', 3 => 'world')
            ),
            'sort_numerically' => array(
                array('a' => 52, 'b' => 2, 'c' => 14, 'd' => 10, 'e' => 03),
                array('e' => 03, 'b' => 2, 'd' => 10, 'c' => 14, 'a' => 52)
            )
        );
    }

    /**
     * Test related method
     *
     * @param array $values
     * @param array $expectedValues
     *
     * @dataProvider dataProviderSort
     */
    public function testSortByProperty($values, $expectedValues)
    {
        $property = 'label';
        $unsortedValues = array();

        // transform values to objects
        foreach ($values as $key => $value) {
            $obj = new \stdClass();
            $obj->$property = $value;
            $values[$key] = $obj;
        }

        $sortedValues = SortHelper::sortByProperty($values, $property);
        foreach ($expectedValues as $key => $expectedValue) {
            $this->assertEquals($expectedValue, $values[$key]->$property);
        }
    }

    /**
     * Test related method
     *
     * @param array $values
     * @param array $expectedValues
     *
     * @dataProvider dataProviderSort
     */
    public function testSort($values, $expectedValues)
    {
        $sortedValues = SortHelper::sort($values);
        $this->assertEquals($expectedValues, $sortedValues);
    }
}
