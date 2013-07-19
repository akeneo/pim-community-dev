<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FamilyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $family = new Family();
        $this->assertEntity($family);
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $family = new Family();
        $this->assertEmpty($family->getId());
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetCode()
    {
        $family = new Family();
        $this->assertEmpty($family->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $family->setCode($newCode);
        $this->assertEquals($newCode, $family->getCode());
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetLabel()
    {
        $family = new Family();
        $this->assertEmpty($family->getLabel());

        // Change value and assert new
        $newLabel = 'test-label';
        $family->setLocale('en_US');
        $family->setLabel($newLabel);
        $this->assertEquals($newLabel, $family->getLabel());
    }

    /**
     * Test getter/setter for attributes property
     */
    public function testGetAddRemoveAttribute()
    {
        $family = new Family();

        // Change value and assert new
        $newAttribute = new ProductAttribute();
        $family->addAttribute($newAttribute);
        $this->assertInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $family->getAttributes()->first()
        );
        $this->assertTrue($family->hasAttribute($newAttribute));

        $family->removeAttribute($newAttribute);
        $this->assertNotInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $family->getAttributes()->first()
        );
        $this->assertFalse($family->hasAttribute($newAttribute));
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $family = new Family();
        $string = 'test-string';
        $family->setCode($string);
        $this->assertEquals($string, $family->__toString());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\Family $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Family', $entity);
    }

    /**
     * Test related method
     */
    public function testGetSetAttributeAsLabel()
    {
        $family    = new Family;
        $attribute = $this->getAttributeMock();

        $this->assertNull($family->getAttributeAsLabel());
        $family->setAttributeAsLabel($attribute);
        $this->assertEquals($attribute, $family->getAttributeAsLabel());
    }

    /**
     * Test related method
     */
    public function testGetAttributeAsLabelChoices()
    {
        $family  = new Family;
        $name    = $this->getAttributeMock();
        $address = $this->getAttributeMock();
        $phone   = $this->getAttributeMock('phone');

        $family->addAttribute($name);
        $family->addAttribute($address);
        $family->addAttribute($phone);

        $this->assertEquals(array($name, $address), $family->getAttributeAsLabelChoices());
    }

    /**
     * Get product attribute mock with attribute type
     *
     * @param string $type
     *
     * @return Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function getAttributeMock($type = 'pim_product_text')
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute', array('getAttributeType'));

        $attribute->expects($this->any())
                  ->method('getAttributeType')
                  ->will($this->returnValue($type));

        return $attribute;
    }
}
