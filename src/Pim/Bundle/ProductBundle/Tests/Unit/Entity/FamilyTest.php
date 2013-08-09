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

        // Change value and assert new
        $family->setCode('code');
        $this->assertEquals('[code]', $family->getLabel());
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
        $this->assertEquals('['.$string.']', $family->__toString());
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

    public function testAddAttribute()
    {
        $family = new Family();
        $attribute = $this->getAttributeMock();
        $family->addAttribute($attribute);
        $this->assertEquals(array($attribute), $family->getAttributes()->toArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddIdentifierAttribute()
    {
        $family = new Family();
        $attribute = $this->getAttributeMock('pim_product_identifier');
        $family->addAttribute($attribute);
    }

    public function testGetAttributeRequirementKeyFor()
    {
        $family = new Family();

        $this->assertEquals('foo_bar', $family->getAttributeRequirementKeyFor('foo', 'bar'));
    }

    public function testGetAttributeRequirements()
    {
        $family               = new Family();
        $mobileName           = $this->getAttributeRequirementMock('mobile',    'name');
        $mobileDescription    = $this->getAttributeRequirementMock('mobile',    'description');
        $ecommerceName        = $this->getAttributeRequirementMock('ecommerce', 'name');
        $ecommerceDescription = $this->getAttributeRequirementMock('ecommerce', 'description');

        $family->setAttributeRequirements(array(
            $mobileName, $mobileDescription, $ecommerceName, $ecommerceDescription
        ));

        $this->assertEquals(array(
            'name_mobile'           => $mobileName,
            'description_mobile'    => $mobileDescription,
            'name_ecommerce'        => $ecommerceName,
            'description_ecommerce' => $ecommerceDescription,
        ), $family->getAttributeRequirements());
    }

    /**
     * Get product attribute mock with attribute type
     *
     * @param string $type
     *
     * @return Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function getAttributeMock($type = 'pim_product_text', $code = null)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
                  ->method('getAttributeType')
                  ->will($this->returnValue($type));

        $attribute->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $attribute;
    }

    protected function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\ConfigBundle\Entity\Channel');

        $channel->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $channel;
    }

    protected function getAttributeRequirementMock($channelCode, $attributeCode)
    {
        $requirement = $this->getMock('Pim\Bundle\ProductBundle\Entity\AttributeRequirement');

        $requirement->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue(
                $this->getChannelMock($channelCode)
            ));

        $requirement->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue(
                $this->getAttributeMock('pim_product_text', $attributeCode)
            ));

        return $requirement;
    }
}
