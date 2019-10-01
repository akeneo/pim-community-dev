<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Remover;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class AttributeOptionRemoverIntegration extends TestCase
{
    public function test_successfully_remove_attribute_option_if_attribute_is_not_used_as_attribute_axes()
    {
        $this->addAdditionalFixtures();

        $attributeOptionRepository = $this->get('pim_catalog.repository.attribute_option');
        $attributeOptionRemover = $this->get('pim_catalog.remover.attribute_option');

        $optionWhite = $attributeOptionRepository->findOneByIdentifier('a_simple_select_color.white');
        $attributeOptionRemover->remove($optionWhite);

        $result = $attributeOptionRepository->findOneByIdentifier('a_simple_select_color.white');
        $this->assertNull($result);
    }

    public function test_successfully_remove_attribute_option_if_option_is_not_used_as_attribute_axes()
    {
        $this->addAdditionalFixtures();

        $attributeOptionRepository = $this->get('pim_catalog.repository.attribute_option');
        $attributeOptionRemover = $this->get('pim_catalog.remover.attribute_option');

        $optionB = $attributeOptionRepository->findOneByIdentifier('a_simple_select.optionB');
        $attributeOptionRemover->remove($optionB);

        $result = $attributeOptionRepository->findOneByIdentifier('a_simple_select.optionB');
        $this->assertNull($result);
    }

    public function test_fail_to_remove_attribute_option_if_option_is_used_as_attribute_axes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Attribute option "optionA" could not be removed as it is used as variant axis value.');

        $this->addAdditionalFixtures();

        $attributeOptionRepository = $this->get('pim_catalog.repository.attribute_option');
        $attributeOptionRemover = $this->get('pim_catalog.remover.attribute_option');

        $optionB = $attributeOptionRepository->findOneByIdentifier('a_simple_select.optionA');
        $attributeOptionRemover->remove($optionB);
    }

    private function addAdditionalFixtures(): void
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $productModelRootA1 = $entityBuilder->createProductModel(
            'product_model_root_A1',
            'familyVariantA1',
            null,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                ],
            ]
        );
        $productModelA1 = $entityBuilder->createProductModel(
            'product_model_A1',
            'familyVariantA1',
            $productModelRootA1,
            [
                'values' => [
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => false,
                        ],
                    ],
                ],
            ]
        );
        $productModelA2 = $entityBuilder->createProductModel('another_product_model', 'familyVariantA2', null, []);
        $entityBuilder->createVariantProduct(
            'variant_product',
            'familyA',
            'familyVariantA2',
            $productModelA2,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
            ]
        );
        $entityBuilder->createVariantProduct(
            'another_variant_product',
            'familyA',
            'familyVariantA1',
            $productModelA1,
            []
        );
        $entityBuilder->createProduct(
            'a_product',
            'familyA',
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
            ]
        );
        $entityBuilder->createProduct(
            'another_product',
            'familyA',
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionB',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
