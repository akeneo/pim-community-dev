<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SimpleSelectIntegration extends AbstractAttributeTestCase
{
    public function testSimpleSelectIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_simpleselect');
    }

    public function testSimpleSelectIsNotUnique()
    {
        $this->assertNotUnique('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveAllowedExtensions()
    {
        $this->assertDoesNotHaveAllowedExtensions('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveMaxCharacters()
    {
        $this->assertDoesNotHaveMaxCharacters('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_simpleselect');
    }

    public function testSimpleSelectShouldNotHaveAMaxFileSize()
    {
        $this->assertDoesNotHaveAMaxFileSize('pim_catalog_simpleselect');
    }
}
