<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    protected $attributeCode = 'sku';
    protected $attributeTitle = 'My sku';

    /**
* Test related method
*/
    public function testGetCode()
    {
        $attribute = new Attribute();
        $attribute->setCode($this->attributeCode);
        $this->assertEquals($attribute->getCode(), $this->attributeCode);
    }

    /**
* Test related method
*/
    public function testGetTitle()
    {
        $attribute = new Attribute();
        $attribute->setTitle($this->attributeTitle);
        $this->assertEquals($attribute->getTitle(), $this->attributeTitle);
    }


    /**
* Test related method
*/
    public function testGetOptions()
    {
        // attribute
        $attribute = new Attribute();
        $attribute->setCode($this->attributeCode);
        // option
        $option = new AttributeOption();
        // option value
        $optionValue = new AttributeOptionValue();
        $option->addOptionValue($optionValue);
        $attribute->addOption($option);

        $this->assertEquals($attribute->getOptions()->count(), 1);
    }
}