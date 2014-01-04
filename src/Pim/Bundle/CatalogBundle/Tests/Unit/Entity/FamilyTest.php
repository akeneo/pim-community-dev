<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\Family
     */
    protected $family;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->family = new Family();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->family);

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->family->getAttributes());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->family->getTranslations());

        $this->assertCount(0, $this->family->getAttributes());
        $this->assertCount(0, $this->family->getTranslations());
    }

    /**
     * Test getter for id property
     */
    public function testId()
    {
        $this->assertEmpty($this->family->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->family->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($this->family->setCode($newCode));
        $this->assertEquals($newCode, $this->family->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->family->setCode($newCode);
        $this->assertEquals($expectedCode, $this->family->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->family->setLocale('en_US'));
        $this->assertEntity($this->family->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->family->getLabel());

        // if no translation, assert the expected code is returned
        $this->family->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->family->getLabel());

        // if empty translation, assert the expected code is returned
        $this->family->setLabel('');
        $this->assertEquals($expectedCode, $this->family->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'toStringCode';
        $expectedCode = '['. $newCode .']';
        $this->family->setCode($newCode);
        $this->assertEquals($expectedCode, $this->family->__toString());

        $newLabel = 'toStringLabel';
        $this->assertEntity($this->family->setLocale('en_US'));
        $this->assertEntity($this->family->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->family->__toString());

        // if no translation, assert the expected code is returned
        $this->family->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->family->__toString());

        // if empty translation, assert the expected code is returned
        $this->family->setLabel('');
        $this->assertEquals($expectedCode, $this->family->__toString());
    }

    /**
     * Test getter/setter for attributes property
     */
    public function testGetAddRemoveAttribute()
    {
        // Change value and assert new
        $newAttribute = new Attribute();
        $this->assertEntity($this->family->addAttribute($newAttribute));
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            $this->family->getAttributes()->first()
        );
        $this->assertTrue($this->family->hasAttribute($newAttribute));

        $this->assertEntity($this->family->removeAttribute($newAttribute));
        $this->assertNotInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            $this->family->getAttributes()->first()
        );
        $this->assertCount(0, $this->family->getAttributes());
        $this->assertFalse($this->family->hasAttribute($newAttribute));
    }

    /**
     * Assert entity
     *
     * @param Pim\Bundle\CatalogBundle\Entity\Family $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Family', $entity);
    }

    /**
     * Test related method
     */
    public function testGetSetAttributeAsLabel()
    {
        $attribute = $this->getAttributeMock();

        $this->assertNull($this->family->getAttributeAsLabel());
        $this->assertEntity($this->family->setAttributeAsLabel($attribute));
        $this->assertEquals($attribute, $this->family->getAttributeAsLabel());
    }

    /**
     * Test related method
     */
    public function testAddAttribute()
    {
        $attribute = $this->getAttributeMock();

        $this->assertEntity($this->family->addAttribute($attribute));
        $this->assertEquals(array($attribute), $this->family->getAttributes()->toArray());
    }

    /**
     * Test related method
     */
    public function testGetAttributeAsLabelChoices()
    {
        $name    = $this->getAttributeMock();
        $address = $this->getAttributeMock();
        $phone   = $this->getAttributeMock('phone');

        $this->family->addAttribute($name);
        $this->family->addAttribute($address);
        $this->family->addAttribute($phone);

        $this->assertEquals(array($name, $address), $this->family->getAttributeAsLabelChoices());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRemoveIdentifierAttribute()
    {
        $attribute = $this->getAttributeMock('pim_catalog_identifier');
        $this->family->removeAttribute($attribute);
    }

    /**
     * Test related method
     */
    public function testGetAttributeRequirementKeyFor()
    {
        $this->assertEquals('foo_bar', $this->family->getAttributeRequirementKeyFor('foo', 'bar'));
    }

    /**
     * Test related method
     */
    public function testGetAttributeRequirements()
    {
        $mobileName           = $this->getAttributeRequirementMock('mobile', 'name');
        $mobileDescription    = $this->getAttributeRequirementMock('mobile', 'description');
        $ecommerceName        = $this->getAttributeRequirementMock('ecommerce', 'name');
        $ecommerceDescription = $this->getAttributeRequirementMock('ecommerce', 'description');

        $this->family->setAttributeRequirements(
            array(
                $mobileName,
                $mobileDescription,
                $ecommerceName,
                $ecommerceDescription
            )
        );

        $expectedAttrRequirements = array(
            'name_mobile'           => $mobileName,
            'description_mobile'    => $mobileDescription,
            'name_ecommerce'        => $ecommerceName,
            'description_ecommerce' => $ecommerceDescription,
        );
        $this->assertEquals(
            $expectedAttrRequirements,
            $this->family->getAttributeRequirements()
        );

        // test add method
        $ipadName = $this->getAttributeRequirementMock('ipad', 'name');
        $this->family->addAttributeRequirement($ipadName);

        $expectedAttrRequirements['name_ipad'] = $ipadName;
        $this->assertEquals(
            $expectedAttrRequirements,
            $this->family->getAttributeRequirements()
        );
    }

    /**
     * Test getter/setter for created property
     */
    public function testCreated()
    {
        $this->assertNull($this->family->getCreated());

        // change value and assert new
        $newCreated = new \Datetime();
        $this->assertEntity($this->family->setCreated($newCreated));
        $this->assertEquals($newCreated, $this->family->getCreated());
    }

    /**
     * Test getter/setter for updated property
     */
    public function testUpdated()
    {
        $this->assertEmpty($this->family->getUpdated());

        // Change value and assert new
        $newUpdated = new \Datetime();
        $this->assertEntity($this->family->setUpdated($newUpdated));
        $this->assertEquals($newUpdated, $this->family->getUpdated());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->family->getTranslations());

        // Change value and assert new
        $newTranslation = new FamilyTranslation();
        $this->assertEntity($this->family->addTranslation($newTranslation));
        $this->assertCount(1, $this->family->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\FamilyTranslation',
            $this->family->getTranslations()->first()
        );

        $this->family->addTranslation($newTranslation);
        $this->assertCount(1, $this->family->getTranslations());

        $this->assertEntity($this->family->removeTranslation($newTranslation));
        $this->assertCount(0, $this->family->getTranslations());
    }

    /**
     * Get product attribute mock with attribute type
     *
     * @param string $type
     * @param string $code
     *
     * @return Pim\Bundle\CatalogBundle\Entity\Attribute
     */
    protected function getAttributeMock($type = 'pim_catalog_text', $code = null)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
                  ->method('getAttributeType')
                  ->will($this->returnValue($type));

        $attribute->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $attribute;
    }

    /**
     * Get channel mock with code
     *
     * @param string $code
     *
     * @return Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $channel->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $channel;
    }

    /**
     * Get attribute requirement mock with channel and attribute codes
     *
     * @param string $channelCode
     * @param string $attributeCode
     *
     * @return Pim\Bundle\CatalogBundle\Entity\AttributeRequirement
     */
    protected function getAttributeRequirementMock($channelCode, $attributeCode)
    {
        $requirement = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');

        $requirement->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($this->getChannelMock($channelCode)));

        $requirement->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->getAttributeMock('pim_catalog_text', $attributeCode)));

        return $requirement;
    }
}
