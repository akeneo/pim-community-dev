<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new Collection();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function testToString()
    {
        $obj = new Collection();
        $text = 'sfd';
        $obj->setData($text);

        $this->assertEquals((string)$obj, $text);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('id', 1),
            array('data', 'asdfasdfsdf'),
            array('type', 'test_type'),
        );
    }
}
