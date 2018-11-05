<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextIntegration extends AbstractAttributeTestCase
{
    public function testTextIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_text');
    }

    public function testTextShouldNotHaveAllowedExtensions()
    {
        $this->assertDoesNotHaveAllowedExtensions('pim_catalog_text');
    }

    public function testTextShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_text');
    }

    public function testTextShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_text');
    }

    public function testTextShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_text');
    }

    public function testTextShouldNotHaveAutoOptionSorting()
    {
        $this->assertDoesNotHaveAutoOptionSorting('pim_catalog_text');
    }

    public function testTextMaxCharactersIsNotGreaterThan()
    {
        $this->assertMaxCharactersIsNotGreaterThan('pim_catalog_text', 255);
    }

    public function testTextValidationRule()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'            => 'new_text',
                'type'            => 'pim_catalog_text',
                'group'           => 'attributeGroupA',
                'validation_rule' => 'oyster',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('The value you selected is not a valid choice.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    public function testTextValidationRegexp()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'              => 'new_text',
                'type'              => 'pim_catalog_text',
                'group'             => 'attributeGroupA',
                'validation_rule'   => 'regexp',
                'validation_regexp' => '/[a-z]+',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This regular expression is not valid.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_text');
    }

    public function testTextShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_text');
    }

    public function testTextShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_text');
    }

    public function testTextShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_text');
    }

    public function testTextShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_text');
    }

    public function testTextShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_text');
    }

    public function testTextShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_text');
    }

    public function testTextShouldNotHaveAMaxFileSize()
    {
        $this->assertDoesNotHaveAMaxFileSize('pim_catalog_text');
    }
}
