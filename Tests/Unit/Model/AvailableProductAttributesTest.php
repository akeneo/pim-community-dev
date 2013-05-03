<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Model;

use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AvailableProductAttributesTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $attributes[] = $this->getProductAttributeMock(24);
        $attributes[] = $this->getProductAttributeMock(42);
        $target       = $this->getTargetedClass($attributes);

        $this->assertEquals(array(24 => false, 42 => false), $target->getAttributes());
    }

    public function testSetAttributes()
    {
        $target = $this->getTargetedClass();
        $target->setAttributes(array('foo', 'bar'));

        $this->assertEquals(array('foo', 'bar'), $target->getAttributes());
    }

    public function testGetAttribute()
    {
        $attribute = $this->getProductAttributeMock(42);
        $target    = $this->getTargetedClass(array($attribute));

        $this->assertEquals(null, $target->getAttribute(24));
        $this->assertEquals($attribute, $target->getAttribute(42));
    }

    public function testGetAttributeToAdd()
    {
        $attribute24 = $this->getProductAttributeMock(24);
        $attribute42 = $this->getProductAttributeMock(42);
        $target       = $this->getTargetedClass(array($attribute24, $attribute42));
        $target->setAttributes(array(24 => true, 42 => false));

        $this->assertEquals(array($attribute24), $target->getAttributesToAdd());
    }

    private function getTargetedClass(array $attributes = array())
    {
        return new AvailableProductAttributes($attributes);
    }

    private function getProductAttributeMock($id)
    {
        $productAttribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute', array('getId'));
        $productAttribute->expects($this->any())
                         ->method('getId')
                         ->will($this->returnValue($id));

        return $productAttribute;
    }
}
