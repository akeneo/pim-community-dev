<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MultiSelectIntegration extends AbstractAttributeTestCase
{
    public function testMultiSelectIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_multiselect');
    }

    public function testMultiSelectIsNotUnique()
    {
        $this->assertNotUnique('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveAllowedExtensions()
    {
        $this->assertDoesNotHaveAllowedExtensions('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveMaxCharacters()
    {
        $this->assertDoesNotHaveMaxCharacters('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_multiselect');
    }

    public function testMultiSelectShouldNotHaveAMaxFileSize()
    {
        $this->assertDoesNotHaveAMaxFileSize('pim_catalog_multiselect');
    }
}
