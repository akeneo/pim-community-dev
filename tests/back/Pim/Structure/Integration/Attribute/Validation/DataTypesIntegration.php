<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * NB:
 * - "type" type cannot be tested because an exception will be thrown by the updater if the data is not a valid attribute
 * type.
 * - "dateMin" and "dateMax" types cannot be tested because of the date format validation inside the updater.
 * - "availableLocales" type cannot be tested for the same reason.
 * - "allowedExtensions" type cannot be tested because it's transformed multiple times by converter, updater and inside
 * the entity (explode, implode, etc.).
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DataTypesIntegration extends AbstractAttributeTestCase
{
    public function testCodeIsString()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'  => 1977,
                'type'  => 'pim_catalog_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type string.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testLocalizableIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'        => 'new_text',
                'type'        => 'pim_catalog_text',
                'group'       => 'attributeGroupA',
                'localizable' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('localizable', $violations->get(0)->getPropertyPath());
    }

    public function testScopableIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'scopable' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('scopable', $violations->get(0)->getPropertyPath());
    }

    public function testUseableAsGridFilterIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                   => 'new_text',
                'type'                   => 'pim_catalog_text',
                'group'                  => 'attributeGroupA',
                'useable_as_grid_filter' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('useableAsGridFilter', $violations->get(0)->getPropertyPath());
    }

    public function testWysiwygEnabledIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'            => 'new_textarea',
                'type'            => 'pim_catalog_textarea',
                'group'           => 'attributeGroupA',
                'wysiwyg_enabled' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testDecimalsAllowedIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_textarea',
                'type'             => 'pim_catalog_textarea',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('decimalsAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testNegativeAllowedIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_textarea',
                'type'             => 'pim_catalog_textarea',
                'group'            => 'attributeGroupA',
                'negative_allowed' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('negativeAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testIdentifierMaxCharacterIsNumeric()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'           => 'new_textarea',
                'type'           => 'pim_catalog_textarea',
                'group'          => 'attributeGroupA',
                'max_characters' => 'capybara',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type numeric.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testSortOrderIsNumeric()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_text',
                'type'       => 'pim_catalog_text',
                'group'      => 'attributeGroupA',
                'sort_order' => 'jambon',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type numeric.', $violations->get(0)->getMessage());
        $this->assertSame('sortOrder', $violations->get(0)->getPropertyPath());
    }

    public function testRequiredIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'required' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testUniqueIsBoolean()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'   => 'new_text',
                'type'   => 'pim_catalog_text',
                'group'  => 'attributeGroupA',
                'unique' => 'true',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type bool.', $violations->get(0)->getMessage());
        $this->assertSame('unique', $violations->get(0)->getPropertyPath());
    }

    public function testMaxFileSizeIsNumeric()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'          => 'new_img',
                'type'          => 'pim_catalog_image',
                'group'         => 'attributeGroupA',
                'max_file_size' => 'jambon',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type numeric.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }

    public function testValidationRegexpIsString()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'              => 'new_text',
                'type'              => 'pim_catalog_text',
                'group'             => 'attributeGroupA',
                'validation_regexp' => 50.1,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type string.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testNumberMinIsNumeric()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_number',
                'type'       => 'pim_catalog_date',
                'group'      => 'attributeGroupA',
                'number_min' => 'capybara',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type numeric.', $violations->get(0)->getMessage());
        $this->assertSame('numberMin', $violations->get(0)->getPropertyPath());
    }

    public function testNumberMaxIsNumeric()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_number',
                'type'       => 'pim_catalog_date',
                'group'      => 'attributeGroupA',
                'number_max' => 'capybara',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type numeric.', $violations->get(0)->getMessage());
        $this->assertSame('numberMax', $violations->get(0)->getPropertyPath());
    }

    public function testMetricFamilyIsString()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 50.1,
                'default_metric_unit' => 'CENTIMETER',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type string.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testDefaultMetricUnitIsString()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be of type string.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }
}
