<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @group ce
 */
class GlobalConstraintsIntegration extends AbstractAttributeTestCase
{
    public function testCodeIsUnique()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'  => 'a_text',
                'type'  => 'pim_catalog_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value is already used.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testIsReferenceDataConfigured()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'                => 'new_ref_data',
                'type'                => 'pim_reference_data_simpleselect',
                'group'               => 'attributeGroupA',
                'reference_data_name' => 'brand',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('Reference data "brand" does not exist. Allowed values are: fabrics, color', $violations->get(0)->getMessage());
        $this->assertSame('reference_data_name', $violations->get(0)->getPropertyPath());
    }

    public function testCodeIsImmutable()
    {
        $attribute = $this->getAttribute('a_metric');

        $this->updateAttribute(
            $attribute,
            [
                'code' => 'jambon',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testTypeIsImmutable()
    {
        $attribute = $this->getAttribute('a_metric');

        $this->updateAttribute(
            $attribute,
            [
                'type' => 'pim_catalog_date',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violations->get(0)->getMessage());
        $this->assertSame('type', $violations->get(0)->getPropertyPath());
    }

    public function testScopableIsImmutable()
    {
        $attribute = $this->getAttribute('a_metric');

        $this->updateAttribute(
            $attribute,
            [
                'scopable' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violations->get(0)->getMessage());
        $this->assertSame('scopable', $violations->get(0)->getPropertyPath());
    }

    public function testLocalizableIsImmutable()
    {
        $attribute = $this->getAttribute('a_metric');

        $this->updateAttribute(
            $attribute,
            [
                'localizable' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violations->get(0)->getMessage());
        $this->assertSame('localizable', $violations->get(0)->getPropertyPath());
    }

    public function testMetricFamilyIsImmutable()
    {
        $attribute = $this->getAttribute('a_metric');

        $this->updateAttribute(
            $attribute,
            [
                'metric_family' => 'Length',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violations->get(0)->getMessage());
        $this->assertSame('metricFamily', $violations->get(0)->getPropertyPath());
    }

    public function testTypeIsNotBlank()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'  => 'a_new_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violations->get(0)->getMessage());
        $this->assertSame('type', $violations->get(0)->getPropertyPath());
    }

    public function testCodeIsNotBlank()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'type'  => 'pim_catalog_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testGroupIsNotBlank()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code' => 'a_new_text',
                'type' => 'pim_catalog_text',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violations->get(0)->getMessage());
        $this->assertSame('group', $violations->get(0)->getPropertyPath());
    }

    public function testCodeMatchesRegexp()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'  => 'amazing code',
                'type'  => 'pim_catalog_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('Attribute code may contain only letters, numbers and underscore', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testCodeMaxLength()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'  => 'really_very_loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong_code',
                'type'  => 'pim_catalog_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value is too long. It should have 255 characters or less.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    /**
     * @dataProvider reservedCodesProvider
     */
    public function testReservedCode($code)
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'  => $code,
                'type'  => 'pim_catalog_text',
                'group' => 'attributeGroupA',
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This code is not available', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function reservedCodesProvider()
    {
        return [
            ['id'], ['associations'], ['associationTypes'], ['category'], ['categoryId'], ['categories'],
            ['completeness'], ['enabled'], ['family'], ['FAMILY'], ['FamilY'], ['groups'], ['products'], ['scope'], ['treeId'], ['values'],
            ['my_groups'], ['my_products'], ['attributes']
        ];
    }

    public function testLocalizableIsNotNull()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'        => 'new_text',
                'type'        => 'pim_catalog_text',
                'group'       => 'attributeGroupA',
                'localizable' => null,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be null.', $violations->get(0)->getMessage());
        $this->assertSame('localizable', $violations->get(0)->getPropertyPath());
    }

    public function testNotLocalizableIfAttributeIsUnique()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'        => 'new_text',
                'type'        => 'pim_catalog_text',
                'group'       => 'attributeGroupA',
                'unique'      => true,
                'localizable' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('A unique attribute can not be localizable', $violations->get(0)->getMessage());
        $this->assertSame('localizable', $violations->get(0)->getPropertyPath());
    }

    public function testScopableIsNotNull()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'scopable' => null,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be null.', $violations->get(0)->getMessage());
        $this->assertSame('scopable', $violations->get(0)->getPropertyPath());
    }

    public function testNotScopableIfAttributeIsUnique()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'     => 'new_text',
                'type'     => 'pim_catalog_text',
                'group'    => 'attributeGroupA',
                'unique'   => true,
                'scopable' => true,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('A unique attribute can not be scopable', $violations->get(0)->getMessage());
        $this->assertSame('scopable', $violations->get(0)->getPropertyPath());
    }

    public function testSortOrderIsNotNull()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_text',
                'type'       => 'pim_catalog_text',
                'group'      => 'attributeGroupA',
                'sort_order' => null,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be null.', $violations->get(0)->getMessage());
        $this->assertSame('sortOrder', $violations->get(0)->getPropertyPath());
    }

    public function testSortOrderIsPositive()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_text',
                'type'       => 'pim_catalog_text',
                'group'      => 'attributeGroupA',
                'sort_order' => -1,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be greater than or equal to 0.', $violations->get(0)->getMessage());
        $this->assertSame('sortOrder', $violations->get(0)->getPropertyPath());
    }

    public function testSortOrderDoesNotHaveDecimals()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'       => 'new_text',
                'type'       => 'pim_catalog_text',
                'group'      => 'attributeGroupA',
                'sort_order' => 2.5,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be a decimal.', $violations->get(0)->getMessage());
        $this->assertSame('sortOrder', $violations->get(0)->getPropertyPath());
    }

    public function testLabelsMaxLength()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'   => 'new_text',
                'type'   => 'pim_catalog_text',
                'group'  => 'attributeGroupA',
                'labels' => [
                    'en_US' => 'One hundred and nine character long label in english language for new text attribute that should not be valid'
                ]
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value is too long. It should have 100 characters or less.', $violations->get(0)->getMessage());
        $this->assertSame('translations[0].label', $violations->get(0)->getPropertyPath());
        $this->assertSame('labels', $violations->get(0)->getConstraint()->payload['standardPropertyName']);
    }

    public function testLabelsLocales()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'   => 'new_text',
                'type'   => 'pim_catalog_text',
                'group'  => 'attributeGroupA',
                'labels' => [
                    'ab_CD' => 'New text'
                ]
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('The locale "ab_CD" does not exist.', $violations->get(0)->getMessage());
        $this->assertSame('translations[0].locale', $violations->get(0)->getPropertyPath());
        $this->assertSame('labels', $violations->get(0)->getConstraint()->payload['standardPropertyName']);
    }

    public function testMaxCharactersDoesNotHaveDecimals()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'           => 'new_text',
                'type'           => 'pim_catalog_text',
                'group'          => 'attributeGroupA',
                'max_characters' => 50.1,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be a decimal.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }

    public function testMaxCharactersIsPositive()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'           => 'new_text',
                'type'           => 'pim_catalog_text',
                'group'          => 'attributeGroupA',
                'max_characters' => -10,
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should be greater than or equal to 0.', $violations->get(0)->getMessage());
        $this->assertSame('maxCharacters', $violations->get(0)->getPropertyPath());
    }
}
