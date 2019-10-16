<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class AttributeOptionsExistValidationIntegration extends TestCase
{
    public function test_existing_options_for_a_simple_product()
    {
        $product = $this->buildProduct('test', [
            'family' => 'familyA',
            'values' => [
                'a_simple_select' => [
                    ['scope' => null, 'locale' => null, 'data' => 'invalid_attribute_option_code']
                ],
                'a_multi_select' => [
                    ['scope' => null, 'locale' => null, 'data' => ['optionA', 'optionY', 'optionB', 'optionZ']],
                ]
            ]
        ]);

        $expectedErrors = [
            'values[a_simple_select-<all_channels>-<all_locales>]' =>
                'Property "a_simple_select" expects a valid code. The option "invalid_attribute_option_code" does not exist',
            'values[a_multi_select-<all_channels>-<all_locales>]' =>
                'Property "a_multi_select" expects valid codes. The following options do not exist: "optionY, optionZ"',
        ];
        $this->assertViolations($product, $expectedErrors);
    }

    public function test_existing_options_for_a_root_product_model()
    {
        $productModel = $this->buildProductModel('test', [
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_multi_select' => [
                    ['scope' => null, 'locale' => null, 'data' => ['optionA', 'optionZ', 'optionB']],
                ]
            ]
        ]);

        $expectedErrors = [
            'values[a_multi_select-<all_channels>-<all_locales>]' =>
                'Property "a_multi_select" expects valid codes. The following options do not exist: "optionZ"',
        ];

        $this->assertViolations($productModel, $expectedErrors);
    }

    public function test_existing_options_for_a_sub_product_model()
    {
        $rootProductModel = $this->buildProductModel(
            'root',
            [
                'family_variant' => 'familyVariantA1',
                'values' => [],
            ]
        );
        $this->assertViolations($rootProductModel, []);
        $this->get('pim_catalog.saver.product_model')->save($rootProductModel);

        $subProductModel = $this->buildProductModel('sub', [
            'parent' => 'root',
            'values' => [
                'a_simple_select' => [
                    ['scope' => null, 'locale' => null, 'data' => 'invalid_option'],
                ],
            ],
        ]);

        $expectedErrors = [
            'values[a_simple_select-<all_channels>-<all_locales>]' =>
                'Property "a_simple_select" expects a valid code. The option "invalid_option" does not exist',
        ];
        $this->assertViolations($subProductModel, $expectedErrors);
    }

    function test_existing_options_for_a_variant_product()
    {
        $rootProductModel = $this->buildProductModel(
            'root',
            [
                'family_variant' => 'familyVariantA2',
                'values' => [],
            ]
        );
        $this->assertViolations($rootProductModel, []);
        $this->get('pim_catalog.saver.product_model')->save($rootProductModel);

        $variantProduct = $this->buildProduct('variant_product', [
            'parent' => 'root',
            'values' => [
                'a_simple_select' => [
                    ['scope' => null, 'locale' => null, 'data' => 'xxl'],
                ],
                'a_yes_no' => [
                    ['scope' => null, 'locale' => null, 'data' => true],
                ]
            ]
        ]);

        $expectedErrors = [
            'values[a_simple_select-<all_channels>-<all_locales>]' =>
                'Property "a_simple_select" expects a valid code. The option "xxl" does not exist',
        ];
        $this->assertViolations($variantProduct, $expectedErrors);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function buildProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        return $product;
    }

    private function buildProductModel(string $code, array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $productModel->setCode($code);
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }

    private function assertViolations(EntityWithValuesInterface $entity, array $expectedErrors): void
    {
        $violations = $this->get('validator')->validate($entity);
        Assert::assertCount(count($expectedErrors), $violations);

        foreach ($violations as $violation) {
            Assert::assertArrayHasKey($violation->getPropertyPath(), $expectedErrors);
            Assert::assertSame($expectedErrors[$violation->getPropertyPath()], $violation->getMessage());
        }
    }
}
