<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Webmozart\Assert\Assert;

class FamilyVariantIntegration extends TestCase
{
    /**
     * Basic test that checks the family variant creation
     */
    public function testTheFamilyVariantCreation()
    {
        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'family_variant',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price']
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.family_variant')->save($variantFamily);

        /** @var FamilyVariantInterface $variantFamily */
        $variantFamily = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('family_variant');
        $this->assertNotNull($variantFamily, 'The family variant with the code "family_variant" does not exist');

        $this->assertEquals('boots', $variantFamily->getFamily()->getCode(), 'The family code does not match boots');
        $this->assertEquals(
            ['name', 'manufacturer', 'description'],
            $this->extractAttributeCode($variantFamily->getCommonAttributes()),
            'Common attributes are invalid'
        );

        $variantAttributeSet = $variantFamily->getVariantAttributeSet(1);
        $this->assertEquals(
            ['color'],
            $this->extractAttributeCode($variantAttributeSet->getAxes()),
            'Axis is invalid (level 1)'
        );
        $this->assertEquals(
            ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
            $this->extractAttributeCode($variantAttributeSet->getAttributes()),
            'Variant attribute are invalid (level 1)'
        );

        $variantAttributeSet = $variantFamily->getVariantAttributeSet(2);
        $this->assertEquals(
            ['size'],
            $this->extractAttributeCode($variantAttributeSet->getAxes()),
            'The axis is invalid (level 2)'
        );
        $this->assertEquals(
            ['sku', 'price'],
            $this->extractAttributeCode($variantAttributeSet->getAttributes()),
            'Variant attribute are invalid (level 2)'
        );
    }

    /**
     * Validation: Family variant code is unique
     */
    function testTheFamilyVariantCodeUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'family_variant',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price']
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('This value is already used.', $errors->get(0)->getMessage());
    }

    /**
     * Validation: An attribute can only be used one time as an axis
     */
    function testTheAttributeSetAxisUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'invalid_axis',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size', 'color'],
                    'attributes' => ['sku', 'price']
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('Variant axes must be unique, "color" are used several times in variant attributes sets', $errors->get(0)->getMessage());
    }

    /**
     * Validation: An attribute can only be used for one attribute set
     */
    function testTheAttributeSetAttributeUniqueness()
    {
        $this->createDefaultFamilyVariant();

        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'invalid_attribute',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'rating', 'price']
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('Attributes must be unique, "rating" are used several times in variant attributes sets', $errors->get(0)->getMessage());
    }

    /**
     * Validation: Available attributes for axis are metric, simple select and reference data simple select
     * Validation: Variant axes "%axis%" cannot be localizable, not scopable and not locale specific
     */
    function testTheAttributeSetAxesType()
    {
        $this->createDefaultFamilyVariant();

        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'invalid_axis_type',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['name'],
                    'attributes' => ['side_view', 'rating', 'color', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'weather_conditions']
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
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
    function testTheNumberOfAttributeSetType()
    {
        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'family_variant',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color', 'size', 'rating', 'lace_color', 'side_view', 'top_view'],
                    'attributes' => ['weather_conditions', 'side_view', 'top_view']
                ],

            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
        $this->assertEquals(
            'A variant attribute set cannot have more than 5 attributes',
            $errors->get(2)->getMessage()
        );
    }

    /**
     * un attribute ne peut pas être un arbre et vise versa
     * da
     */
    public function testTODO()
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getFunctionalCatalog('footwear')]);
    }

    /**
     * Create a family variant with the code family_variant
     *
     * @return FamilyVariantInterface
     */
    private function createDefaultFamilyVariant(): FamilyVariantInterface
    {
        $variantFamily = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($variantFamily, [
            'code' => 'family_variant',
            'family' => 'boots',
            'label' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price']
                ]
            ],
        ]);

        $this->get('pim_catalog.saver.family_variant')->save($variantFamily);

        return $variantFamily;
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
        $codes = $collection->map(function(AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();

        return array_values($codes);
    }
}
