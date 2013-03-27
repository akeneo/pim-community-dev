<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ConfigBundle\Entity\Language;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $productAttribute = new ProductAttribute();
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductAttribute', $productAttribute);
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetName()
    {
        $productAttribute = new ProductAttribute();
        $this->assertEmpty($productAttribute->getName());

        // Change value and assert new
        $newName = 'test-name';
        $productAttribute->setName($newName);
        $this->assertEquals($newName, $productAttribute->getName());
    }

    /**
     * Test getter/setter for description property
     */
    public function testGetSetDescription()
    {
        $productAttribute = new ProductAttribute();
        $this->assertEmpty($productAttribute->getDescription());

        // Change value and assert new
        $newDescription = 'test-description';
        $productAttribute->setDescription($newDescription);
        $this->assertEquals($newDescription, $productAttribute->getDescription());
    }

    /**
     * Test getter/setter for variant property
     */
    public function testGetSetVariant()
    {
        $productAttribute = new ProductAttribute();
        $this->assertEmpty($productAttribute->getVariant());

        // change value and assert new
        $newVariant = 'test-variant';
        $productAttribute->setVariant($newVariant);
        $this->assertEquals($newVariant, $productAttribute->getVariant());
    }

    /**
     * Test getter/setter for smart property
     */
    public function testGetSetSmart()
    {
        $productAttribute = new ProductAttribute();
        $this->assertFalse($productAttribute->getSmart());

        // change value and assert new
        $newSmart = true;
        $productAttribute->setSmart($newSmart);
        $this->assertTrue($productAttribute->getSmart());
    }

    /**
     * Test getter/setter for group property
     */
    public function testGetSetGroup()
    {
        $productAttribute = new ProductAttribute();
        $this->assertEmpty($productAttribute->getGroup());

        // change value and assert new
        $attributeGroup = new AttributeGroup();
        $productAttribute->setGroup($attributeGroup);
        $this->assertEquals($attributeGroup, $productAttribute->getGroup());
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\AttributeGroup', $productAttribute->getGroup());

        $productAttribute->setGroup(null);
        $this->assertEquals(null, $productAttribute->getGroup());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $productAttribute = new ProductAttribute();
        $string = 'test-string';
        $productAttribute->setName($string);
        $this->assertEquals($string, $productAttribute->__toString());
    }

    /**
     * Test getter/setter for useableAsGridColumn property
     */
    public function testGetSetUseableAsGridColumn()
    {
        $productAttribute = new ProductAttribute();
        $this->assertFalse($productAttribute->getUseableAsGridColumn());

        // change value and assert new
        $newUseableAsGridColumn = true;
        $productAttribute->setUseableAsGridColumn($newUseableAsGridColumn);
        $this->assertTrue($productAttribute->getUseableAsGridColumn());
    }

    /**
     * Test getter/setter for useableAsGridFilter property
     */
    public function testGetSetUseableAsGridFilter()
    {
        $productAttribute = new ProductAttribute();
        $this->assertFalse($productAttribute->getUseableAsGridFilter());

        // change value and assert new
        $newUseableAsGridFilter = true;
        $productAttribute->setUseableAsGridFilter($newUseableAsGridFilter);
        $this->assertTrue($productAttribute->getUseableAsGridFilter());
    }

    /**
     * Test get/add/remove availableLanguages property
     */
    public function testGetAddRemoveAvailableLanguages()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getAvailableLanguages());

        // Change value and assert new
        $newLanguage = new Language();
        $productAttribute->addAvailableLanguage($newLanguage);
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Language', $productAttribute->getAvailableLanguages()->first());
        $this->assertCount(1, $productAttribute->getAvailableLanguages());

        $productAttribute->removeAvailableLanguage($newLanguage);
        $this->assertNull($productAttribute->getAvailableLanguages());
    }

    /**
     * Test getter/setter for maxCharacters property
     */
    public function testGetSetMaxCharacters()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getMaxCharacters());

        // Change value and assert new
        $characters = 100;
        $productAttribute->setMaxCharacters($characters);
        $this->assertEquals($characters, $productAttribute->getMaxCharacters());
    }

    /**
     * Test getter/setter for validationRule property
     */
    public function testGetSetValidationRule()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getValidationRule());

        // Change value and assert new
        $rule = 'email';
        $productAttribute->setValidationRule($rule);
        $this->assertEquals($rule, $productAttribute->getValidationRule());
    }

    /**
     * Test getter/setter for validationRegexp property
     */
    public function testGetSetValidationRegexp()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getValidationRegexp());

        // Change value and assert new
        $regexp = '/[^0-9]/';
        $productAttribute->setValidationRegexp($regexp);
        $this->assertEquals($regexp, $productAttribute->getValidationRegexp());
    }

    /**
     * Test getter/setter for wysiwygEnabled property
     */
    public function testGetSetWysiwygEnabled()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getWysiwygEnabled());

        // Change value and assert new
        $productAttribute->setWysiwygEnabled(true);
        $this->assertTrue($productAttribute->getWysiwygEnabled());
    }

    /**
     * Test getter/setter for numberMin property
     */
    public function testGetSetNumberMin()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getNumberMin());

        // Change value and assert new
        $number = 10;
        $productAttribute->setNumberMin($number);
        $this->assertEquals($number, $productAttribute->getNumberMin());
    }

    /**
     * Test getter/setter for numberMax property
     */
    public function testGetSetNumberMax()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getNumberMax());

        // Change value and assert new
        $number = 20;
        $productAttribute->setNumberMax($number);
        $this->assertEquals($number, $productAttribute->getNumberMax());
    }

    /**
     * Test getter/setter for decimalPlaces property
     */
    public function testGetSetDecimalPlaces()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getDecimalPlaces());

        // Change value and assert new
        $decimals = 2;
        $productAttribute->setDecimalPlaces($decimals);
        $this->assertEquals($decimals, $productAttribute->getDecimalPlaces());
    }

    /**
     * Test getter/setter for negativeAllowed property
     */
    public function testGetSetNegativeAllowed()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getNegativeAllowed());

        // Change value and assert new
        $productAttribute->setNegativeAllowed(true);
        $this->assertTrue($productAttribute->getNegativeAllowed());
    }

    /**
     * Test getter/setter for ValueCreationAllowed property
     */
    public function testGetSetValueCreationAllowed()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getValueCreationAllowed());

        // Change value and assert new
        $productAttribute->setValueCreationAllowed(true);
        $this->assertTrue($productAttribute->getValueCreationAllowed());
    }

    /**
     * Test getter/setter for dateType property
     */
    public function testGetSetDateType()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getDateType());

        // Change value and assert new
        $dateType = 'datetime';
        $productAttribute->setDateType($dateType);
        $this->assertEquals($dateType, $productAttribute->getDateType());
    }

    /**
     * Test getter/setter for dateMin property
     */
    public function testGetSetDateMin()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getDateMin());

        // Change value and assert new
        $date = new \DateTime();
        $productAttribute->setDateMin($date);
        $this->assertInstanceOf('DateTime', $productAttribute->getDateMin());
        $this->assertEquals($date, $productAttribute->getDateMin());
    }

    /**
     * Test getter/setter for dateMax property
     */
    public function testGetSetDateMax()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getDateMax());

        // Change value and assert new
        $date = new \DateTime();
        $productAttribute->setDateMax($date);
        $this->assertInstanceOf('DateTime', $productAttribute->getDateMax());
        $this->assertEquals($date, $productAttribute->getDateMax());
    }

    /**
     * Test getter/setter for metricType property
     */
    public function testGetSetMetricType()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getMetricType());

        // Change value and assert new
        $type = 'weight';
        $productAttribute->setMetricType($type);
        $this->assertEquals($type, $productAttribute->getMetricType());
    }

    /**
     * Test getter/setter for defaultMetricUnit property
     */
    public function testGetSetDefaultMetricUnit()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getDefaultMetricUnit());

        // Change value and assert new
        $unit = 'm';
        $productAttribute->setDefaultMetricUnit($unit);
        $this->assertEquals($unit, $productAttribute->getDefaultMetricUnit());
    }

    /**
     * Test getter/setter for allowedFileSources property
     */
    public function testGetSetAllowedFileSources()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getAllowedFileSources());

        // Change value and assert new
        $source = 'upload';
        $productAttribute->setAllowedFileSources($source);
        $this->assertEquals($source, $productAttribute->getAllowedFileSources());
    }

    /**
     * Test getter/setter for maxFileSize property
     */
    public function testGetSetMaxFileSize()
    {
        $productAttribute = new ProductAttribute();
        $this->assertNull($productAttribute->getMaxFileSize());

        // Change value and assert new
        $size = 1024;
        $productAttribute->setMaxFileSize($size);
        $this->assertEquals($size, $productAttribute->getMaxFileSize());
    }

    /**
     * Test getter/setter for allowedFileExtensions property
     */
    public function testGetSetAllowedFileExtensions()
    {
        $productAttribute = new ProductAttribute();
        $this->assertEmpty($productAttribute->getAllowedFileExtensions());

        // Change value and assert new
        $extensions = array('jpg', 'png', 'gif');
        $productAttribute->setAllowedFileExtensions($extensions);
        $this->assertEquals($extensions, $productAttribute->getAllowedFileExtensions());
    }
}
