<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue;

use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @staticvar integer
     */
    protected static $id = 12;

    /**
     * @staticvar string
     */
    protected static $locale = 'en';

    /**
     * @staticvar string
     */
    protected static $value = 'testAttOptValue';

    /**
     * @staticvar string
     */
    protected static $attClass = 'Pim\Bundle\FlexibleEntityBundle\Entity\Attribute';

    /**
     * @staticvar string
     */
    protected static $attOptClass = 'Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption';

    /**
     * @staticvar string
     */
    protected static $attOptValueClass = 'Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue';

    /**
     * Test related getter/setter method
     */
    public function testId()
    {
        $attOptValue = new AttributeOptionValue();

        // assert default value is null
        $this->assertNull($attOptValue->getId());

        // assert get/set
        $obj = $attOptValue->setId(self::$id);
        $this->assertInstanceOf(self::$attOptValueClass, $obj);
        $this->assertEquals(self::$id, $attOptValue->getId());
    }

    /**
     * Test related getter/setter method
     */
    public function testGetLocale()
    {
        $attOptValue = new AttributeOptionValue();

        // assert default value is null
        $this->assertNull($attOptValue->getLocale());

        // assert get/set
        $obj = $attOptValue->setLocale(self::$locale);
        $this->assertInstanceOf(self::$attOptValueClass, $obj);
        $this->assertEquals(self::$locale, $attOptValue->getLocale());
    }

    /**
     * Test related getter/setter method
     */
    public function testValue()
    {
        $attOptValue = new AttributeOptionValue();

        // assert default value is null
        $this->assertNull($attOptValue->getValue());

        // assert get/set
        $obj = $attOptValue->setValue(self::$value);
        $this->assertInstanceOf(self::$attOptValueClass, $obj);
        $this->assertEquals(self::$value, $attOptValue->getValue());
    }

    /**
     * Test related getter/setter method
     */
    public function testOption()
    {
        // initialize entities
        $attOpt = new AttributeOption();
        $attOptValue = new AttributeOptionValue();

        // assert get/set
        $obj = $attOptValue->setOption($attOpt);
        $this->assertInstanceOf(self::$attOptValueClass, $obj);
        $this->assertEquals($attOpt, $attOptValue->getOption());
    }
}
