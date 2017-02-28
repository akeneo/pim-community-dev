<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Validation\Attribute;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextIntegration extends AbstractAttributeTestCase
{
    public function testTextIsNotRequired()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'required' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'               => 'new_text',
                'type'               => 'pim_catalog_text',
                'group'              => 'attributeGroupA',
                'allowed_extensions' => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveAMetricFamily()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'          => 'new_text',
                'type'          => 'pim_catalog_text',
                'group'         => 'attributeGroupA',
                'metric_family' => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveADefaultMetricUnit()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_text',
                'type'                => 'pim_catalog_text',
                'group'               => 'attributeGroupA',
                'default_metric_unit' => 'KILOWATT',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveAReferenceDataName()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_text',
                'type'                => 'pim_catalog_text',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot be linked to reference data.', $violations->get(0)->getMessage());
        $this->assertSame('properties', $violations->get(0)->getPropertyPath());
        $this->assertSame('reference_data_name', $violations->get(0)->getConstraint()->payload['standardPropertyName']);
    }

    public function testTextMaxCharactersIsNotGreaterThan()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'           => 'new_text',
                'type'           => 'pim_catalog_text',
                'group'          => 'attributeGroupA',
                'max_characters' => 256,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be less than or equal to 255.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
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
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'            => 'new_text',
                'type'            => 'pim_catalog_text',
                'group'           => 'attributeGroupA',
                'wysiwyg_enabled' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveANumberMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_text',
                'type'       => 'pim_catalog_text',
                'group'      => 'attributeGroupA',
                'number_min' => 13,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMin', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveANumberMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_text',
                'type'       => 'pim_catalog_text',
                'group'      => 'attributeGroupA',
                'number_max' => 13,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMax', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveDecimalsAllowed()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_text',
                'type'             => 'pim_catalog_text',
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('decimalsAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveNegativeAllowed()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_text',
                'type'             => 'pim_catalog_text',
                'group'            => 'attributeGroupA',
                'negative_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('negativeAllowed', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveADateMin()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'date_min' => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveADateMax()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'date_max' => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    public function testTextShouldNotHaveAMaxFileSize()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'          => 'new_text',
                'type'          => 'pim_catalog_text',
                'group'         => 'attributeGroupA',
                'max_file_size' => 1024,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }
}
