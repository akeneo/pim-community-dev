<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Oro\Bundle\NavigationBundle\Entity\Title;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $obj = new Title();
        $value = 'testValue';

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
            ['route'],
            ['title'],
            ['shortTitle'],
            ['isSystem']
        ];
    }
}
