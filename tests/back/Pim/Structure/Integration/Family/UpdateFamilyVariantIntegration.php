<?php

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateFamilyVariantIntegration extends TestCase
{
    /**
     * Basic test that checks the family variant update
     */
    public function testTheFamilyVariantUpdate()
    {
        $expectedCommonAttributes = [
            'composition',
            'keywords',
            'material',
            'meta_description',
            'meta_title',
            'name',
            'notice',
            'price',
            'sole_composition',
            'supplier',
            'top_composition',
            'variation_image',
            'variation_name',
        ];

        $familyVariant = $this->getFamilyVariant('shoes_size');

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'labels' => [
                    'en_US' => 'My family variant',
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['eu_shoes_size'],
                        'attributes' => [
                            'ean',
                            'brand',
                            'collection',
                            'color',
                            'description',
                            'erp_name',
                            'image',
                            'weight',
                            'size',
                        ],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $errors = $this->validateFamilyVariant($familyVariant);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
        $this->get('doctrine.orm.entity_manager')->refresh($familyVariant);

        $this->assertEquals(
            $expectedCommonAttributes,
            $this->extractAttributeCode($familyVariant->getCommonAttributes()),
            'Common attributes are invalid'
        );

        $variantAttributeSet = $familyVariant->getVariantAttributeSet(1);
        $this->assertEquals(
            ['eu_shoes_size'],
            $this->extractAttributeCode($variantAttributeSet->getAxes()),
            'Axis is invalid (level 1)'
        );
        $this->assertEquals(
            [
                'brand',
                'collection',
                'color',
                'description',
                'ean',
                'erp_name',
                'eu_shoes_size',
                'image',
                'size',
                'sku',
                'weight',
            ],
            $this->extractAttributeCode($variantAttributeSet->getAttributes()),
            'Variant attribute are invalid (level 1)'
        );

        $this->assertEquals(
            1,
            $familyVariant->getNumberOfLevel(),
            'Number of variant level is invalid'
        );
    }

    public function testAddUniqueAttributeToFamily()
    {
        $uniqueAttribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
        $this->get('pim_catalog.updater.attribute')->update(
            $uniqueAttribute,
            [
                'code' => 'unique_attribute',
                'group' => 'other',
                'unique' => true,
            ]
        );

        $errors = $this->get('validator')->validate($uniqueAttribute);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.attribute')->save($uniqueAttribute);

        $accessories = $this->get('pim_catalog.repository.family')->findOneByIdentifier('accessories');
        $accessories->addAttribute($uniqueAttribute);
        $this->get('pim_catalog.saver.family')->save($accessories);

        $accessoriesSize = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('accessories_size');

        $variantAttributeSet = $accessoriesSize->getVariantAttributeSet(1);
        $this->assertEquals(
            [
                'ean',
                'size',
                'sku',
                'unique_attribute',
                'variation_name',
                'weight',
            ],
            $this->extractAttributeCode($variantAttributeSet->getAttributes())
        );
    }

    /**
     * Validation: Family variant axes cannot be updated when adding attributes
     */
    public function testTheFamilyVariantAxesImmutabilityByAddingAxes()
    {
        $familyVariant = $this->getFamilyVariant('shoes_size');

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'axes' => ['weight', 'brand'],
                        'attributes' => ['ean', 'collection', 'color', 'description', 'erp_name'],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $errors = $this->validateFamilyVariant($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('Variant axes cannot be modified for the level "1"', $errors->get(0)->getMessage());
        $this->assertEquals('variantAttributeSets[0].axes', $errors->get(0)->getPropertyPath());
    }

    /**
     * Validation: Family variant axes cannot be updated when deleting one of the attribute in the axes
     */
    public function testTheFamilyVariantAxesImmutabilityByDeletingAxes()
    {
        $familyVariant = $this->getFamilyVariant('clothing_colorsize');

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $errors = $this->validateFamilyVariant($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('Variant axes cannot be modified for the level "1"', $errors->get(0)->getMessage());
        $this->assertEquals('variantAttributeSets[0].axes', $errors->get(0)->getPropertyPath());
    }

    /**
     * Validation: The number of level of the family variant cannot be changed
     */
    public function testTheFamilyVariantLevelNumberImmutability()
    {
        $this->expectException(ImmutablePropertyException::class);
        $this->expectExceptionMessage('The number of variant attribute sets cannot be changed.');

        $familyVariant = $this->getFamilyVariant('shoes_size');

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'axes' => ['eu_shoes_size'],
                        'attributes' => ['brand', 'collection', 'color', 'description', 'erp_name', 'image'],
                        'level' => 1,
                    ],
                    [
                        'axes' => ['supplier'],
                        'attributes' => ['ean', 'name', 'notice', 'price', 'sku'],
                        'level' => 2,
                    ],
                ],
            ]
        );
    }

    /**
     * Validation: An attribute can be moved from a variant attribute set to a lower one
     */
    public function testMovingOfAnAttributeToLowerLevel()
    {
        $familyVariant = $this->getFamilyVariant('clothing_color_size');

        // Move "brand" from common to attribute set level 2
        // Move Collection from from common to attribute set level 1
        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'attributes' => ['collection', 'color', 'composition', 'material', 'variation_image', 'variation_name'],
                        'level' => 1,
                    ],
                    [
                        'attributes' => ['brand', 'ean', 'sku', 'weight', 'size'],
                        'level' => 2,
                    ],
                ],
            ]
        );

        $errors = $this->validateFamilyVariant($familyVariant);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
        $this->get('doctrine.orm.entity_manager')->refresh($familyVariant);

        $this->assertEquals(
            [
                'care_instructions',
                'description',
                'erp_name',
                'image',
                'keywords',
                'meta_description',
                'meta_title',
                'name',
                'notice',
                'price',
                'supplier',
                'wash_temperature',
            ],
            $this->extractAttributeCode($familyVariant->getCommonAttributes()),
            'Variant attribute are invalid (level 1)'
        );

        $variantAttributeSet1 = $familyVariant->getVariantAttributeSet(1);
        $this->assertEquals(
            ['collection', 'color', 'composition', 'material', 'variation_image', 'variation_name'],
            $this->extractAttributeCode($variantAttributeSet1->getAttributes()),
            'Variant attribute are invalid (level 1)'
        );

        $variantAttributeSet2 = $familyVariant->getVariantAttributeSet(2);
        $this->assertEquals(
            ['brand', 'ean', 'size', 'sku', 'weight'],
            $this->extractAttributeCode($variantAttributeSet2->getAttributes()),
            'Variant attribute are invalid (level 2)'
        );
    }

    /**
     * Validation: An attribute cannot be moved from a variant attribute set to an upper one
     */
    public function testMovingOfAnAttributeToUpperLevel()
    {
        $familyVariant = $this->getFamilyVariant('clothing_color_size');

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'attributes' => ['weight'],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $errors = $this->validateFamilyVariant($familyVariant);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Attributes must be unique, "weight" are used several times in variant attributes sets',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * @group do
     *
     * Validation: An attribute cannot be moved from a variant attribute set to an upper one
     */
    public function testRemovingOfAnAttributeToUpperLevel()
    {
        $familyVariant = $this->getFamilyVariant('clothing_color_size');

        $this->get('pim_catalog.updater.family_variant')->update(
            $familyVariant,
            [
                'variant_attribute_sets' => [
                    [
                        'attributes' => ['color','composition','material','variation_image','variation_name'],
                        'level' => 1,
                    ],
                    [
                        'attributes' => ['size','ean','sku'],
                        'level' => 2,
                    ],
                ],
            ]
        );

        $variantAttributeSet1 = $familyVariant->getVariantAttributeSet(1);
        $this->assertEquals(
            ['color','composition','material','variation_image','variation_name'],
            $this->extractAttributeCode($variantAttributeSet1->getAttributes()),
            'Variant attribute are invalid (level 1)'
        );

        $variantAttributeSet2 = $familyVariant->getVariantAttributeSet(2);
        $this->assertEquals(
            ['ean', 'size', 'sku'],
            $this->extractAttributeCode($variantAttributeSet2->getAttributes()),
            'Variant attribute are invalid (level 2)'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * Gets a family variant by its identifier.
     *
     * @param string $code
     *
     * @return FamilyVariantInterface
     */
    private function getFamilyVariant(string $code): FamilyVariantInterface
    {
        return $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier($code);
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
        $codes = $collection->map(
            function (AttributeInterface $attribute) {
                return $attribute->getCode();
            }
        )->toArray();

        $codes = array_values($codes);
        sort($codes);

        return $codes;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return ConstraintViolationListInterface
     */
    private function validateFamilyVariant(FamilyVariantInterface $familyVariant): ConstraintViolationListInterface
    {
        return $this->get('validator')->validate($familyVariant);
    }
}
