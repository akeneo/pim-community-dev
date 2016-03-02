<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Title;

use Oro\Bundle\NavigationBundle\Title\StoredTitle;

class StoredTitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     *
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new StoredTitle();

        call_user_func_array([$obj, 'set' . ucfirst($property)], [$value]);
        $this->assertEquals($value, call_user_func_array([$obj, 'get' . ucfirst($property)], []));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            ['params', ['testKey' => 'testValue']],
            ['template', 'testValue'],
            ['prefix', 'testValue'],
            ['suffix', 'testValue']
        ];
    }
}
