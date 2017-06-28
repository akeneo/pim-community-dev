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
            'variant-attribute-sets' => [
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
        Assert::notNull($variantFamily, 'The family variant with the code "family_variant" does not exist');

        $this->assertEquals('boots', $variantFamily->getFamily()->getCode(), 'The family code does not match boots');
        $this->assertEquals(
            ['name', 'manufacturer', 'description'],
            $this->extractAttributeCode($variantFamily->getCommonAttributeSet()->getAttributes()),
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
            'variant-attribute-sets' => [
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
            'variant-attribute-sets' => [
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
        $this->assertEquals('Variant axes must be unique', $errors->get(0)->getMessage());
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
            'variant-attribute-sets' => [
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
        $this->assertEquals('Attributes must be unique', $errors->get(0)->getMessage());
    }

    /**
     * Validation: Available attributes for axis are metric, simple select and reference data simple select
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
            'variant-attribute-sets' => [
                [
                    'axes' => ['side_view'],
                    'attributes' => ['weather_conditions', 'rating', 'color', 'top_view', 'lace_color']
                ],
                [
                    'axes' => ['size'],
                    'attributes' => ['sku', 'price']
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($variantFamily);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Variant axes must be a boolean, a simple select, a simple reference data or a metric',
            $errors->get(0)->getMessage()
        );
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
            'variant-attribute-sets' => [
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
        return $collection->map(function(AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();
    }
}