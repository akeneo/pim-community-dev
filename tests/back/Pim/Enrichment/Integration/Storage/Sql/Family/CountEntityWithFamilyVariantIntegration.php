<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Family;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountEntityWithFamilyVariantIntegration extends TestCase
{
    public function testThatItFindsTheNumberOfProductsAndProductModelsHavingAFamilyVariant()
    {
        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('clothing_color_size');
        $result = $this
            ->get('pim_catalog.doctrine.query.count_entity_with_family_variant')
            ->belongingToFamilyVariant($familyVariant);
        $this->assertEquals(123, $result);
    }

    public function testThatItFindsNoProductNorProductModelHavingTheFamilyVariant()
    {
        $familyVariant = $this->createFamilyVariant([
            'code'        => 'family_variant_without_products_nor_product_models',
            'family'      => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['color', 'variation_name', 'variation_image'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['size', 'ean', 'sku'],
                ],
            ]
        ]);
        $result = $this
            ->get('pim_catalog.doctrine.query.count_entity_with_family_variant')
            ->belongingToFamilyVariant($familyVariant);
        $this->assertEquals(0, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param array $data
     *
     * @return FamilyVariantInterface
     */
    protected function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }
}
