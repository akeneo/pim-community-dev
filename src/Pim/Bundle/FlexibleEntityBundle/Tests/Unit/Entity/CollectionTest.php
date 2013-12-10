<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Collection;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     *
     * @param string $property
     * @param mixed  $value
     *
     * @dataProvider provider
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new Collection();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    /**
     * Test related method
     */
    public function testToString()
    {
        $obj = new Collection();
        $text = 'sfd';
        $obj->setData($text);

        $this->assertEquals((string) $obj, $text);
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
