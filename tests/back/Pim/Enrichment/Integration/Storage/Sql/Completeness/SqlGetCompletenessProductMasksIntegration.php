<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\SqlGetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\TestCase;

class SqlGetCompletenessProductMasksIntegration extends TestCase
{
    public function test_it_returns_mask_with_only_sku()
    {
        $this->createProduct('simple_product', 'familyA', []);

        $expected = [
            new CompletenessProductMask(-1, 'simple_product', 'familyA', [
                'sku-<all_channels>-<all_locales>',
            ])
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['simple_product']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_it_returns_several_masks()
    {
        $this->createProduct('product1', 'familyA', []);
        $this->createProduct('product2', 'familyA', []);

        $expected = [
            new CompletenessProductMask(-1, 'product1', 'familyA', [
                'sku-<all_channels>-<all_locales>',
            ]),
            new CompletenessProductMask(-1, 'product2', 'familyA', [
                'sku-<all_channels>-<all_locales>',
            ])
        ];

        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['product1', 'product2', 'nonExistingProduct']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_it_returns_mask_for_a_product_without_family()
    {
        $this->createProduct('product_without_family', null, []);

        $expected = [
            new CompletenessProductMask(-1, 'product_without_family', null, ['sku-<all_channels>-<all_locales>'])
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['product_without_family']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_it_returns_default_masks()
    {
        $this->createProduct('complex_product', 'familyA', [
            'a_date' => [['locale' => null, 'scope' => null, 'data' => '2010-10-10']],
            'a_file' => [['locale' => null, 'scope' => null, 'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))]],
            'a_metric' => [['locale' => null, 'scope' => null, 'data' => ['amount' => 1, 'unit' => 'WATT']]],
            'a_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['optionA']]],
            'a_number_float' => [['locale' => null, 'scope' => null, 'data' => 3.14]],
            'a_number_float_negative' => [['locale' => null, 'scope' => null, 'data' => -3.14]],
            'a_number_integer' => [['locale' => null, 'scope' => null, 'data' => 42]],
            'a_ref_data_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['fabricA', 'fabricB']]],
            'a_ref_data_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'colorB']],
            'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']],
            'a_text' => [['locale' => null, 'scope' => null, 'data' => 'foo']],
            'a_text_area' => [['locale' => null, 'scope' => null, 'data' => 'foo']],
            'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
            'an_image' => [['locale' => null, 'scope' => null, 'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))]],
        ]);

        $expected = [
            new CompletenessProductMask(-1, 'complex_product', 'familyA', [
                'a_date-<all_channels>-<all_locales>',
                'a_file-<all_channels>-<all_locales>',
                'a_metric-<all_channels>-<all_locales>',
                'a_multi_select-<all_channels>-<all_locales>',
                'a_number_float-<all_channels>-<all_locales>',
                'a_number_float_negative-<all_channels>-<all_locales>',
                'a_number_integer-<all_channels>-<all_locales>',
                'a_ref_data_multi_select-<all_channels>-<all_locales>',
                'a_ref_data_simple_select-<all_channels>-<all_locales>',
                'a_simple_select-<all_channels>-<all_locales>',
                'a_text-<all_channels>-<all_locales>',
                'a_text_area-<all_channels>-<all_locales>',
                'a_yes_no-<all_channels>-<all_locales>',
                'an_image-<all_channels>-<all_locales>',
                'sku-<all_channels>-<all_locales>',
            ])
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['complex_product']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_it_returns_mask_with_specific_channels()
    {
        $this->createProduct('product_with_scopable_data', 'familyA', [
            'a_localized_and_scopable_text_area' => [
                ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'foo']
            ],
            'a_scopable_price' => [
                ['locale' => null, 'scope' => 'ecommerce', 'data' => [['amount' => '2000.00', 'currency' => 'USD']]]
            ],
        ]);

        $expected = [
            new CompletenessProductMask(-1, 'product_with_scopable_data', 'familyA', [
                'sku-<all_channels>-<all_locales>',
                'a_localized_and_scopable_text_area-ecommerce-en_US',
                'a_scopable_price-USD-ecommerce-<all_locales>',
            ])
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['product_with_scopable_data']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_it_returns_mask_with_specific_locales()
    {
        $this->createProduct('product_with_localizable_data', 'familyA', [
            'a_localizable_image' => [
                ['locale' => 'en_US', 'scope' => null, 'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))]
            ],
            'a_localized_and_scopable_text_area' => [
                ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'foo']
            ],
        ]);

        $expected = [
            new CompletenessProductMask(-1, 'product_with_localizable_data', 'familyA', [
                'sku-<all_channels>-<all_locales>',
                'a_localizable_image-<all_channels>-en_US',
                'a_localized_and_scopable_text_area-ecommerce-en_US',
            ])
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['product_with_localizable_data']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_it_returns_price_masks()
    {
        $this->createProduct('product_with_prices', 'familyA', [
            'a_price' => [['locale' => null, 'scope' => null, 'data' => [['amount' => 50.00, 'currency' => 'EUR']]]],
            'a_scopable_price' => [
                ['locale' => null, 'scope' => 'ecommerce', 'data' => [['amount' => '2000.00', 'currency' => 'USD']]]
            ],
        ]);

        $expected = [
            new CompletenessProductMask(-1, 'product_with_prices', 'familyA', [
                'sku-<all_channels>-<all_locales>',
                'a_price-EUR-<all_channels>-<all_locales>',
                'a_scopable_price-USD-ecommerce-<all_locales>',
            ])
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['product_with_prices']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    function test_that_it_returns_masks_even_if_an_attribute_was_deleted()
    {
        $this->createProduct(
            'productA',
            'familyA',
            [
                'a_price' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [['amount' => 50.00, 'currency' => 'EUR']]
                    ]
                ],
            ]
        );
        $expected = [
            new CompletenessProductMask(
                -1, 'productA', 'familyA', [
                    'sku-<all_channels>-<all_locales>',
                    'a_price-EUR-<all_channels>-<all_locales>',
                ]
            )
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['productA']);
        $this->assertSameCompletenessProductMasks($expected, $result);

        $aPrice = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_price');
        $this->get('pim_catalog.remover.attribute')->remove($aPrice);

        $expected = [
            new CompletenessProductMask(
                -1, 'productA', 'familyA', [
                    'sku-<all_channels>-<all_locales>',
                ]
            ),
        ];
        $result = $this->getCompletenessProductMasks()->fromProductIdentifiers(['productA']);
        $this->assertSameCompletenessProductMasks($expected, $result);
    }

    public function test_that_it_returns_a_mask_from_a_value_collection()
    {
        $values = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'productA'),
                ScalarValue::scopableLocalizableValue(
                    'a_localized_and_scopable_text_area',
                    'Lorem ipsum',
                    'ecommerce',
                    'en_US'
                ),
                PriceCollectionValue::scopableValue(
                    'a_scopable_price',
                    new PriceCollection([new ProductPrice(200.00, 'USD')]),
                    'ecommerce'
                ),
            ]
        );
        $expected = [
            new CompletenessProductMask(
                -1, 'productA', 'familyA', [
                    'sku-<all_channels>-<all_locales>',
                    'a_localized_and_scopable_text_area-ecommerce-en_US',
                    'a_scopable_price-USD-ecommerce-<all_locales>',
                ]
            ),
        ];
        $result = $this->getCompletenessProductMasks()->fromValueCollection(-1, 'productA', 'familyA', $values);
        $this->assertSameCompletenessProductMasks($expected, [$result]);
    }

    function test_that_it_returns_an_empty_mask_for_an_empty_value_collection()
    {
        $values = new WriteValueCollection();
        $expected = [
            new CompletenessProductMask(-1, 'productA', 'familyA', []),
        ];

        $result = $this->getCompletenessProductMasks()->fromValueCollection(-1, 'productA', 'familyA', $values);
        $this->assertSameCompletenessProductMasks($expected, [$result]);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    private function createProduct(string $identifier, ?string $familyCode, array $values): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf('Impossible to setup test in %s: %s', static::class, $errors->get(0)->getMessage()));
        }
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getCompletenessProductMasks(): SqlGetCompletenessProductMasks {
        return $this->get('akeneo.pim.enrichment.completeness.query.get_product_masks');
    }

    /**
     * @param CompletenessProductMask[] $expected
     * @param CompletenessProductMask[] $result
     */
    private function assertSameCompletenessProductMasks(array $expected, array $result): void
    {
        if (count($expected) !== count($result)) {
            throw new \Exception(sprintf('Found %d product masks, %d expected.', count($result), count($expected)));
        }

        foreach ($result as $resultMask) {
            $found = false;
            foreach ($expected as $expectedMask) {
                if ($resultMask->identifier() === $expectedMask->identifier()) {
                    $found = true;
                    $this->assertSame($expectedMask->familyCode(), $resultMask->familyCode());
                    $this->assertEqualsCanonicalizing($expectedMask->mask(), $resultMask->mask());
                }
            }

            if (!$found) {
                throw new \Exception(sprintf('Can not find mask of product "%s"', $resultMask->identifier()));
            }
        }
    }
}
