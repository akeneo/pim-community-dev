<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFamilyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $productFamily = new ProductFamily();
        $this->assertEntity($productFamily);
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $productFamily = new ProductFamily();
        $this->assertEmpty($productFamily->getId());
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetCode()
    {
        $productFamily = new ProductFamily();
        $this->assertEmpty($productFamily->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $productFamily->setCode($newCode);
        $this->assertEquals($newCode, $productFamily->getCode());
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetLabel()
    {
        $productFamily = new ProductFamily();
        $this->assertEmpty($productFamily->getLabel());

        // Change value and assert new
        $newLabel = 'test-label';
        $productFamily->setLabel($newLabel);
        $this->assertEquals($newLabel, $productFamily->getLabel());
    }

    /**
     * Test getter/setter for attributes property
     */
    public function testGetAddRemoveAttribute()
    {
        $productFamily = new ProductFamily();

        // Change value and assert new
        $newAttribute = new ProductAttribute();
        $productFamily->addAttribute($newAttribute);
        $this->assertInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $productFamily->getAttributes()->first()
        );

        $productFamily->removeAttribute($newAttribute);
        $this->assertNotInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $productFamily->getAttributes()->first()
        );
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $productFamily = new ProductFamily();
        $string = 'test-string';
        $productFamily->setCode($string);
        $this->assertEquals($string, $productFamily->__toString());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\ProductFamily $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductFamily', $entity);
    }

    public function testGetSetAttributeAsLabel()
    {
        $family    = new ProductFamily;
        $attribute = $this->getAttributeMock();

        $this->assertNull($family->getAttributeAsLabel());
        $family->setAttributeAsLabel($attribute);
        $this->assertEquals($attribute, $family->getAttributeAsLabel());
    }

    public function testGetAttributeAsLabelChoices()
    {
        $family  = new ProductFamily;
        $name    = $this->getAttributeMock();
        $address = $this->getAttributeMock();
        $phone   = $this->getAttributeMock('phone');

        $family->addAttribute($name);
        $family->addAttribute($address);
        $family->addAttribute($phone);

        $this->assertEquals(array($name, $address), $family->getAttributeAsLabelChoices());
    }

    private function getAttributeMock($type = 'pim_product_text')
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute', array('getAttributeType'));

        $attribute->expects($this->any())
                  ->method('getAttributeType')
                  ->will($this->returnValue($type));

        return $attribute;
    }
}
