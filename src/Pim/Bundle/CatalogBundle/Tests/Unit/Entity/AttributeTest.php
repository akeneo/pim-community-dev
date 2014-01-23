<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->attribute = new Attribute();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->attribute);

        $this->assertEmptyCollection($this->attribute->getOptions());
        $this->assertNull($this->attribute->getAvailableLocales());
        $this->assertEmptyCollection($this->attribute->getTranslations());

        $this->assertFalse($this->attribute->isRequired());
        $this->assertFalse($this->attribute->isUnique());
        $this->assertNull($this->attribute->getDefaultValue());
        $this->assertFalse($this->attribute->isSearchable());
        $this->assertFalse($this->attribute->isTranslatable());
        $this->assertFalse($this->attribute->isScopable());
        $this->assertFalse($this->attribute->isUseableAsGridColumn());
        $this->assertFalse($this->attribute->isUseableAsGridFilter());
        $this->assertNull($this->attribute->isDecimalsAllowed());
        $this->assertNull($this->attribute->isNegativeAllowed());
    }

    /**
     * Assert an empty collection
     *
     * @param \Doctrine\Common\Collections\Collection $collection
     */
    protected function assertEmptyCollection($collection)
    {
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $collection);
        $this->assertCount(0, $collection);
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->attribute->setCode($newCode);
        $this->assertEquals($expectedCode, $this->attribute->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->attribute->setLocale('en_US'));
        $this->assertEntity($this->attribute->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->attribute->getLabel());

        // if no translation, assert the expected code is returned
        $this->attribute->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->attribute->getLabel());

        // if empty translation, assert the expected code is returned
        $this->attribute->setLabel('');
        $this->assertEquals($expectedCode, $this->attribute->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->attribute->setCode($newCode);
        $this->assertEquals($expectedCode, $this->attribute->__toString());

        $newLabel = 'test-label';
        $this->assertEntity($this->attribute->setLocale('en_US'));
        $this->assertEntity($this->attribute->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->attribute->__toString());

        // if no translation, assert the expected code is returned
        $this->attribute->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->attribute->__toString());

        // if empty translation, assert the expected code is returned
        $this->attribute->setLabel('');
        $this->assertEquals($expectedCode, $this->attribute->__toString());
    }

    /**
     * Test get virtual group
     */
    public function testGetVirtualGroup()
    {
        $this->attribute->getVirtualGroup()->setLocale('en_US');
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', $this->attribute->getVirtualGroup());
        $this->assertEquals('Other', $this->attribute->getVirtualGroup()->getLabel());

        $attributeGroup = new AttributeGroup();
        $this->assertEntity($this->attribute->setGroup($attributeGroup));
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', $this->attribute->getVirtualGroup());
        $this->assertEquals($attributeGroup, $this->attribute->getGroup());
    }

    /**
     * Test getter/setter for group property
     *
     * TODO : Test with null
     */
    public function testGetSetGroup()
    {
        $this->assertNull($this->attribute->getGroup());

        $attributeGroup = new AttributeGroup();
        $this->assertEntity($this->attribute->setGroup($attributeGroup));
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', $this->attribute->getGroup());
        $this->assertEquals($attributeGroup, $this->attribute->getGroup());
    }

    /**
     * Test is/setter for useableAsGridColumn property
     *
     * TODO : Test with the both values
     */
    public function testIsSetUseableAsGridColumn()
    {
        $this->assertFalse($this->attribute->isUseableAsGridColumn());

        // change value and assert new
        $newUseableAsGridColumn = true;
        $this->assertEntity($this->attribute->setUseableAsGridColumn($newUseableAsGridColumn));
        $this->assertTrue($this->attribute->isUseableAsGridColumn());
    }

    /**
     * Test is/setter for useableAsGridFilter property
     *
     * TODO : Test with the both values
     */
    public function testIsSetUseableAsGridFilter()
    {
        $this->assertFalse($this->attribute->isUseableAsGridFilter());

        // change value and assert new
        $newUseableAsGridFilter = true;
        $this->assertEntity($this->attribute->setUseableAsGridFilter($newUseableAsGridFilter));
        $this->assertTrue($this->attribute->isUseableAsGridFilter());
    }

    /**
     * Test get/add/remove availableLocales property
     *
     * TODO : Add more tests
     */
    public function testGetAddRemoveAvailableLocales()
    {
        $this->assertNull($this->attribute->getAvailableLocales());

        // Change value and assert new
        $newLocale = new Locale();
        $this->attribute->addAvailableLocale($newLocale);
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\Locale',
            $this->attribute->getAvailableLocales()->first()
        );
        $this->assertCount(1, $this->attribute->getAvailableLocales());

        $this->attribute->removeAvailableLocale($newLocale);
        $this->assertNull($this->attribute->getAvailableLocales());
    }

    /**
     * Test getter/setter for maxCharacters property
     */
    public function testGetSetMaxCharacters()
    {
        $this->assertNull($this->attribute->getMaxCharacters());

        // Change value and assert new
        $characters = 100;
        $this->assertEntity($this->attribute->setMaxCharacters($characters));
        $this->assertEquals($characters, $this->attribute->getMaxCharacters());
    }

    /**
     * Test getter/setter for validationRule property
     */
    public function testGetSetValidationRule()
    {
        $this->assertNull($this->attribute->getValidationRule());

        // Change value and assert new
        $rule = 'email';
        $this->assertEntity($this->attribute->setValidationRule($rule));
        $this->assertEquals($rule, $this->attribute->getValidationRule());
    }

    /**
     * Test getter/setter for validationRegexp property
     */
    public function testGetSetValidationRegexp()
    {
        $this->assertNull($this->attribute->getValidationRegexp());

        // Change value and assert new
        $regexp = '/[^0-9]/';
        $this->assertEntity($this->attribute->setValidationRegexp($regexp));
        $this->assertEquals($regexp, $this->attribute->getValidationRegexp());
    }

    /**
     * Test is/setter for wysiwygEnabled property
     *
     * TODO : Test with the both values
     */
    public function testIsSetWysiwygEnabled()
    {
        $this->assertNull($this->attribute->isWysiwygEnabled());

        // Change value and assert new
        $this->assertEntity($this->attribute->setWysiwygEnabled(true));
        $this->assertTrue($this->attribute->isWysiwygEnabled());
    }

    /**
     * Test getter/setter for numberMin property
     */
    public function testGetSetNumberMin()
    {
        $this->assertNull($this->attribute->getNumberMin());

        // Change value and assert new
        $number = 10;
        $this->assertEntity($this->attribute->setNumberMin($number));
        $this->assertEquals($number, $this->attribute->getNumberMin());
    }

    /**
     * Test getter/setter for numberMax property
     */
    public function testGetSetNumberMax()
    {
        $this->assertNull($this->attribute->getNumberMax());

        // Change value and assert new
        $number = 20;
        $this->assertEntity($this->attribute->setNumberMax($number));
        $this->assertEquals($number, $this->attribute->getNumberMax());
    }

    /**
     * Test is/setter for decimalsAllowed property
     *
     * TODO : Test with the both values
     */
    public function testIsSetDecimalsAllowed()
    {
        $this->assertNull($this->attribute->isDecimalsAllowed());

        $decimalsAllowed = false;
        $this->assertEntity($this->attribute->setDecimalsAllowed($decimalsAllowed));
        $this->assertEquals($decimalsAllowed, $this->attribute->isDecimalsAllowed());
    }

    /**
     * Test is/setter for negativeAllowed property
     *
     * TODO : Test with the both values
     */
    public function testIsSetNegativeAllowed()
    {
        $this->assertNull($this->attribute->isNegativeAllowed());

        // Change value and assert new
        $this->assertEntity($this->attribute->setNegativeAllowed(false));
        $this->assertFalse($this->attribute->isNegativeAllowed());
    }

    /**
     * Test getter/setter for dateType property
     */
    public function testGetSetDateType()
    {
        $this->assertNull($this->attribute->getDateType());

        // Change value and assert new
        $dateType = 'datetime';
        $this->assertEntity($this->attribute->setDateType($dateType));
        $this->assertEquals($dateType, $this->attribute->getDateType());
    }

    /**
     * Test getter/setter for dateMin property
     */
    public function testGetSetDateMin()
    {
        $this->assertNull($this->attribute->getDateMin());

        // Change value and assert new
        $date = new \DateTime();
        $this->assertEntity($this->attribute->setDateMin($date));
        $this->assertInstanceOf('DateTime', $this->attribute->getDateMin());
        $this->assertEquals($date, $this->attribute->getDateMin());
    }

    /**
     * Test getter/setter for dateMax property
     */
    public function testGetSetDateMax()
    {
        $this->assertNull($this->attribute->getDateMax());

        // Change value and assert new
        $date = new \DateTime();
        $this->assertEntity($this->attribute->setDateMax($date));
        $this->assertInstanceOf('DateTime', $this->attribute->getDateMax());
        $this->assertEquals($date, $this->attribute->getDateMax());
    }

    /**
     * Test getter/setter for metricType property
     */
    public function testGetSetMetricFamily()
    {
        $this->assertNull($this->attribute->getMetricFamily());

        // Change value and assert new
        $type = 'weight';
        $this->assertEntity($this->attribute->setMetricFamily($type));
        $this->assertEquals($type, $this->attribute->getMetricFamily());
    }

    /**
     * Test getter/setter for defaultMetricUnit property
     */
    public function testGetSetDefaultMetricUnit()
    {
        $this->assertNull($this->attribute->getDefaultMetricUnit());

        // Change value and assert new
        $unit = 'm';
        $this->assertEntity($this->attribute->setDefaultMetricUnit($unit));
        $this->assertEquals($unit, $this->attribute->getDefaultMetricUnit());
    }

    /**
     * Test getter/setter for maxFileSize property
     */
    public function testGetSetMaxFileSize()
    {
        $this->assertNull($this->attribute->getMaxFileSize());

        // Change value and assert new
        $size = 1.23;
        $this->assertEntity($this->attribute->setMaxFileSize($size));
        $this->assertEquals($size, $this->attribute->getMaxFileSize());
    }

    /**
     * Test getter/setter for allowedExtensions property
     */
    public function testGetSetAllowedExtensions()
    {
        $this->assertEmpty($this->attribute->getAllowedExtensions());

        // Change value and assert new
        $extensions = ['jpg', 'png', 'gif'];
        $this->assertEntity($this->attribute->setAllowedExtensions(' jpg, png,gif'));
        $this->assertEquals($extensions, $this->attribute->getAllowedExtensions());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->attribute->getTranslations());

        // Change value and assert new
        $newTranslation = new AttributeTranslation();
        $this->assertEntity($this->attribute->addTranslation($newTranslation));
        $this->assertCount(1, $this->attribute->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\AttributeTranslation',
            $this->attribute->getTranslations()->first()
        );

        $this->attribute->addTranslation($newTranslation);
        $this->assertCount(1, $this->attribute->getTranslations());

        $this->assertEntity($this->attribute->removeTranslation($newTranslation));
        $this->assertCount(0, $this->attribute->getTranslations());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetLocale()
    {
        $this->attribute->setLocale('en_US');
    }

    /**
     * Test getter/setter for sortOrder property
     */
    public function testGetSetSortOrder()
    {
        $this->assertEquals(0, $this->attribute->getSortOrder());

        $expectedOrder = 3;
        $this->assertEntity($this->attribute->setSortOrder($expectedOrder));
        $this->assertEquals($expectedOrder, $this->attribute->getSortOrder());
    }

    /**
     * Test getter/setter for defaultValue property
     */
    public function testGetSetDefaultValue()
    {
        $this->assertEquals('', $this->attribute->getDefaultValue());

        $expectedDefaultValue = 'test-default-value';
        $this->assertEntity($this->attribute->setDefaultValue($expectedDefaultValue));
        $this->assertEquals($expectedDefaultValue, $this->attribute->getDefaultValue());
    }

    /**
     * Data provider for set parameters method
     *
     * @return array
     */
    public static function setParametersDataProvider()
    {
        return [
            [
                'someValues' =>
                    ['sortOrder' => 5, 'maxFileSize' => 4, 'dateMin' => '2013-06-15', 'decimalsAllowed' => true]
            ],
            [
                'onlyOneValue' => ['negativeAllowed' => false]
            ],
            [
                'noValue' => []
            ]
        ];
    }

    /**
     * Test related method
     *
     * @param array $parameters
     *
     * @dataProvider setParametersDataProvider
     */
    public function testSetParameters($parameters)
    {
        $this->assertEntity($this->attribute->setParameters($parameters));
    }

    /**
     * Assert entity
     * @param Pim\Bundle\CatalogBundle\Entity\Attribute $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Attribute', $entity);
    }
}
