<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateIntegration extends AbstractAttributeTestCase
{
    public function testDateIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_date');
    }

    public function testDateShouldNotHaveAllowedExtensions()
    {
        $this->assertDoesNotHaveAllowedExtensions('pim_catalog_date');
    }

    public function testDateShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_date');
    }

    public function testDateShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_date');
    }

    public function testDateShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_date');
    }

    public function testDateShouldNotHaveAutoOptionSorting()
    {
        $this->assertDoesNotHaveAutoOptionSorting('pim_catalog_date');
    }

    public function testDateShouldNotHaveMaxCharacters()
    {
        $this->assertDoesNotHaveMaxCharacters('pim_catalog_date');
    }

    public function testDateShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_date');
    }

    public function testDateShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_date');
    }

    public function testDateShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_date');
    }

    public function testDateShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_date');
    }

    public function testDateShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_date');
    }

    public function testDateShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_date');
    }

    public function testDateShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_date');
    }

    public function testDateShouldNotHaveAMaxFileSize()
    {
        $this->assertDoesNotHaveAMaxFileSize('pim_catalog_date');
    }
}
