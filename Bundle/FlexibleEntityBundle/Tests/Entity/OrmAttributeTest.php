<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\OrmAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\OrmAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Entity\OrmAttributeOptionValue;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class OrmAttributeTest extends \PHPUnit_Framework_TestCase
{
    protected $attributeCode = 'sku';
    protected $attributeTitle = 'My sku';

    /**
* Test related method
*/
    public function testGetCode()
    {
        $attribute = new OrmAttribute();
        $attribute->setCode($this->attributeCode);
        $this->assertEquals($attribute->getCode(), $this->attributeCode);
    }

    /**
* Test related method
*/
    public function testGetTitle()
    {
        $attribute = new OrmAttribute();
        $attribute->setTitle($this->attributeTitle);
        $this->assertEquals($attribute->getTitle(), $this->attributeTitle);
    }


    /**
* Test related method
*/
    public function testGetOptions()
    {
        // attribute
        $attribute = new OrmAttribute();
        $attribute->setCode($this->attributeCode);
        // option
        $option = new OrmAttributeOption();
        // option value
        $optionValue = new OrmAttributeOptionValue();
        $option->addOptionValue($optionValue);
        $attribute->addOption($option);

        $this->assertEquals($attribute->getOptions()->count(), 1);
    }
}