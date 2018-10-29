<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IdentifierIntegration extends AbstractAttributeTestCase
{
    public function testSingleIdentifier()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                   => 'new_identifier',
                'type'                   => 'pim_catalog_identifier',
                'group'                  => 'attributeGroupA',
                'useable_as_grid_filter' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('An identifier attribute already exists.', $violations->get(0)->getMessage());
        $this->assertSame('type', $violations->get(0)->getConstraint()->payload['standardPropertyName']);
    }

    public function testIdentifierIsUsableAsGridFilter()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'useable_as_grid_filter' => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame(
            '"sku" is an identifier attribute, it must be usable as grid filter',
            $violations->get(0)->getMessage()
        );
        $this->assertSame('useableAsGridFilter', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierIsRequired()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'required' => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type must be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveAllowedExtensions()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'allowed_extensions' => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveAMetricFamily()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'metric_family' => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveADefaultMetricUnit()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'default_metric_unit' => 'KILOWATT',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveAReferenceDataName()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'reference_data_name' => 'color',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot be linked to reference data.', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveAutoOptionSorting()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'auto_option_sorting' => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot have options.', $violations->get(0)->getMessage());
        $this->assertSame('auto_option_sorting', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveAvailableLocales()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'available_locales' => ['de_DE'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot have available locales.', $violations->get(0)->getMessage());
        $this->assertSame('availableLocales', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierMaxCharacterIsNotDecimal()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'max_characters' => '50.1',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be a decimal.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierMaxCharacterIsPositive()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'max_characters' => '-5',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be greater than or equal to 0.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierMaxCharactersIsNotGreaterThan()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'max_characters' => '256',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be less than or equal to 255.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveADateMin()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'date_min' => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveADateMax()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'date_max' => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveAMaxFileSize()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'max_file_size' => '666',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierValidationRule()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'validation_rule' => 'email',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('The value you selected is not a valid choice.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierValidationRegexp()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'validation_rule'   => 'regexp',
                'validation_regexp' => '/[a-z]+',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This regular expression is not valid.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveANumberMin()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'number_min' => 13,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMin', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveANumberMax()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'number_max' => 13,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMax', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveWysiwygEnabled()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'wysiwyg_enabled' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveDecimalsAllowed()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'decimals_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('decimalsAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierShouldNotHaveNegativeAllowed()
    {
        $attribute = $this->getAttribute('sku');

        $this->updateAttribute(
            $attribute,
            [
                'negative_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('negativeAllowed', $violations->get(0)->getPropertyPath());
    }
}
