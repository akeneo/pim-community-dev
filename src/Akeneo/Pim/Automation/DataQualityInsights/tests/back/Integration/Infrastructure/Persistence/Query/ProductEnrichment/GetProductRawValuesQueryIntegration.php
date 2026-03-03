<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment\GetProductRawValuesQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductRawValuesQueryIntegration extends TestCase
{
    public function test_it_returns_product_values_by_attribute()
    {
        $productUuid = $this->createProduct();

        $expectedRawValues = [
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'some text'
                ],
            ],
            'a_yes_no' => [
                '<all_channels>' => [
                    '<all_locales>' => true
                ],
            ],
        ];

        $productRawValues = $this
            ->get(GetProductRawValuesQuery::class)
            ->execute($productUuid);

        $this->assertProductHasRawValues($expectedRawValues, $productRawValues);
    }

    public function test_it_returns_empty_array_if_product_do_not_exists()
    {
        $result = $this
            ->get(GetProductRawValuesQuery::class)
            ->execute(ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), ['a_text', 'a_yes_no']);

        $this->assertSame([], $result);
    }

    public function test_it_returns_variant_product_values()
    {
        $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_price' => [
                        'data' => [
                            'data' => [['amount' => '50', 'currency' => 'EUR']],
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                    'a_number_float' => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'data' => 'my pink tshirt',
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'family_variant' => 'familyVariantA1',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [
                        'data' => [
                            'data' => 'optionA',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                    'a_text' => [
                        [
                            'data' => 'some text',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                ],
            ]
        );

        $productId = $this->createVariantProduct(
            'variant_A_yes',
            [
                new ChangeParent('sub_pm_A'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );

        $result = $this
            ->get(GetProductRawValuesQuery::class)
            ->execute($productId);

        $expectedResult = [
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'some text'
                ],
            ],
            'a_price' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        0 => [
                            'amount' => '50.00',
                            'currency' => 'EUR'
                        ]
                    ],
                ],
            ],
            'a_yes_no' => [
                '<all_channels>' => [
                    '<all_locales>' => true
                ],
            ],
            'a_number_float' => [
                '<all_channels>' => [
                    '<all_locales>' => '12.5000',
                ],
            ],
            'a_simple_select' => [
                '<all_channels>' => [
                    '<all_locales>' => 'optionA'
                ],
            ],
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'en_US' => 'my pink tshirt'
                ],
            ],
        ];

        $this->assertProductHasRawValues($expectedResult, $result);
    }

    private function createOrUpdateProduct(string $identifier, array $userIntents): ProductInterface {
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product afterwards

        $this->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createWithIdentifierSystemUser(
            $identifier,
            $userIntents
        ));

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function createProduct(): ProductUuid
    {
        $product =  $this->createOrUpdateProduct('product_with_family', [
            new SetFamily('familyA3'),
            new SetTextValue('a_text', null, null, 'some text'),
            new SetBooleanValue('a_yes_no', null, null, true),
        ]);

        return $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());
    }

    private function createVariantProduct(string $identifier, array $userIntents): ProductUuid
    {
        $product = $this->createOrUpdateProduct($identifier, $userIntents);

        return $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }



    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertProductHasRawValues(array $expectedRawValues, $productRawValues): void
    {
        foreach ($expectedRawValues as $attributeCode => $expectedAttributeRawValues) {
            $this->assertArrayHasKey($attributeCode, $productRawValues);
            $this->assertEquals($expectedAttributeRawValues, $productRawValues[$attributeCode]);
        }
    }
}
