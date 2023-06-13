<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

/**
 * Integration tests to verify a product is well saved in database.
 */
class ProductSaverIntegration extends TestCase
{
    public function test_it_saves_a_variant_product(): void
    {
        $productModel = $this->createProductModel('just-a-product-model', 'familyVariantA1');
        $this->updateProductModel($productModel, $this->getStandardValuesWithDifferentFewAttributes());
        $this->saveProductModel($productModel);

        $product = $this->createVariantProduct($productModel, 'just-a-variant-product-with-a-few-values', 'familyVariantA1');
        $standardValues = $this->getStandardValuesWithFewAttributes();
        $this->updateProduct($product, $standardValues);
        $this->saveProduct($product);

        $jsonRawValues = $this->get('database_connection')->fetchOne(
            <<<SQL
            SELECT raw_values FROM pim_catalog_product WHERE identifier = 'just-a-variant-product-with-a-few-values'
            SQL
        );
        $rawValues = json_decode($jsonRawValues, true);
        NormalizedProductCleaner::cleanOnlyValues($rawValues);

        $expectedRawValues = $this->getStorageValuesWithFewAttributes();
        NormalizedProductCleaner::cleanOnlyValues($expectedRawValues);

        $this->assertSame($expectedRawValues, $rawValues);
    }

    public function test_it_stores_product_identifier_values(): void
    {
        $this->createIdentifierAttribute('ean');

        $product = new Product();
        $sku = IdentifierValue::value('sku', true, 'sku-product-1');
        $ean = IdentifierValue::value('ean', false, '0123456789');
        $product->addValue($sku);
        $product->addValue($ean);
        $this->saveProduct($product);
        Assert::assertEqualsCanonicalizing(
            ['sku#sku-product-1', 'ean#0123456789'],
            $this->getIdentifierValues($product->getUuid())
        );

        $product->removeValue($sku);
        $otherProduct = new Product();
        $this->get('pim_catalog.saver.product')->saveAll([$product, $otherProduct]);

        Assert::assertEqualsCanonicalizing(
            ['ean#0123456789'],
            $this->getIdentifierValues($product->getUuid())
        );
        Assert::assertSame([], $this->getIdentifierValues($otherProduct->getUuid()));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     */
    private function updateProduct(ProductInterface $product, array $data)
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
    }

    /**
     * @param ProductInterface $product
     */
    private function saveProduct(ProductInterface $product)
    {
        $this->get('pim_catalog.saver.product')->save($product);
    }

    /**
     * @param string $identifier
     *
     * @return ProductModelInterface
     */
    private function createProductModel(string $identifier, string $familyVariantCode): ProductModelInterface
    {
        $familyVariant = $this->get('pim_api.repository.family_variant')->findOneByIdentifier($familyVariantCode);

        $model = new ProductModel();
        $model->setCode($identifier);
        $model->setFamilyVariant($familyVariant);

        return $model;
    }

    /**
     * TODO: should be replaced by a proper object (builder, factory, whatever)
     *
     * @param ProductModelInterface $productModel
     * @param string                $productIdentifier
     * @param string                $familyVariantCode
     *
     * @return ProductInterface
     */
    private function createVariantProduct(ProductModelInterface $productModel, string $productIdentifier, string $familyVariantCode): ProductInterface
    {
        $product = new Product();
        $product->setParent($productModel);

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier($familyVariantCode);
        $product->setFamilyVariant($familyVariant);
        $product->setFamily($familyVariant->getFamily());

        $identifierAttribute = $this->get('pim_catalog.repository.attribute')->getIdentifier();
        $this->get('pim_catalog.builder.product')->addOrReplaceValue($product, $identifierAttribute, null, null, $productIdentifier);

        return $product;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param array                 $data
     */
    private function updateProductModel(ProductModelInterface $productModel, array $data)
    {
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function saveProductModel(ProductModelInterface $productModel)
    {
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * @return array
     */
    private function getStandardValuesWithAllAttributes(): array
    {
        return [
            'values' => [
                'a_file' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt',
                    ],
                ],
                'an_image' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg',
                    ],
                ],
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_metric' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                    ],
                ],
                'a_metric_without_decimal' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => 98, 'unit' => 'CENTIMETER'],
                    ],
                ],
                'a_metric_without_decimal_negative' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => -20, 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_metric_negative' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'a_number_float' => [
                    ['locale' => null, 'scope' => null, 'data' => '12.5678000000111'],
                ],
                'a_number_float_negative' => [
                    ['locale' => null, 'scope' => null, 'data' => '-99.8732000000111'],
                ],
                'a_number_integer' => [
                    ['locale' => null, 'scope' => null, 'data' => 42]
                ],
                'a_number_integer_negative' => [
                    ['locale' => null, 'scope' => null, 'data' => -42]
                ],
                'a_price' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '56.53', 'currency' => 'EUR'],
                            ['amount' => '45.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_price_without_decimal' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => 56, 'currency' => 'EUR'],
                            ['amount' => -45, 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_ref_data_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['fabricA', 'fabricB']]
                ],
                'a_ref_data_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'colorB'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a text',
                    ],
                ],
                '123'                                => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'a text for an attribute with numerical code',
                    ],
                ],
                'a_text_area' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'a_localizable_image' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => '7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => '0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg',
                    ],
                ],
                'a_scopable_price' => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => [
                            ['amount' => '15.00', 'currency' => 'EUR'],
                            ['amount' => '20.00', 'currency' => 'USD'],
                        ],
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['amount' => '17.00', 'currency' => 'EUR'],
                            ['amount' => '24.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'a text area for ecommerce in English',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'tablet',
                        'data'   => 'a text area for tablets in English'
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => 'une zone de texte pour les tablettes en français',
                    ],
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getStandardValuesWithFewAttributes(): array
    {
        return [
            'values' => [
                'a_number_integer' => [
                    ['locale' => null, 'scope' => null, 'data' => 42]
                ],
                'a_price' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '56.53', 'currency' => 'EUR'],
                            ['amount' => '45.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a beautiful text',
                    ],
                ],
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => true,
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    private function getStandardValuesWithDifferentFewAttributes(): array
    {
        return [
            'values' => [
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_metric' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getStorageValuesWithFewAttributes(): array
    {
        return [
            'a_number_integer' => [
                '<all_channels>' => [
                    '<all_locales>' => 42,
                ],
            ],
            'a_price' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        ['amount' => '56.53', 'currency' => 'EUR'],
                        ['amount' => '45.00', 'currency' => 'USD'],
                    ],
                ],
            ],
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'this is a beautiful text',
                ],
            ],
            'a_yes_no' => [
                '<all_channels>' => [
                    '<all_locales>' => true,
                ],
            ],
            'sku' => [
                '<all_channels>' => [
                    '<all_locales>' => 'just-a-variant-product-with-a-few-values',
                ],
            ],
        ];
    }

    private function createIdentifierAttribute(string $attributeCode): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::IDENTIFIER);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => $attributeCode,
            'group' => 'other',
            'scopable' => false,
            'localizable' => false,
        ]);
        // TODO CPM-1066: uncomment validation
//        Assert::assertCount(
//            0,
//            $this->get('validator')->validate($attribute)
//        );
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function getIdentifierValues(UuidInterface $uuid): array
    {
        $identifiersFromDb = $this->get('database_connection')->fetchOne(
            <<<SQL
            SELECT identifiers FROM pim_catalog_product_identifiers
            WHERE product_uuid = :uuid
            SQL,
            ['uuid' => $uuid->getBytes()],
            ['uuid' => ParameterType::BINARY]
        );

        return \json_decode($identifiersFromDb, true);
    }
}
