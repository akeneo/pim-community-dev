<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IdentifierIntegration extends AbstractAttributeTestCase
{
    public function testMultipleIdentifiersCanBeCreated(): void
    {
        $attribute1 = $this->createAttribute();

        $this->updateAttribute(
            $attribute1,
            [
                'code'                   => 'second_identifier',
                'type'                   => 'pim_catalog_identifier',
                'group'                  => 'attributeGroupA',
                'useable_as_grid_filter' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute1);
        $this->assertCount(0, $violations);

        $attribute2 = $this->createAttribute();

        $this->updateAttribute(
            $attribute2,
            [
                'code'                   => 'third_identifier',
                'type'                   => 'pim_catalog_identifier',
                'group'                  => 'attributeGroupA',
                'useable_as_grid_filter' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute2);
        $this->assertCount(0, $violations);
    }

    public function testIdentifierIsUsableAsGridFilter(): void
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

    public function testIdentifierIsRequired(): void
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

    public function testIdentifierShouldNotHaveAllowedExtensions(): void
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

    public function testIdentifierShouldNotHaveAMetricFamily(): void
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

    public function testIdentifierShouldNotHaveADefaultMetricUnit(): void
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

    public function testIdentifierShouldNotHaveAReferenceDataName(): void
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

    public function testIdentifierShouldNotHaveAutoOptionSorting(): void
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

    public function testIdentifierShouldNotHaveAvailableLocales(): void
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

    public function testIdentifierMaxCharacterIsNotDecimal(): void
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

    public function testIdentifierMaxCharacterIsPositive(): void
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

    public function testIdentifierMaxCharactersIsNotGreaterThan(): void
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

    public function testIdentifierShouldNotHaveADateMin(): void
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

    public function testIdentifierShouldNotHaveADateMax(): void
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

    public function testIdentifierShouldNotHaveAMaxFileSize(): void
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

    public function testIdentifierValidationRule(): void
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

    public function testIdentifierValidationRegexp(): void
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

    public function testIdentifierShouldNotHaveANumberMin(): void
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

    public function testIdentifierShouldNotHaveANumberMax(): void
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

    public function testIdentifierShouldNotHaveWysiwygEnabled(): void
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

    public function testIdentifierShouldNotHaveDecimalsAllowed(): void
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

    public function testIdentifierShouldNotHaveNegativeAllowed(): void
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

    public function testMultipleIdentifiersCantBeCreatedOverLimit(): void
    {
        $limit = 10;
        for ($i = 1; $i < $limit; $i++) {
            $tempAttribute = $this->createAttribute();
            $this->updateAttribute(
                $tempAttribute,
                [
                    'code'                   => 'identifier'.$i,
                    'type'                   => 'pim_catalog_identifier',
                    'group'                  => 'attributeGroupA',
                    'useable_as_grid_filter' => true,
                ]
            );
            $violations = $this->validateAttribute($tempAttribute);
            $this->assertCount(0, $violations);
            $this->saveAttribute($tempAttribute);

            $this->assertNotNull($this->getAttribute('identifier'.$i));
        }

        $attributeOverLimit = $this->createAttribute();

        $this->updateAttribute(
            $attributeOverLimit,
            [
                'code'                   => 'over_limit_identifier',
                'type'                   => 'pim_catalog_identifier',
                'group'                  => 'attributeGroupA',
                'useable_as_grid_filter' => true,
            ]
        );

        $violations = $this->validateAttribute($attributeOverLimit);
        $this->assertCount(1, $violations);
        $this->assertSame('Limit of "10" identifier attributes is reached. The following identifier has not been created ', $violations->get(0)->getMessage());
    }
}
