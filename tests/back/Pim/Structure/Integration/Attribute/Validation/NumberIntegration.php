<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NumberIntegration extends AbstractAttributeTestCase
{
    public function testNumberIsNotRequired()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'required'         => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'               => 'new_number',
                'type'               => 'pim_catalog_number',
                'group'              => 'attributeGroupA',
                'decimals_allowed'   => true,
                'negative_allowed'   => false,
                'allowed_extensions' => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAMetricFamily()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'metric_family'    => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testDoesNotHaveADefaultMetricUnit()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_number',
                'type'                => 'pim_catalog_number',
                'group'               => 'attributeGroupA',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'default_metric_unit' => 'KILOWATT',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAReferenceDataName()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_number',
                'type'                => 'pim_catalog_number',
                'group'               => 'attributeGroupA',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'reference_data_name' => 'color',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot be linked to reference data.', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAutoOptionSorting()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_number',
                'type'                => 'pim_catalog_number',
                'group'               => 'attributeGroupA',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'auto_option_sorting' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot have options.', $violations->get(0)->getMessage());
        $this->assertSame('auto_option_sorting', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveMaxCharacters()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'max_characters'   => '42',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAValidationRule()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'validation_rule'  => 'email',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAValidationRegexp()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'              => 'new_number',
                'type'              => 'pim_catalog_number',
                'group'             => 'attributeGroupA',
                'decimals_allowed'  => true,
                'negative_allowed'  => false,
                'validation_regexp' => '/[a-z]+/',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveWysiwygEnabled()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'wysiwyg_enabled'  => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveADateMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'date_min'         => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveADateMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'date_max'         => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    public function testNumberShouldNotHaveAMaxFileSize()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_number',
                'type'             => 'pim_catalog_number',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => false,
                'max_file_size'    => 1024,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }
}
