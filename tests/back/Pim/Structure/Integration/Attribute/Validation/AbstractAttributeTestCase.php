<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeTestCase extends TestCase
{
    protected function assertNotRequired($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_attribute',
                'type'     => $type,
                'group'    => 'attributeGroupA',
                'required' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t be required', $violations->get(0)->getMessage());
        $this->assertSame('required', $violations->get(0)->getPropertyPath());
    }

    protected function assertNotUnique($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'   => 'new_attribute',
                'type'   => $type,
                'group'  => 'attributeGroupA',
                'unique' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute type can\'t have unique value', $violations->get(0)->getMessage());
        $this->assertSame('unique', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveAllowedExtensions($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'               => 'new_attribute',
                'type'               => $type,
                'group'              => 'attributeGroupA',
                'allowed_extensions' => ['gif', 'png'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be blank.', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveAMetricFamily($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'          => 'new_attribute',
                'type'          => $type,
                'group'         => 'attributeGroupA',
                'metric_family' => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveADefaultMetricUnit($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_attribute',
                'type'                => $type,
                'group'               => 'attributeGroupA',
                'default_metric_unit' => 'KILOWATT',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('defaultMetricUnit', $violations->get(0)->getPropertyPath());
    }

    public function assertDoesNotHaveAReferenceDataName($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_attribute',
                'type'                => $type,
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'color',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot be linked to reference data.', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function assertDoesNotHaveAutoOptionSorting($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_attribute',
                'type'                => $type,
                'group'               => 'attributeGroupA',
                'auto_option_sorting' => false,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This attribute cannot have options.', $violations->get(0)->getMessage());
        $this->assertSame('auto_option_sorting', $violations->get(0)->getPropertyPath());
    }

    public function assertDoesNotHaveMaxCharacters($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'           => 'new_attribute',
                'type'           => $type,
                'group'          => 'attributeGroupA',
                'max_characters' => 42,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    protected function assertMaxCharactersIsNotGreaterThan($type, $limit)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'           => 'new_attribute',
                'type'           => $type,
                'group'          => 'attributeGroupA',
                'max_characters' => $limit + 1,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame(sprintf('This value should be less than or equal to %d.', $limit), $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveAValidationRule($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'            => 'new_attribute',
                'type'            => $type,
                'group'           => 'attributeGroupA',
                'validation_rule' => 'email',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRule', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveAValidationRegexp($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'              => 'new_attribute',
                'type'              => $type,
                'group'             => 'attributeGroupA',
                'validation_regexp' => '/[a-z]+/',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('validationRegexp', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveWysiwygEnabled($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'            => 'new_attribute',
                'type'            => $type,
                'group'           => 'attributeGroupA',
                'wysiwyg_enabled' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('wysiwygEnabled', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveANumberMin($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_attribute',
                'type'       => $type,
                'group'      => 'attributeGroupA',
                'number_min' => 13,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMin', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveANumberMax($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_attribute',
                'type'       => $type,
                'group'      => 'attributeGroupA',
                'number_max' => 13,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('numberMax', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveDecimalsAllowed($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_attribute',
                'type'             => $type,
                'group'            => 'attributeGroupA',
                'decimals_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('decimalsAllowed', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveNegativeAllowed($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => 'new_attribute',
                'type'             => $type,
                'group'            => 'attributeGroupA',
                'negative_allowed' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('negativeAllowed', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveADateMin($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_attribute',
                'type'     => $type,
                'group'    => 'attributeGroupA',
                'date_min' => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMin', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveADateMax($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_attribute',
                'type'     => $type,
                'group'    => 'attributeGroupA',
                'date_max' => '2015-11-24',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('dateMax', $violations->get(0)->getPropertyPath());
    }

    protected function assertDoesNotHaveAMaxFileSize($type)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'          => 'new_attribute',
                'type'          => $type,
                'group'         => 'attributeGroupA',
                'max_file_size' => 1024,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be null.', $violations->get(0)->getMessage());
        $this->assertSame('maxFileSize', $violations->get(0)->getPropertyPath());
    }

    /**
     * @param $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        return $this->get('pim_catalog.repository.attribute')->findOneByCode($code);
    }

    /**
     * @return AttributeInterface
     */
    protected function createAttribute()
    {
        return $this->get('pim_catalog.factory.attribute')->create();
    }

    /**
     * @param AttributeInterface $attribute
     * @param array                                                    $data
     */
    protected function updateAttribute(AttributeInterface $attribute, array $data)
    {
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateAttribute(AttributeInterface $attribute)
    {
        return $this->get('validator')->validate($attribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
