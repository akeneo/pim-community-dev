<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @group ce
 */
class ReferenceDataSimpleSelectIntegration extends AbstractAttributeTestCase
{
    public function testReferenceDataSimpleSelectIsNotRequired()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'required'            => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectIsNotUnique()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'unique'              => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t have unique value', $violations->get(0)->getMessage());
        $this->assertSame('unique', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'allowed_extensions'  => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveAMetricFamily()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'metric_family'       => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveADefaultMetricUnit()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'default_metric_unit' => 'KILOWATT',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectHasAReferenceDataName()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectHasAValidReferenceDataName()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'invalid',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('Reference data "invalid" does not exist. Allowed values are: fabrics, color', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataMultiSelectShouldNotHaveAutoOptionSorting()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'auto_option_sorting' => false,
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
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'max_characters'      => 42,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveAValidationRule()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'validation_rule'     => 'email',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveAValidationRegexp()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'validation_regexp'   => '/[a-z]+/',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveWysiwygEnabled()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'wysiwyg_enabled'     => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveANumberMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'number_min'          => 1,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMin', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveANumberMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'number_max'          => 42,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMax', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveDecimalsAllowed()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'decimals_allowed'    => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('decimalsAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveNegativeAllowed()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'negative_allowed'    => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('negativeAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveADateMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'date_min'            => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveADateMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'date_max'            => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    public function testReferenceDataSimpleSelectShouldNotHaveAMaxFileSize()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
                'max_file_size'       => 1024,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }
}
