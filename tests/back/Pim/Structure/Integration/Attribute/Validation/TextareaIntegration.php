<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextareaIntegration extends AbstractAttributeTestCase
{
    public function testTextareaIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_textarea');
    }

    public function testTextareaIsNotUnique()
    {
        $this->assertNotUnique('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveAllowedExtensions()
    {
        $this->assertDoesNotHaveAllowedExtensions('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveAutoOptionSorting()
    {
        $this->assertDoesNotHaveAutoOptionSorting('pim_catalog_textarea');
    }

    public function testTextareaMaxCharactersIsNotGreaterThan()
    {
        $this->assertMaxCharactersIsNotGreaterThan('pim_catalog_textarea', 65535);
    }

    public function testTextareaShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_textarea');
    }

    public function testTextareaShouldNotHaveAMaxFileSize()
    {
        $this->assertDoesNotHaveAMaxFileSize('pim_catalog_textarea');
    }
}
