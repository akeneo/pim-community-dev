<?php
namespace Oro\Bundle\ProductBundle\Test\Entity;

use Oro\Bundle\ProductBundle\Entity\ProductAttributeOption;

use Oro\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    protected $attributeCode  = 'sku';
    protected $attributeTitle = 'My sku';

    /**
     * Test related method
     */
    public function testGetCode()
    {
        $attribute = new ProductAttribute();
        $attribute->setCode($this->attributeCode);
        $this->assertEquals($attribute->getCode(), $this->attributeCode);
    }

    /**
     * Test related method
     */
    public function testGetTitle()
    {
        $attribute = new ProductAttribute();
        $attribute->setTitle($this->attributeTitle);
        $this->assertEquals($attribute->getTitle(), $this->attributeTitle);
    }


    /**
     * Test related method
     */
    public function testGetOptions()
    {
        // attribute
        $attribute = new ProductAttribute();
        $attribute->setCode($this->attributeCode);
        // option
        $option = new ProductAttributeOption();
        $attribute->addOption($option);

        $this->assertEquals($attribute->getOptions()->count(), 1);
    }
}