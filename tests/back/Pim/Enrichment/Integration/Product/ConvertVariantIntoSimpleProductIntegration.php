<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ConvertVariantIntoSimpleProductIntegration extends TestCase
{
    /** @var ProductInterface */
    private $product;

    /** @test */
    public function it_converts_a_variant_product_into_a_simple_one(): void
    {
        Assert::assertTrue($this->product->isVariant());

        $initialValues = WriteValueCollection::fromCollection($this->product->getValues());
        $initialCategories = $this->product->getCategoryCodes();
        $initialAssociations = $this->get('pim_catalog.normalizer.standard.product.associations')
                                    ->normalize($this->product, 'standard');

        $this->convertToSimpleProduct($this->product);

        Assert::assertFalse($this->product->isVariant());
        Assert::assertNull($this->product->getFamilyVariant());
        $this->assertValues($this->product, $initialValues);
        $this->assertCategories($this->product, $initialCategories);
        $this->assertAssociations($this->product, $initialAssociations);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $productFromDb = $this->get('pim_catalog.repository.product')->findOneByIdentifier('variant');

        Assert::assertFalse($productFromDb->isVariant());
        Assert::assertNull($productFromDb->getFamilyVariant());
        $this->assertValues($productFromDb, $initialValues);
        $this->assertCategories($productFromDb, $initialCategories);
        $this->assertAssociations($productFromDb, $initialAssociations);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function loadFixtures()
    {
        $this->createProduct('random', ['family' => 'familyA']);
        $this->createProduct('other', ['family' => 'familyA1']);
        $this->createProductModel(['code' => 'pm_1', 'family_variant' => 'familyVariantA1']);
        $this->createProductModel(['code' => 'pm_2', 'family_variant' => 'familyVariantA2']);

        $this->createProductModel(
            [
                'code' => 'root',
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'a_date' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => '2020-09-29',
                        ],
                    ],
                    'a_scopable_price' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => [
                                [
                                    'amount' => 10,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => [
                                [
                                    'amount' => 8,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ],
                ],
                'categories' => ['categoryA1', 'categoryA2'],
                'associations' => [
                    'PACK' => [
                        'products' => ['random'],
                        'product_models' => ['pm_1'],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );

        $this->product = $this->createProduct(
            'variant',
            [
                'parent' => 'root',
                'categories' => ['categoryB'],
                'values' => [
                    'a_simple_select' => [
                        [
                            'data' => 'optionA',
                            'scope' => null,
                            'locale' => null,
                        ],
                    ],
                    'a_yes_no' => [
                        [
                            'data' => true,
                            'scope' => null,
                            'locale' => null,
                        ],
                    ],
                    'a_text' => [
                        [
                            'data' => 'variant text',
                            'scope' => null,
                            'locale' => null,
                        ],
                    ],
                ],
                'associations' => [
                    'PACK' => [
                        'products' => ['other'],
                    ],
                    'UPSELL' => [
                        'product_models' => ['pm_2'],
                        'groups' => ['groupB'],
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function assertValues(ProductInterface $product, WriteValueCollection $expectedValues): void
    {
        $actualValues = $product->getValuesForVariation();

        Assert::assertSame($expectedValues->count(), $actualValues->count());
        foreach ($expectedValues as $expectedValue) {
            $actualValue = $actualValues->getSame($expectedValue);
            Assert::assertNotNull($actualValue);
            Assert::assertEquals($expectedValue, $actualValue);
        }
    }

    private function assertCategories(ProductInterface $product, array $expectedCategoryCodes): void
    {
        Assert::assertEqualsCanonicalizing($expectedCategoryCodes, $product->getCategoryCodes());
    }

    private function assertAssociations(ProductInterface $product, array $expectedAssociations): void
    {
        Assert::assertSame($product->getAssociations()->count(), count($expectedAssociations));
        foreach ($expectedAssociations as $associationTypeCode => $association) {
            $actualAssociation = $product->getAssociationForTypeCode($associationTypeCode);
            Assert::assertInstanceOf(ProductAssociation::class, $actualAssociation);

            $actualAssociatedProductIdentifiers = $actualAssociation->getProducts()->map(
                function (ProductInterface $product): string {
                    return $product->getIdentifier();
                }
            )->toArray();
            Assert::assertEqualsCanonicalizing($association['products'] ?? [], $actualAssociatedProductIdentifiers);

            $actualAssociatedProductModelCodes = $actualAssociation->getProductModels()->map(
                function (ProductModelInterface $productModel): string {
                    return $productModel->getCode();
                }
            )->toArray();
            Assert::assertEqualsCanonicalizing(
                $association['product_models'] ?? [],
                $actualAssociatedProductModelCodes
            );

            $actualAssociatedGroupCodes = $actualAssociation->getGroups()->map(
                function (GroupInterface $group): string {
                    return $group->getCode();
                }
            )->toArray();
            Assert::assertEqualsCanonicalizing(
                $association['groups'] ?? [],
                $actualAssociatedGroupCodes
            );
        }
    }

    private function convertToSimpleProduct(ProductInterface $product): void
    {
        if (!$product->isVariant()) {
            throw new \InvalidArgumentException('The "%s" product is already simple', $product->getIdentifier());
        }
        $this->get('pim_catalog.entity_with_family_variant.remove_parent_from_product')->from(
            $product,
            ['parent' => null]
        );
        $this->saveProduct($product);
    }

    private function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->saveProduct($product);

        return $product;
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, sprintf('The product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function saveProduct(ProductInterface $product): void
    {
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, sprintf('The product is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product')->save($product);
    }
}
