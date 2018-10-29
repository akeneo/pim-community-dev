<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionIntegration extends AbstractAttributeTestCase
{
    public function testPriceCollectionIsNotRequired()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'required'         => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionIsNotUnique()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'unique'           => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t have unique value', $violations->get(0)->getMessage());
        $this->assertSame('unique', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'               => 'new_price',
                'type'               => 'pim_catalog_price_collection',
                'group'              => 'attributeGroupA',
                'decimals_allowed'   => true,
                'allowed_extensions' => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAMetricFamily()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'metric_family'    => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveADefaultMetricUnit()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_price',
                'type'                => 'pim_catalog_price_collection',
                'group'               => 'attributeGroupA',
                'decimals_allowed'    => true,
                'default_metric_unit' => 'METER',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAReferenceDataName()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_price',
                'type'                => 'pim_catalog_price_collection',
                'group'               => 'attributeGroupA',
                'decimals_allowed'    => true,
                'reference_data_name' => 'color',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot be linked to reference data.', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAutoOptionSorting()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_price',
                'type'                => 'pim_catalog_price_collection',
                'group'               => 'attributeGroupA',
                'decimals_allowed'    => true,
                'auto_option_sorting' => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot have options.', $violations->get(0)->getMessage());
        $this->assertSame('auto_option_sorting', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveMaxCharacters()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'max_characters'   => 42,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAValidationRule()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'validation_rule'  => 'email',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAValidationRegexp()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'              => 'new_price',
                'type'              => 'pim_catalog_price_collection',
                'group'             => 'attributeGroupA',
                'decimals_allowed'  => true,
                'validation_regexp' => '/[a-z]+/',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveWysiwygEnabled()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'wysiwyg_enabled'  => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveNegativeAllowed()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'negative_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('negativeAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveADateMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'date_min'         => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveADateMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'date_max'         => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    public function testPriceCollectionShouldNotHaveAMaxFileSize()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_price',
                'type'             => 'pim_catalog_price_collection',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
                'max_file_size'    => 1024,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }
}
