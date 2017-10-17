<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;

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
            'label' => [
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
            ['color', 'weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
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
            ['size', 'sku', 'price'],
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
            'label' => [
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
            ['size', 'price', 'sku', 'unique_attribute'],
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
            'label' => [
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
            'label' => [
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
    }

    /**
     * Validation: An attribute can only be used for one attribute set
     */
    public function testTheAttributeSetAttributeUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'invalid_attribute',
            'family' => 'boots',
            'label' => [
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
            'label' => [
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
            'label' => [
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
            'A variant attribute set cannot have more than 5 attributes',
            $errors->get(2)->getMessage()
        );
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
            'label' => [
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
            'label' => [
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
            'label' => [
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
            'label' => [
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
            'label' => [
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
            'label' => [
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
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "attribute_set_1" expects a valid attribute code. The attribute does not exist, "weather" given.
     */
    public function testAttributesExist()
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant',
            'family' => 'boots',
            'label' => [
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
            'label' => [
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
