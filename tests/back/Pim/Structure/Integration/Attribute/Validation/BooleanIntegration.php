<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BooleanIntegration extends AbstractAttributeTestCase
{
    public function testBooleanIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_boolean');
    }

    public function testBooleanIsNotUnique()
    {
        $this->assertNotUnique('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAllowedExtensions()
    {
        $this->assertDoesNotHaveAllowedExtensions('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAutoOptionSorting()
    {
        $this->assertDoesNotHaveAutoOptionSorting('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveMaxCharacters()
    {
        $this->assertDoesNotHaveMaxCharacters('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_boolean');
    }

    public function testBooleanShouldNotHaveAMaxFileSize()
    {
        $this->assertDoesNotHaveAMaxFileSize('pim_catalog_boolean');
    }
}
