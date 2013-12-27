<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    protected $attribute;
    protected $attributeCode  = 'sku';

    /**
     * Set up unit test
     */
    protected function setUp()
    {
        // create attribute
        $this->attribute = new Attribute();
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $myid = 123;
        $this->attribute->setId($myid);
        $this->assertEquals($this->attribute->getId(), $myid);
    }

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
    public function testGetEntityType()
    {
        $entityType = 'Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible';
        $this->attribute->setEntityType($entityType);
        $this->assertEquals($this->attribute->getEntityType(), $entityType);
    }

    /**
     * Test related method
     */
    public function testGetBackendStorage()
    {
        $storage = AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE;
        $this->attribute->setBackendStorage($storage);
        $this->assertEquals($this->attribute->getBackendStorage(), $storage);
    }

    /**
     * Test related method
     */
    public function testGetBackendType()
    {
        $type = AbstractAttributeType::BACKEND_TYPE_VARCHAR;
        $this->attribute->setBackendType($type);
        $this->assertEquals($this->attribute->getBackendType(), $type);
    }

    /**
     * Test related method
     */
    public function testUpdated()
    {
        $date = new \DateTime();
        $this->attribute->setUpdated($date);
        $this->assertEquals($this->attribute->getUpdated(), $date);
    }

    /**
     * Test related method
     */
    public function testCreated()
    {
        $date = new \DateTime();
        $this->attribute->setCreated($date);
        $this->assertEquals($this->attribute->getCreated(), $date);
    }

    /**
     * Test related method
     */
    public function testIsRequired()
    {
        // false by default
        $this->assertFalse($this->attribute->isRequired());
        $this->attribute->setRequired(true);
        $this->assertTrue($this->attribute->isRequired());
    }

    /**
     * Test related method
     */
    public function testIsUnique()
    {
        // false by default
        $this->assertFalse($this->attribute->isUnique());
        $this->attribute->setUnique(true);
        $this->assertTrue($this->attribute->isUnique());
    }

    /**
     * Test related method
     */
    public function testTranslatable()
    {
        // false by default
        $this->assertFalse($this->attribute->isTranslatable());
        $this->attribute->setTranslatable(true);
        $this->assertTrue($this->attribute->isTranslatable());
    }

    /**
     * Test related method
     */
    public function testSearchable()
    {
        // false by default
        $this->assertFalse($this->attribute->isSearchable());
        $this->attribute->setSearchable(true);
        $this->assertTrue($this->attribute->isSearchable());
    }

    /**
     * Test related method
     */
    public function testScopable()
    {
        // false by default
        $this->assertFalse($this->attribute->isScopable());
        $this->attribute->setScopable(true);
        $this->assertTrue($this->attribute->isScopable());
    }

    /**
     * Test related method
     */
    public function testDefaultValue()
    {
        // null by default
        $this->assertNull($this->attribute->getDefaultValue());
        $myvalue = 'my default value';
        $this->attribute->setDefaultValue($myvalue);
        $this->assertEquals($this->attribute->getDefaultValue(), $myvalue);
    }

    /**
     * Test related method
     */
    public function testConvertDefaultValueToTimestamp()
    {
        $date = new \DateTime('now');
        $this->attribute->setDefaultValue($date);
        $this->attribute->convertDefaultValueToTimestamp();
        $this->assertEquals($this->attribute->getDefaultValue(), $date->format('U'));
    }

    /**
     * Test related method
     */
    public function testConvertDefaultValueToDatetime()
    {
        $date = new \DateTime('now');
        $this->attribute->setDefaultValue($date->format('U'));
        $this->attribute->setAttributeType('pim_flexibleentity_date');
        $this->attribute->convertDefaultValueToDatetime();
        $this->assertEquals($this->attribute->getDefaultValue()->format('U'), $date->format('U'));
    }

    /**
     * Test related method
     */
    public function testConvertDefaultValueToInteger()
    {
        $this->attribute->convertDefaultValueToInteger();
        $this->assertNull($this->attribute->getDefaultValue());

        $this->attribute->setDefaultValue(true);
        $this->attribute->setAttributeType('pim_flexibleentity_integer');
        $this->attribute->convertDefaultValueToInteger();
        $this->assertEquals($this->attribute->getDefaultValue(), 1);
    }

    /**
     * Test related method
     */
    public function testConvertDefaultValueToBoolean()
    {
        $this->attribute->convertDefaultValueToInteger();
        $this->assertNull($this->attribute->getDefaultValue());

        $this->attribute->setDefaultValue(1);
        $this->attribute->setAttributeType('pim_flexibleentity_boolean');
        $this->attribute->convertDefaultValueToBoolean();
        $this->assertEquals($this->attribute->getDefaultValue(), true);
    }

    /**
     * Test related method
     */
    public function testGetOptions()
    {
        // option
        $option = new AttributeOption();
        // option value
        $optionValue = new AttributeOptionValue();
        $option->addOptionValue($optionValue);
        $this->attribute->addOption($option);
        $this->assertEquals($this->attribute->getOptions()->count(), 1);
        $this->attribute->removeOption($option);
        $this->assertEquals($this->attribute->getOptions()->count(), 0);
    }

    /**
     * Test related method
     */
    public function testGetSetSortOrder()
    {
        $this->assertEquals(0, $this->attribute->getSortOrder());

        $this->attribute->setSortOrder(20);
        $this->assertEquals(20, $this->attribute->getSortOrder());
    }
}
