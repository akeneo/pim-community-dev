<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Florian Klein (florian.klein@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveProductModelIntegration extends TestCase
{
    /**
     * @test
     */
    public function removing_a_product_model_deletes_its_children_too()
    {
        [$rootProductModel, $subProductModel, $variant] = $this->arrange();

        $rootId = $rootProductModel->getId();
        $subId = $subProductModel->getId();
        $variantId = $variant->getId();

        sleep(5); // the product need to be indexed...

        $this->getFromTestContainer('pim_catalog.remover.product_model')->remove($rootProductModel);

        $this->getFromTestContainer('doctrine.orm.default_entity_manager')->clear();
        $productRepository = $this->getFromTestContainer('pim_catalog.repository.product');

        $this->assertNull($productRepository->find($rootId));
        $this->assertNull($productRepository->find($subId));
        $this->assertNull($productRepository->find($variantId));
    }

    /**
     * Inserts and returns a product model hierarchy of 1 root, 1 sub-model and 1 variant
     */
    private function arrange(): array
    {
        $this->entityBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->entityBuilder->createFamilyVariant(
            [
                'code' => 'two_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                    ],
                ],
            ]
        );

        $rootProductModel = $this->entityBuilder->createProductModel(
            'root_product_model_two_level',
            'two_level_family_variant',
            null,
            []
        );

        $subProductModel = $this->entityBuilder->createProductModel(
            'sub_product_model',
            'two_level_family_variant',
            $rootProductModel,
            []
        );

        $variant = $this->entityBuilder->createVariantProduct(
            'variant_product_1',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            []
        );

        return [$rootProductModel, $subProductModel, $variant];
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
