<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricIntegration extends AbstractAttributeTestCase
{
    public function testMetricIsNotRequired()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'required'            => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testMetricIsNotUnique()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'unique'              => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t have unique value', $violations->get(0)->getMessage());
        $this->assertSame('unique', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'allowed_extensions'  => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testMetricHasValidMetricFamily()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'invalid',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('Please specify a valid metric family', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testMetricHasValidDefaultMetricUnit()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'INVALID',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('Please specify a valid metric unit', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveAReferenceDataName()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
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

    public function testMetricShouldNotHaveAutoOptionSorting()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
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

    public function testMetricShouldNotHaveMaxCharacters()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'max_characters'      => '42',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveAValidationRule()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'validation_rule'     => 'email',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveAValidationRegexp()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'validation_regexp'   => '/[a-z]+/',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveWysiwygEnabled()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'wysiwyg_enabled'     => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveADateMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'date_min'            => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveADateMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'date_max'            => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    public function testMetricShouldNotHaveAMaxFileSize()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_metric',
                'type'                => 'pim_catalog_metric',
                'group'               => 'attributeGroupA',
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER',
                'decimals_allowed'    => true,
                'negative_allowed'    => false,
                'max_file_size'       => 1024,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }
}
