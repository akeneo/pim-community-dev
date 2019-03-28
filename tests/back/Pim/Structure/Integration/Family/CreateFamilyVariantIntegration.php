<?php

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Collections\Collection;

class CreateFamilyVariantIntegration extends TestCase
{
    /**
     * Basic test that checks the family variant creation
     */
    public function testTheFamilyVariantCreation()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $this->get('doctrine.orm.entity_manager')->refresh($familyVariant);
        $this->assertNotNull($familyVariant, 'The family variant with the code "family_variant" does not exist');

        $this->assertEquals('boots', $familyVariant->getFamily()->getCode(), 'The family code does not match boots');
        $this->assertEquals(
            ['name', 'manufacturer', 'description'],
            $this->extractAttributeCode($familyVariant->getCommonAttributes()),
            'Common attributes are invalid'
        );

        $variantAttributeSet = $familyVariant->getVariantAttributeSet(1);
        $this->assertEquals(
            ['color'],
            $this->extractAttributeCode($variantAttributeSet->getAxes()),
            'Axis is invalid (level 1)'
        );
        $this->assertEquals(
            ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color', 'color'],
            $this->extractAttributeCode($variantAttributeSet->getAttributes()),
            'Variant attribute are invalid (level 1)'
        );

        $variantAttributeSet = $familyVariant->getVariantAttributeSet(2);
        $this->assertEquals(
            ['size'],
            $this->extractAttributeCode($variantAttributeSet->getAxes()),
            'The axis is invalid (level 2)'
        );
        $this->assertEquals(
            ['sku', 'price', 'size'],
            $this->extractAttributeCode($variantAttributeSet->getAttributes()),
            'Variant attribute are invalid (level 2)'
        );
    }

    public function testUniqueAttributeIsAutomaticallySetAtVariantProductLevel()
    {
        $uniqueAttribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
        $this->get('pim_catalog.updater.attribute')->update($uniqueAttribute, [
            'code' => 'unique_attribute',
            'group' => 'other',
            'unique' => true,
        ]);

        $errors = $this->get('validator')->validate($uniqueAttribute);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.attribute')->save($uniqueAttribute);

        $bootsFamily = $this->get('pim_catalog.repository.family')->findOneByIdentifier('boots');
        $bootsFamily->addAttribute($uniqueAttribute);
        $this->get('pim_catalog.saver.family')->save($bootsFamily);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $this->get('doctrine.orm.entity_manager')->refresh($familyVariant);

        $variantAttributeSet = $familyVariant->getVariantAttributeSet(2);
        $this->assertEquals(
            ['price', 'size', 'sku', 'unique_attribute'],
            $this->extractAttributeCode($variantAttributeSet->getAttributes()),
            'Variant attribute are invalid (level 2)'
        );
    }

    /**
     * Validation: Family variant code is unique
     */
    public function testTheFamilyVariantCodeUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('This value is already used.', $errors->get(0)->getMessage());
        $this->assertSame('code', $errors->get(0)->getPropertyPath());
    }

    public function testFamilyVariantMissingCode()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku', 'price'],
                        'level'=> 2,
                    ]
                ],
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testFamilyVariantBlankCode()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => '',
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku', 'price'],
                        'level'=> 2,
                    ]
                ],
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be blank.', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testFamilyVariantRegexCode()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => '@code',
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku', 'price'],
                        'level'=> 2,
                    ]
                ],
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('Family variant code may contain only letters, numbers and underscores', $violations->get(0)->getMessage());
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
    }

    public function testFamilyVariantTooLongCode()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => 'A_SQL_query_goes_into_a_bar_walks_up_to_two_tables_and_asks__Can_I_join_you___hip_hip_Array_for_the_joke_in_integration_tests',
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku', 'price'],
                        'level'=> 2,
                    ]
                ],
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('code', $violations->get(0)->getPropertyPath());
        $this->assertSame('This value is too long. It should have 100 characters or less.', $violations->get(0)->getMessage());
    }

    public function testCreateFamilyVariantUnknownFamily()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "family" expects a valid family code. The family does not exist, "unknown_family" given');

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => 'familyVariantCode',
                'family' => 'unknown_family',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku', 'price'],
                        'level'=> 2,
                    ]
                ],
            ]
        );
    }

    public function testCreateFamilyVariantMissingFamily()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => 'familyVariantCode',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku', 'price'],
                        'level'=> 2,
                    ]
                ],
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('This value should not be null.', $violations->get(0)->getMessage());
        $this->assertSame('family', $violations->get(0)->getPropertyPath());
    }

    public function testFamilyVariantMissingAttributeSets()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code' => 'newFamilyVariantA1',
                'family' => 'boots',
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('There should be at least one level defined in the family variant', $violations->get(0)->getMessage());
        $this->assertSame('variant_attribute_sets', $violations->get(0)->getPropertyPath());
    }

    public function testCreateFamilyVariantNoLevel()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code' => 'newFamilyVariantA1',
                'family' => 'boots',
                'variant_attribute_sets' => []
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('There should be at least one level defined in the family variant', $violations->get(0)->getMessage());
    }

    public function testCreateFamilyVariantWithVariantAttributeSetAsString()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "variant_attribute_sets" expects an array of objects as data.');

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => 'familyVariantCode',
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => 'color',
            ]
        );
    }

    public function testCreateFamilyVariantWithVariantAttributeSetAsArrayOfString()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "variant_attribute_sets" expects an array of objects as data.');

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code'   => 'familyVariantCode',
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => ['color'],
            ]
        );
    }

    /**
     * Validation: If level of attribute set is not specified it is not set, so validation must return an error.
     */
    public function testTheAttributeSetWithoutLevelSpecified()
    {
        $this->createDefaultFamilyVariant();

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code'                   => 'a_family_variant',
            'family'                 => 'boots',
            'labels'                 => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes'       => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color']
                ]
            ],
        ]);

        $this->assertSame(0, $familyVariant->getVariantAttributeSets()->count());

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('There should be at least one level defined in the family variant', $violations->get(0)->getMessage());
    }

    /**
     * Validation: An attribute can only be used one time as an axis
     */
    public function testTheAttributeSetAxisUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'invalid_axis',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size', 'color'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(2, $errors->count());
        $this->assertEquals(
            'Variant axes must be unique, "color" are used several times in variant attributes sets',
            $errors->get(0)->getMessage()
        );
        $this->assertEquals(
            'Attributes must be unique, "color" are used several times in variant attributes sets',
            $errors->get(1)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
        $this->assertSame('variant_attribute_sets', $errors->get(1)->getPropertyPath());
    }

    public function testTheAttributeSetAttributeUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'invalid_attribute',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'rating', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Attributes must be unique, "rating" are used several times in variant attributes sets',
            $errors->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
    }

    /**
     * Validation: Available attributes for axis are metric, simple select and reference data simple select
     * Validation: Variant axes "%axis%" cannot be localizable, not scopable and not locale specific
     */
    public function testTheAttributeSetAxesType()
    {
        $this->createDefaultFamilyVariant();

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'invalid_axis_type',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['name'],
                    'attributes' => ['side_view', 'rating', 'color', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'weather_conditions'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(2, $errors->count());
        $this->assertEquals(
            'Variant axes "name" must be a boolean, a simple select, a simple reference data or a metric',
            $errors->get(1)->getMessage()
        );

        $this->assertEquals(
            'Variant axes "name" cannot be localizable, not scopable and not locale specific',
            $errors->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
        $this->assertSame('variant_attribute_sets', $errors->get(1)->getPropertyPath());
    }

    public function testCreateFamilyVariantWithIdentifierAsAxis()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code' => 'invalid_axis_type',
                'family' => 'boots',
                'labels' => [
                    'en_US' => 'My family variant'
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['side_view', 'rating', 'color', 'top_view', 'lace_color'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['size', 'sku'],
                        'attributes' => ['sku', 'weather_conditions'],
                        'level'=> 2,
                    ]
                ]
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(2, $violations);
        $this->assertSame('Variant axes "sku" cannot be unique or an identifier', $violations->get(0)->getMessage());
        $this->assertSame(
            'Variant axes "sku" must be a boolean, a simple select, a simple reference data or a metric',
            $violations->get(1)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $violations->get(0)->getPropertyPath());
        $this->assertSame('variant_attribute_sets', $violations->get(1)->getPropertyPath());
    }

    /**
     * Validation: Available attributes for axis are metric, simple select and reference data simple select
     * Validation: Variant axes "%axis%" cannot be localizable, not scopable and not locale specific
     */
    public function testTheNumberOfAttributeSetType()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color', 'size', 'rating', 'lace_color', 'side_view', 'top_view'],
                    'attributes' => ['weather_conditions', 'side_view', 'top_view'],
                    'level'=> 1,
                ],

            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(
            'A variant attribute set cannot have more than 5 axes',
            $errors->get(2)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(2)->getPropertyPath());
    }

    public function testCreateFamilyVariantWithNoAttributeInAxes()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => [],
                    'attributes' => [],
                    'level'=> 1,
                ],

            ],
        ]);

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame(
            'There should be at least one attribute defined as axis for the attribute set for level "1"',
            $violations->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $violations->get(0)->getPropertyPath());
    }

    /**
     * Validation: Available axes must have parents
     */
    public function testTheNumberOfAttributeSetLevel()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color', 'size'],
                    'attributes' => [
                        'weather_conditions',
                        'rating',
                        'side_view',
                        'top_view',
                        'lace_color',
                        'sku',
                        'price',
                    ],
                    'level'=> 2,
                ],
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'There is no variant attribute set for level "1"',
            $errors->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
    }

    public function testCreateFamilyVariantTooManyLevels()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'code' => 'newFamilyVariantA1',
                'family' => 'boots',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view'],
                        'level'=> 1,
                    ],
                    [
                        'axes' => ['lace_color'],
                        'attributes' => ['price'],
                        'level'=> 2,
                    ],
                    [
                        'axes' => ['size'],
                        'attributes' => ['sku'],
                        'level'=> 3,
                    ]
                ]
            ]
        );

        $violations = $this->get('validator')->validate($familyVariant);

        $this->assertCount(1, $violations);
        $this->assertSame('Family variant cannot have more than "2" level', $violations->get(0)->getMessage());
        $this->assertSame('variant_attribute_sets', $violations->get(0)->getPropertyPath());
    }

    /**
     * Validation: Family variant attributes must be present in the family the family variant is attached too
     */
    public function testTheAttributesAreInTheFamily()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color', 'size'],
                    'attributes' => [
                        'weather_conditions',
                        'rating',
                        'side_view',
                        'top_view',
                        'lace_color',
                        'sku',
                        'price',
                        'heel_color',
                    ],
                    'level'=> 1,
                ],
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            '"heel_color" attribute cannot be added to "family_variant" family variant, as it is not an attribute of the "boots" family',
            $errors->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
    }

    /**
     * Validation: Attribute with unique value must be set on the product level
     */
    public function testUniqueAttributesAreAtProductLevel()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->addAttributeToFamily('unique_attribute', 'boots');

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => [
                        'unique_attribute',
                        'weather_conditions',
                        'rating',
                        'side_view',
                        'top_view',
                        'lace_color',
                    ],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Unique attribute "unique_attribute" must be set at the product level',
            $errors->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
    }

    /**
     * Validation: Attribute of identifier type must be set on the product level
     */
    public function testIdentifierAttributesAreAtProductLevel()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['sku', 'weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Unique attribute "sku" must be set at the product level',
            $errors->get(0)->getMessage()
        );
        $this->assertSame('variant_attribute_sets', $errors->get(0)->getPropertyPath());
    }

    /**
     * Validation: Unique or identifier attributes are automatically set on the product level
     */
    public function testUniqueAttributesAreAutomaticallyAtProductLevel()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->addAttributeToFamily('unique_attribute', 'boots');

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(0, $errors->count());
    }

    /**
     * Validation: Attributes must be defined as axes in the same variant attribute set
     */
    public function testAxesDefinedAsAttributeInLevel()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['size', 'weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        $this->assertEquals(2, $errors->count());
        $this->assertEquals(
            'Attribute "size" must be set as attribute in the same variant attribute set it was set as axis',
            $errors->get(0)->getMessage()
        );
        $this->assertEquals(
            'Attributes must be unique, "size" are used several times in variant attributes sets',
            $errors->get(1)->getMessage()
        );
    }

    /**
     * Validation: The attribute set attributes must exists
     */
    public function testAttributesExist()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "attribute_set_1" expects a valid attribute code. The attribute does not exist, "weather" given.');

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);
    }

    /**
     * Validation: The attribute set attributes must exists
     */
    public function testAxisAttributesExist()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "attribute_set_1" expects a valid attribute code. The attribute does not exist, "weather" given.');

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['weather'],
                    'attributes' => ['rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);
    }

    public function testLabelHasExistingLocale()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'klingon' => 'qorDu\''
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level' => 1,
                ]
            ],
        ]);
        $errors = $this->get('validator')->validate($familyVariant);

        $this->assertSame('The locale "klingon" does not exist.', $errors->get(0)->getMessage());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }

    /**
     * Create a family variant with the code family_variant
     *
     * @return FamilyVariantInterface
     */
    private function createDefaultFamilyVariant(): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'=> 1,
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'=> 2,
                ]
            ],
        ]);

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    /**
     * Extract the attribute code from the attribute collection
     *
     * @param Collection $collection
     *
     * @return array
     */
    private function extractAttributeCode(Collection $collection): array
    {
        $codes = $collection->map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();

        return array_values($codes);
    }

    /**
     * @param string $attributeCode
     * @param string $familyCode
     */
    private function addAttributeToFamily(string $attributeCode, string $familyCode): void
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
        $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);

        $attribute->setCode($attributeCode);
        $attribute->setUnique(true);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $family->addAttribute($attribute);
        $this->get('pim_catalog.saver.family')->save($family);
    }
}
