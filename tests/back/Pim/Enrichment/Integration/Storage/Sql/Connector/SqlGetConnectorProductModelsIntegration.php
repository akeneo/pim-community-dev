<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetConnectorProductModelsIntegration extends TestCase
{
    /**
     * @test
     *
     * @group ce
     */
    public function it_gets_several_connector_product_models_from_PQB(): void
    {
        $pqb = $this->get('pim_catalog.query.product_model_query_builder_search_after_size_factory_external_api')
                    ->create(['limit' => 10]);
        $actualProductModelList = $this->getQuery()->fromProductQueryBuilder(
            $pqb,
            $this->getUserIdFromUsername('admin'),
            null,
            null,
            null
        );

        $dataSimplePm = $this->getIdAndDatesFromProductModelCode('simple_pm');
        $dataRootPm = $this->getIdAndDatesFromProductModelCode('root_pm');
        $dataSubPm = $this->getIdAndDatesFromProductModelCode('sub_pm_A');

        $expectedProductModelList = new ConnectorProductModelList(3, [
            new ConnectorProductModel(
                (int)$dataSimplePm['id'],
                'simple_pm',
                new \DateTimeImmutable($dataSimplePm['created']),
                new \DateTimeImmutable($dataSimplePm['updated']),
                null,
                'familyA',
                'familyVariantA2',
                [],
                [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'X_SELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],

                ],
                [],
                new ReadValueCollection([])
            ),
            new ConnectorProductModel(
                (int)$dataRootPm['id'],
                'root_pm',
                new \DateTimeImmutable($dataRootPm['created']),
                new \DateTimeImmutable($dataRootPm['updated']),
                null,
                'familyA',
                'familyVariantA1',
                [],
                [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'X_SELL' => [
                        'products' => ['another_product'],
                        'product_models' => ['simple_pm'],
                        'groups' => ['groupB'],
                    ],
                ],
                ['categoryA2'],
                new ReadValueCollection(
                    [
                        PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(number_format(50.00, 2), 'EUR')])),
                        ScalarValue::value('a_number_float', '12.5000'),
                        ScalarValue::scopableLocalizableValue(
                            'a_localized_and_scopable_text_area',
                            'mon tshirt rose',
                            'tablet',
                            'fr_FR'
                        ),
                        ScalarValue::scopableLocalizableValue(
                            'a_localized_and_scopable_text_area',
                            'my pink tshirt',
                            'ecommerce',
                            'en_US'
                        ),
                    ]
                )
            ),
            new ConnectorProductModel(
                (int)$dataSubPm['id'],
                'sub_pm_A',
                new \DateTimeImmutable($dataSubPm['created']),
                new \DateTimeImmutable($dataSubPm['updated']),
                'root_pm',
                'familyA',
                'familyVariantA1',
                [],
                [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'X_SELL' => [
                        'products' => ['a_simple_product', 'another_product'],
                        'product_models' => ['simple_pm'],
                        'groups' => ['groupA', 'groupB'],
                    ],
                ],
                ['categoryA1', 'categoryA2'],
                new ReadValueCollection(
                    [
                        OptionValue::value('a_simple_select', 'optionA'),
                        PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(number_format(50.00, 2), 'EUR')])),
                        ScalarValue::value('a_number_float', '12.5000'),
                        ScalarValue::scopableLocalizableValue(
                            'a_localized_and_scopable_text_area',
                            'mon tshirt rose',
                            'tablet',
                            'fr_FR'
                        ),
                        ScalarValue::scopableLocalizableValue(
                            'a_localized_and_scopable_text_area',
                            'my pink tshirt',
                            'ecommerce',
                            'en_US'
                        ),
                        ScalarValue::value('a_text', 'Lorem ipsum dolor sit amet'),
                    ]
                )
            ),
        ]);

        Assert::assertEquals($expectedProductModelList, $actualProductModelList);
    }

    /**
     * @test
     *
     * @group ce
     */
    public function it_gets_connector_product_models_by_filtering_on_values(): void
    {
        $pqb = $this->get('pim_catalog.query.product_model_query_builder_search_after_size_factory_external_api')
                    ->create(['limit' => 10]);
        $actualProductModelList = $this->getQuery()->fromProductQueryBuilder(
            $pqb,
            $this->getUserIdFromUsername('admin'),
            ['a_localized_and_scopable_text_area', 'a_number_float', 'a_simple_select'],
            'ecommerce',
            ['en_US']
        );

        $dataSimplePm = $this->getIdAndDatesFromProductModelCode('simple_pm');
        $dataRootPm = $this->getIdAndDatesFromProductModelCode('root_pm');
        $dataSubPm = $this->getIdAndDatesFromProductModelCode('sub_pm_A');

        $expectedProductModelList = $expectedProductModelList = new ConnectorProductModelList(3, [
            new ConnectorProductModel(
                (int)$dataSimplePm['id'],
                'simple_pm',
                new \DateTimeImmutable($dataSimplePm['created']),
                new \DateTimeImmutable($dataSimplePm['updated']),
                null,
                'familyA',
                'familyVariantA2',
                [],
                [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'X_SELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                ],
                [],
                new ReadValueCollection([])
            ),
            new ConnectorProductModel(
                (int)$dataRootPm['id'],
                'root_pm',
                new \DateTimeImmutable($dataRootPm['created']),
                new \DateTimeImmutable($dataRootPm['updated']),
                null,
                'familyA',
                'familyVariantA1',
                [],
                [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'X_SELL' => [
                        'products' => ['another_product'],
                        'product_models' => ['simple_pm'],
                        'groups' => ['groupB'],
                    ],
                ],
                ['categoryA2'],
                new ReadValueCollection(
                    [
                        ScalarValue::value('a_number_float', '12.5000'),
                        ScalarValue::scopableLocalizableValue(
                            'a_localized_and_scopable_text_area',
                            'my pink tshirt',
                            'ecommerce',
                            'en_US'
                        ),
                    ]
                )
            ),
            new ConnectorProductModel(
                (int)$dataSubPm['id'],
                'sub_pm_A',
                new \DateTimeImmutable($dataSubPm['created']),
                new \DateTimeImmutable($dataSubPm['updated']),
                'root_pm',
                'familyA',
                'familyVariantA1',
                [],
                [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                    'X_SELL' => [
                        'products' => ['a_simple_product', 'another_product'],
                        'product_models' => ['simple_pm'],
                        'groups' => ['groupA', 'groupB'],
                    ],
                ],
                ['categoryA1', 'categoryA2'],
                new ReadValueCollection(
                    [
                        OptionValue::value('a_simple_select', 'optionA'),
                        ScalarValue::value('a_number_float', '12.5000'),
                        ScalarValue::scopableLocalizableValue(
                            'a_localized_and_scopable_text_area',
                            'my pink tshirt',
                            'ecommerce',
                            'en_US'
                        ),
                    ]
                )
            ),
        ]);

        Assert::assertEquals($expectedProductModelList, $actualProductModelList);
    }

    /**
     * @test
     *
     * @group ce
     */
    public function it_gets_a_single_connector_product_model_from_its_code(): void
    {
        $data = $this->getIdAndDatesFromProductModelCode('sub_pm_A');

        $expectedProductModel = new ConnectorProductModel(
            (int)$data['id'],
            'sub_pm_A',
            new \DateTimeImmutable($data['created']),
            new \DateTimeImmutable($data['updated']),
            'root_pm',
            'familyA',
            'familyVariantA1',
            [],
            [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'X_SELL' => [
                    'products' => ['a_simple_product', 'another_product'],
                    'product_models' => ['simple_pm'],
                    'groups' => ['groupA', 'groupB'],
                ],
            ],
            ['categoryA1', 'categoryA2'],
            new ReadValueCollection(
                [
                    OptionValue::value('a_simple_select', 'optionA'),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    ScalarValue::value('a_text', 'Lorem ipsum dolor sit amet'),
                    ScalarValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue(
                        'a_localized_and_scopable_text_area',
                        'mon tshirt rose',
                        'tablet',
                        'fr_FR'
                    ),
                    ScalarValue::scopableLocalizableValue(
                        'a_localized_and_scopable_text_area',
                        'my pink tshirt',
                        'ecommerce',
                        'en_US'
                    ),
                ]
            )
        );

        $actualProductModel = $this->getQuery()->fromProductModelCode('sub_pm_A', $this->getUserIdFromUsername('admin'));

        Assert::assertEquals($expectedProductModel, $actualProductModel);
    }

    /**
     * @test
     */
    public function it_returns_empty_associations_if_there_is_no_association_type(): void
    {
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_association_type_translation');
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_association_type');

        $subProductModel = $this->getQuery()->fromProductModelCode('sub_pm_A', $this->getUserIdFromUsername('admin'));

        Assert::assertSame([], $subProductModel->associations());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_does_not_find_the_product_model(): void
    {
        $this->expectException(ObjectNotFoundException::class);
        $this->expectExceptionMessage('Product model "unknown_product_model" was not found');
        $this->getQuery()->fromProductModelCode('unknown_product_model', $this->getUserIdFromUsername('admin'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('a_simple_product', []);
        $this->createProduct('another_product', []);
        $this->createProductModel(
            [
                'code' => 'simple_pm',
                'family_variant' => 'familyVariantA2',
            ]
        );
        $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
                'categories' => ['categoryA2'],
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
                        [
                            'data' => 'mon tshirt rose',
                            'locale' => 'fr_FR',
                            'scope' => 'tablet',
                        ],
                    ],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['another_product'],
                        'product_models' => ['simple_pm'],
                        'groups' => ['groupB'],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'family_variant' => 'familyVariantA1',
                'parent' => 'root_pm',
                'categories' => ['categoryA1'],
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
                            'data' => 'Lorem ipsum dolor sit amet',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['a_simple_product'],
                        'product_models' => [],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): GetConnectorProductModels
    {
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_models_from_codes');
    }

    private function getUserIdFromUsername(string $username): int
    {
        return (int)$this->get('database_connection')->fetchColumn(
            'SELECT id from oro_user WHERE username = :username',
            [
                'username' => $username,
            ]
        );
    }

    private function getIdAndDatesFromProductModelCode(string $productModelCode): array
    {
        return $this->get('database_connection')->fetchAssoc(
            'SELECT id, created, updated FROM pim_catalog_product_model where code = :code',
            [
                'code' => $productModelCode,
            ]
        );
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

    private function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
