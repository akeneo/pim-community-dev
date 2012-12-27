<?php
namespace Oro\Bundle\DataModelBundle\Test\Entity;

use Oro\Bundle\DataModelBundle\Entity\OrmEntityAttribute;
use Oro\Bundle\DataModelBundle\Entity\OrmEntityAttributeOption;
use Oro\Bundle\DataModelBundle\Entity\OrmEntityAttributeOptionValue;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class OrmEntityAttributeTest extends \PHPUnit_Framework_TestCase
{
    protected $attributeCode = 'sku';
    protected $attributeTitle = 'My sku';

    /**
* Test related method
*/
    public function testGetCode()
    {
        $attribute = new OrmEntityAttribute();
        $attribute->setCode($this->attributeCode);
        $this->assertEquals($attribute->getCode(), $this->attributeCode);
    }

    /**
* Test related method
*/
    public function testGetTitle()
    {
        $attribute = new OrmEntityAttribute();
        $attribute->setTitle($this->attributeTitle);
        $this->assertEquals($attribute->getTitle(), $this->attributeTitle);
    }


    /**
* Test related method
*/
    public function testGetOptions()
    {
        // attribute
        $attribute = new OrmEntityAttribute();
        $attribute->setCode($this->attributeCode);
        // option
        $option = new OrmEntityAttributeOption();
        // option value
        $optionValue = new OrmEntityAttributeOptionValue();
        $option->addOptionValue($optionValue);
        $attribute->addOption($option);

        $this->assertEquals($attribute->getOptions()->count(), 1);
    }
}